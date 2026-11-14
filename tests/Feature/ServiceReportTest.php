<?php

namespace Tests\Feature;

use App\Jobs\SendServiceReportJob;
use App\Mail\ServiceReportMail;
use App\Models\EmailLog;
use App\Models\Pool;
use App\Models\User;
use App\Models\Visit;
use App\Services\VisitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ServiceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_completing_a_visit_queues_service_report_job(): void
    {
        Queue::fake();

        $tech = User::factory()->technician()->create();
        $pool = Pool::factory()->create(['company_id' => $tech->company_id]);
        $visit = Visit::factory()->create([
            'company_id' => $tech->company_id,
            'pool_id' => $pool->id,
            'customer_id' => $pool->customer_id,
            'technician_user_id' => $tech->id,
            'status' => Visit::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        app(VisitService::class)->complete($visit, [
            'checklist' => ['skim' => true, 'brush' => true, 'vacuum' => true, 'empty_baskets' => true, 'clean_filter' => true],
            'reading_ph' => 7.2,
            'notes' => 'Done',
        ]);

        Queue::assertPushed(SendServiceReportJob::class, fn ($job) => $job->visitId === $visit->id);
    }

    public function test_job_sends_mail_and_logs_success(): void
    {
        Mail::fake();

        $tech = User::factory()->technician()->create();
        $pool = Pool::factory()->create(['company_id' => $tech->company_id]);
        $pool->customer->update(['email' => 'owner@example.com']);

        $visit = Visit::factory()->completed()->create([
            'company_id' => $tech->company_id,
            'pool_id' => $pool->id,
            'customer_id' => $pool->customer_id,
            'technician_user_id' => $tech->id,
            'checklist' => ['skim' => true],
            'reading_ph' => 7.4,
        ]);

        (new SendServiceReportJob($visit->id))->handle(app(\App\Services\ServiceReportService::class));

        Mail::assertSent(ServiceReportMail::class, function (ServiceReportMail $mail) use ($visit) {
            return $mail->visit->is($visit)
                && $mail->hasTo('owner@example.com');
        });

        $log = EmailLog::where('related_id', $visit->id)->sole();
        $this->assertSame(EmailLog::STATUS_SENT, $log->status);
        $this->assertSame('owner@example.com', $log->to_email);
        $this->assertNotNull($log->sent_at);
    }

    public function test_job_skips_when_customer_has_no_email(): void
    {
        Mail::fake();

        $tech = User::factory()->technician()->create();
        $pool = Pool::factory()->create(['company_id' => $tech->company_id]);
        $pool->customer->update(['email' => null]);

        $visit = Visit::factory()->completed()->create([
            'company_id' => $tech->company_id,
            'pool_id' => $pool->id,
            'customer_id' => $pool->customer_id,
            'technician_user_id' => $tech->id,
        ]);

        (new SendServiceReportJob($visit->id))->handle(app(\App\Services\ServiceReportService::class));

        Mail::assertNothingSent();

        $log = EmailLog::where('related_id', $visit->id)->sole();
        $this->assertSame(EmailLog::STATUS_SKIPPED, $log->status);
        $this->assertStringContainsString('no email', strtolower($log->error));
    }

    public function test_admin_can_preview_and_resend_report(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $tech = User::factory()->technician()->create(['company_id' => $admin->company_id]);
        $pool = Pool::factory()->create(['company_id' => $admin->company_id]);
        $pool->customer->update(['email' => 'jordan@example.com']);

        $visit = Visit::factory()->completed()->create([
            'company_id' => $admin->company_id,
            'pool_id' => $pool->id,
            'customer_id' => $pool->customer_id,
            'technician_user_id' => $tech->id,
        ]);

        $this->actingAs($admin)->get(route('visits.report', $visit))
            ->assertOk()
            ->assertSee($visit->customer->name);

        $this->actingAs($admin)->post(route('visits.report.resend', $visit))
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(ServiceReportMail::class);
        $this->assertSame(1, EmailLog::where('related_id', $visit->id)->where('status', EmailLog::STATUS_SENT)->count());
    }

    public function test_admin_can_view_email_log(): void
    {
        $admin = User::factory()->admin()->create();
        EmailLog::create([
            'company_id' => $admin->company_id,
            'customer_id' => null,
            'to_email' => 'a@example.com',
            'subject' => 'Service report — test',
            'type' => EmailLog::TYPE_SERVICE_REPORT,
            'status' => EmailLog::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('email-logs.index'))
            ->assertOk()
            ->assertSee('a@example.com')
            ->assertSee('Service report — test');
    }

    public function test_technician_cannot_view_reports_or_email_log(): void
    {
        $tech = User::factory()->technician()->create();
        $pool = Pool::factory()->create(['company_id' => $tech->company_id]);
        $visit = Visit::factory()->completed()->create([
            'company_id' => $tech->company_id,
            'pool_id' => $pool->id,
            'customer_id' => $pool->customer_id,
            'technician_user_id' => $tech->id,
        ]);

        $this->actingAs($tech)->get(route('visits.report', $visit))->assertForbidden();
        $this->actingAs($tech)->get(route('email-logs.index'))->assertForbidden();
    }
}
