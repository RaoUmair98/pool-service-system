<?php

namespace Tests\Feature;

use App\Mail\InvoiceMail;
use App\Models\Customer;
use App\Models\EmailLog;
use App\Models\Invoice;
use App\Models\Pool;
use App\Models\User;
use App\Services\InvoiceGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generator_creates_one_draft_invoice_per_customer_idempotently(): void
    {
        $company = \App\Models\Company::factory()->create(['currency' => 'USD']);
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        Pool::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'name' => 'Main',
            'monthly_rate' => 100,
            'is_active' => true,
        ]);
        Pool::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'name' => 'Spa',
            'monthly_rate' => 50,
            'is_active' => true,
        ]);

        $generator = app(InvoiceGenerator::class);
        $first = $generator->generateForCompany($company, 2026, 11);
        $second = $generator->generateForCompany($company, 2026, 11);

        $this->assertSame(1, $first['created']);
        $this->assertSame(0, $second['created']);
        $this->assertSame(1, $second['skipped']);

        $invoice = Invoice::where('customer_id', $customer->id)->sole();
        $this->assertSame(Invoice::STATUS_DRAFT, $invoice->status);
        $this->assertSame('150.00', $invoice->total);
        $this->assertCount(2, $invoice->items);
        $this->assertStringStartsWith('INV-'.$company->id.'-202611-', $invoice->invoice_number);
    }

    public function test_admin_can_generate_send_and_mark_paid(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $customer = Customer::factory()->create([
            'company_id' => $admin->company_id,
            'email' => 'bill@example.com',
        ]);
        Pool::factory()->create([
            'company_id' => $admin->company_id,
            'customer_id' => $customer->id,
            'monthly_rate' => 120,
        ]);

        $this->actingAs($admin)->post(route('invoices.generate'), [
            'year' => 2026,
            'month' => 11,
        ])->assertRedirect()->assertSessionHas('success');

        $invoice = Invoice::where('customer_id', $customer->id)->sole();

        $this->actingAs($admin)->get(route('invoices.show', $invoice))->assertOk()->assertSee($invoice->invoice_number);
        $this->actingAs($admin)->get(route('invoices.pdf', $invoice))->assertOk();

        $this->actingAs($admin)->post(route('invoices.send', $invoice))
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(InvoiceMail::class, fn (InvoiceMail $mail) => $mail->hasTo('bill@example.com'));
        $this->assertSame(Invoice::STATUS_SENT, $invoice->fresh()->status);
        $this->assertSame(EmailLog::STATUS_SENT, EmailLog::where('related_id', $invoice->id)->value('status'));

        $this->actingAs($admin)->post(route('invoices.mark-paid', $invoice), [
            'payment_method' => 'cash',
            'payment_note' => 'Collected on site',
        ])->assertRedirect()->assertSessionHas('success');

        $invoice->refresh();
        $this->assertSame(Invoice::STATUS_PAID, $invoice->status);
        $this->assertSame('cash', $invoice->payment_method);
        $this->assertNotNull($invoice->paid_at);
    }

    public function test_send_skips_when_customer_has_no_email(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $customer = Customer::factory()->create([
            'company_id' => $admin->company_id,
            'email' => null,
        ]);
        $invoice = Invoice::factory()->create([
            'company_id' => $admin->company_id,
            'customer_id' => $customer->id,
            'status' => Invoice::STATUS_DRAFT,
        ]);
        $invoice->items()->create([
            'description' => 'Monthly service — Main',
            'amount' => 100,
        ]);

        $this->actingAs($admin)->post(route('invoices.send', $invoice))
            ->assertRedirect()
            ->assertSessionHas('error');

        Mail::assertNothingSent();
        $this->assertSame(EmailLog::STATUS_SKIPPED, EmailLog::where('related_id', $invoice->id)->value('status'));
        $this->assertSame(Invoice::STATUS_DRAFT, $invoice->fresh()->status);
    }

    public function test_technician_cannot_manage_invoices(): void
    {
        $tech = User::factory()->technician()->create();
        $invoice = Invoice::factory()->create(['company_id' => $tech->company_id]);

        $this->actingAs($tech)->get(route('invoices.index'))->assertForbidden();
        $this->actingAs($tech)->get(route('invoices.show', $invoice))->assertForbidden();
    }

    public function test_admin_cannot_see_another_companys_invoices(): void
    {
        $admin = User::factory()->admin()->create();
        $other = Invoice::factory()->create();

        $this->actingAs($admin)->get(route('invoices.show', $other))->assertNotFound();
        $this->actingAs($admin)->post(route('invoices.send', $other))->assertNotFound();
    }

    public function test_dashboard_shows_unpaid_invoice_count(): void
    {
        $admin = User::factory()->admin()->create();
        Invoice::factory()->sent()->create([
            'company_id' => $admin->company_id,
            'customer_id' => Customer::factory()->create(['company_id' => $admin->company_id])->id,
            'total' => 80,
        ]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Unpaid invoices');
    }
}
