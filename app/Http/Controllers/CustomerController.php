<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

/**
 * Pool owners for the signed-in admin's company. Customers have no login in v1.
 */
class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->forCompany($request->user()->company_id)
            ->when($request->search, function ($query, $search) {
                $query->where(fn ($q) => $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('service_address', 'like', "%$search%"));
            })
            ->status($request->status)
            ->orderBy('name')
            ->paginate($this->perPage($request, 20))
            ->withQueryString();

        return $this->infiniteOrView($request, 'customers.index', 'customers.rows', compact('customers'), 'customers');
    }

    public function create()
    {
        return view('customers.form', [
            'customer' => new Customer(['is_active' => true]),
        ]);
    }

    public function store(Request $request)
    {
        $customer = $request->user()->company->customers()->create($this->validated($request));

        $message = "$customer->name added.";

        return $request->input('action') === 'save_and_new'
            ? redirect()->route('customers.create')->with('success', $message)
            : redirect()->route('customers.show', $customer)->with('success', $message);
    }

    public function show(Request $request, Customer $customer)
    {
        $this->ensureSameCompany($request, $customer);
        $customer->load('pools');

        return view('customers.show', compact('customer'));
    }

    public function edit(Request $request, Customer $customer)
    {
        $this->ensureSameCompany($request, $customer);

        return view('customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->ensureSameCompany($request, $customer);

        $customer->update($this->validated($request));

        return redirect()->route('customers.show', $customer)->with('success', "$customer->name updated successfully.");
    }

    public function toggle(Request $request, Customer $customer)
    {
        $this->ensureSameCompany($request, $customer);

        $customer->update(['is_active' => ! $customer->is_active]);

        return back()->with('success', $customer->is_active
            ? "$customer->name is active again."
            : "$customer->name is turned off and hidden from new work.");
    }

    public function destroy(Request $request, Customer $customer)
    {
        $this->ensureSameCompany($request, $customer);

        if ($customer->pools()->exists()) {
            return back()->with('error', "$customer->name has pools. Turn them off instead of deleting, or delete the pools first.");
        }

        $name = $customer->name;
        $customer->delete();

        return redirect()->route('customers.index')->with('success', "$name deleted.");
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255'],
            'service_address' => ['required', 'string', 'max:1000'],
            'access_notes' => ['nullable', 'string', 'max:2000'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
