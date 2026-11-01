<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * The company's people: admins (owner / office) and technicians. Admins see and manage only their own company's team.
 */
class TeamController extends Controller
{
    public function index(Request $request)
    {
        $members = User::query()
            ->where('company_id', $request->user()->company_id)
            ->with('roles')
            ->when($request->search, function ($query, $search) {
                $query->where(fn ($q) => $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%"));
            })
            ->when(array_key_exists((string) $request->role, User::ROLES), fn ($query) => $query->role($request->role))
            ->status($request->status)
            ->orderBy('name')
            ->paginate($this->perPage($request, 20))
            ->withQueryString();

        return view('team.index', compact('members'));
    }

    public function create(Request $request)
    {
        return view('team.form', [
            'member' => new User(['is_active' => true]),
            'role' => array_key_exists((string) $request->role, User::ROLES) ? $request->role : User::ROLE_TECHNICIAN,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $member = $request->user()->company->users()->create(Arr::except($data, 'role'));
        $member->assignRole($data['role']);

        $message = "$member->name added as ".User::ROLES[$data['role']][0].'.';

        return $request->input('action') === 'save_and_new'
            ? redirect()->route('team.create', ['role' => $data['role']])->with('success', $message)
            : redirect()->route('team.index')->with('success', $message);
    }

    public function edit(Request $request, User $member)
    {
        $this->ensureSameCompany($request, $member);

        return view('team.form', ['member' => $member, 'role' => $member->role_name]);
    }

    public function update(Request $request, User $member)
    {
        $this->ensureSameCompany($request, $member);

        $data = $this->validated($request, $member);
        $oldRole = $member->role_name;

        // Nobody can lock themselves out: your own role and on/off switch stay as they are.
        if ($member->is($request->user())) {
            $data['role'] = $oldRole;
            $data['is_active'] = true;
        }

        $member->update(Arr::except($data, 'role'));

        if ($data['role'] !== $oldRole) {
            $member->syncRoles($data['role']);
            ActivityLog::record('updated', $member, ['Role' => ['old' => User::ROLES[$oldRole][0] ?? null, 'new' => User::ROLES[$data['role']][0]]]);
        }

        return redirect()->route('team.index')->with('success', "$member->name updated successfully.");
    }

    public function toggle(Request $request, User $member)
    {
        $this->ensureSameCompany($request, $member);

        if ($member->is($request->user())) {
            return back()->with('error', 'You cannot turn off your own account.');
        }

        $member->update(['is_active' => ! $member->is_active]);

        return back()->with('success', $member->is_active
            ? "$member->name can log in again."
            : "$member->name is turned off and can no longer log in.");
    }

    public function destroy(Request $request, User $member)
    {
        $this->ensureSameCompany($request, $member);

        if ($member->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account from here.');
        }

        $member->delete();

        return back()->with('success', "$member->name deleted.");
    }

    /**
     * Another company's people do not exist as far as this admin is concerned.
     */
    protected function ensureSameCompany(Request $request, User $member): void
    {
        abort_unless($member->company_id === $request->user()->company_id, 404);
    }

    protected function validated(Request $request, ?User $member = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($member)],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            // Set by the admin when adding someone; left empty on edit to keep the current one.
            'password' => [$member ? 'nullable' : 'required', 'string', Password::defaults()],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data + ['is_active' => $request->boolean('is_active')];
    }
}
