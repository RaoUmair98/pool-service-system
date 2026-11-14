<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Pools belong to a customer. Admin adds type, size, equipment, photo and monthly rate.
 */
class PoolController extends Controller
{
    public function create(Request $request, Customer $customer)
    {
        $this->ensureSameCompany($request, $customer);

        return view('pools.form', [
            'customer' => $customer,
            'pool' => new Pool([
                'is_active' => true,
                'type' => Pool::TYPE_CHLORINE,
                'monthly_rate' => 0,
            ]),
            'equipment' => [['name' => '', 'notes' => '']],
        ]);
    }

    public function store(Request $request, Customer $customer)
    {
        $this->ensureSameCompany($request, $customer);

        $data = $this->validated($request);
        $equipment = $this->validatedEquipment($request);

        $pool = DB::transaction(function () use ($request, $customer, $data, $equipment) {
            $pool = $customer->pools()->create($data + [
                'company_id' => $customer->company_id,
            ]);

            $this->storePhoto($request, $pool);
            $this->syncEquipment($pool, $equipment);

            return $pool;
        });

        $message = $pool->displayName().' added for '.$customer->name.'.';

        return $request->input('action') === 'save_and_new'
            ? redirect()->route('customers.pools.create', $customer)->with('success', $message)
            : redirect()->route('pools.show', $pool)->with('success', $message);
    }

    public function show(Request $request, Pool $pool)
    {
        $this->ensureSameCompany($request, $pool);
        $pool->load(['customer', 'equipment', 'schedule.technician']);

        return view('pools.show', compact('pool'));
    }

    public function edit(Request $request, Pool $pool)
    {
        $this->ensureSameCompany($request, $pool);
        $pool->load('equipment');

        $equipment = $pool->equipment->map(fn ($row) => [
            'name' => $row->name,
            'notes' => $row->notes,
        ])->all() ?: [['name' => '', 'notes' => '']];

        return view('pools.form', [
            'customer' => $pool->customer,
            'pool' => $pool,
            'equipment' => old('equipment', $equipment),
        ]);
    }

    public function update(Request $request, Pool $pool)
    {
        $this->ensureSameCompany($request, $pool);

        $data = $this->validated($request);
        $equipment = $this->validatedEquipment($request);

        DB::transaction(function () use ($request, $pool, $data, $equipment) {
            if ($request->boolean('remove_photo') && $pool->photo_path) {
                Storage::disk('public')->delete($pool->photo_path);
                $data['photo_path'] = null;
            }

            $pool->update($data);
            $this->storePhoto($request, $pool);
            $this->syncEquipment($pool, $equipment);
        });

        return redirect()->route('pools.show', $pool)->with('success', $pool->displayName().' updated successfully.');
    }

    public function toggle(Request $request, Pool $pool)
    {
        $this->ensureSameCompany($request, $pool);

        $pool->update(['is_active' => ! $pool->is_active]);

        return back()->with('success', $pool->is_active
            ? $pool->displayName().' is active again.'
            : $pool->displayName().' is turned off and hidden from new schedules and invoices.');
    }

    public function destroy(Request $request, Pool $pool)
    {
        $this->ensureSameCompany($request, $pool);

        if ($pool->visits()->exists()) {
            return back()->with('error', $pool->displayName().' has visits. Turn it off instead of deleting.');
        }

        $customer = $pool->customer;
        $name = $pool->displayName();

        if ($pool->photo_path) {
            Storage::disk('public')->delete($pool->photo_path);
        }

        $pool->delete();

        return redirect()->route('customers.show', $customer)->with('success', "$name deleted.");
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Pool::TYPES))],
            'gallons' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'monthly_rate' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'remove_photo' => ['sometimes', 'boolean'],
        ]);

        unset($data['photo'], $data['remove_photo']);

        return $data + ['is_active' => $request->boolean('is_active')];
    }

    /**
     * @return list<array{name: string, notes: ?string}>
     */
    protected function validatedEquipment(Request $request): array
    {
        $data = $request->validate([
            'equipment' => ['nullable', 'array'],
            'equipment.*.name' => ['nullable', 'string', 'max:255'],
            'equipment.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        return collect($data['equipment'] ?? [])
            ->map(fn ($row) => [
                'name' => trim((string) ($row['name'] ?? '')),
                'notes' => trim((string) ($row['notes'] ?? '')) ?: null,
            ])
            ->filter(fn ($row) => $row['name'] !== '')
            ->values()
            ->all();
    }

    protected function storePhoto(Request $request, Pool $pool): void
    {
        if (! $request->hasFile('photo')) {
            return;
        }

        if ($pool->photo_path) {
            Storage::disk('public')->delete($pool->photo_path);
        }

        $path = $request->file('photo')->store('companies/'.$pool->company_id.'/pools/'.$pool->id, 'public');
        $pool->update(['photo_path' => $path]);
    }

    /**
     * @param  list<array{name: string, notes: ?string}>  $equipment
     */
    protected function syncEquipment(Pool $pool, array $equipment): void
    {
        $pool->equipment()->delete();

        foreach ($equipment as $row) {
            $pool->equipment()->create($row);
        }
    }
}
