<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FreightRule;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of sales representatives and administrators.
     */
    public function index(Request $request): View
    {
        $salesRole = Role::where('slug', 'sales')->first();
        $adminRole = Role::where('slug', 'admin')->first();
        $managerRole = Role::where('slug', 'manager')->first();

        // Technical administrators (no financial data)
        $admins = User::with('role')
            ->where('role_id', $adminRole->id)
            ->orderBy('name')
            ->get();

        // Commercial managers (exclusive financial visibility)
        $managers = $managerRole ? User::with('role')
            ->where('role_id', $managerRole->id)
            ->orderBy('name')
            ->get() : collect();

        // Query sales representatives
        $query = User::with('role')
            ->where('role_id', $salesRole->id)
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_id', 'like', "%{$search}%")
                    ->orWhere('rep_code', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('territory', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $users = $query->paginate(20)->withQueryString();

        $activeTab = $request->input('tab');
        if (!$activeTab) {
            if ($request->filled('search') || $request->filled('status') || $request->filled('page')) {
                $activeTab = 'sales';
            } else {
                $activeTab = 'admins';
            }
        }

        // Distinct states and all freight rules for dynamic rep territory selection
        $freightStates = FreightRule::distinct()->orderBy('state')->pluck('state');
        $freightRules = FreightRule::select('state', 'city', 'customer_rate', 'municipality_rate')
            ->orderBy('state')
            ->orderBy('city')
            ->get();

        return view('admin.users.index', compact('users', 'admins', 'managers', 'activeTab', 'freightStates', 'freightRules'));
    }

    /**
     * Store a newly created sales representative, manager, or administrator.
     */
    public function store(Request $request): RedirectResponse
    {
        $roleType = $request->input('role_type', 'sales');

        if ($roleType === 'admin') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'company_id' => ['nullable', 'string', 'max:100', 'unique:users,company_id'],
                'password' => ['required', 'string', 'min:8'],
            ]);

            $adminRole = Role::where('slug', 'admin')->first();

            $user = User::create([
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'company_id' => $validated['company_id'] ?? null,
                'rep_code' => null,
                'territory' => 'Technical Operations',
                'password' => Hash::make($validated['password']),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]);

            AuditService::log(
                action: 'USER_CREATED',
                entityType: 'User',
                entityId: $user->id,
                newValue: [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => 'admin',
                ],
                notes: "Created technical administrator account for {$user->name} ({$user->email})"
            );

            return redirect()->route('admin.users.index')->with('success', "Technical Administrator {$user->name} created successfully.");
        }

        if ($roleType === 'manager') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'company_id' => ['nullable', 'string', 'max:100', 'unique:users,company_id'],
                'territory' => ['nullable', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]);

            $managerRole = Role::where('slug', 'manager')->first();

            $user = User::create([
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'company_id' => $validated['company_id'] ?? null,
                'rep_code' => null,
                'territory' => $validated['territory'] ?? 'Commercial Management',
                'password' => Hash::make($validated['password']),
                'role_id' => $managerRole->id,
                'is_active' => true,
            ]);

            AuditService::log(
                action: 'USER_CREATED',
                entityType: 'User',
                entityId: $user->id,
                newValue: [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => 'manager',
                ],
                notes: "Created commercial manager account for {$user->name} ({$user->email})"
            );

            return redirect()->route('admin.users.index')->with('success', "Commercial Manager {$user->name} created successfully.");
        }

        // Standard sales representative creation
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'company_id' => ['nullable', 'string', 'max:100', 'unique:users,company_id'],
            'rep_code' => ['required', 'string', 'max:10'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:200'],
            'assigned_states' => ['nullable', 'array'],
            'assigned_states.*' => ['string', 'max:100'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $salesRole = Role::where('slug', 'sales')->first();

        $assignedStates = $request->input('assigned_states', []);
        $stateVal = $validated['state'] ?? null;
        if ($stateVal && !in_array($stateVal, $assignedStates)) {
            $assignedStates[] = $stateVal;
        }
        $assignedStates = array_values(array_unique(array_filter($assignedStates)));
        if (empty($stateVal) && !empty($assignedStates)) {
            $stateVal = $assignedStates[0];
        }

        $cityVal = $validated['city'] ?? null;
        $territory = ($cityVal && $stateVal)
            ? ($cityVal . ', ' . $stateVal)
            : ($stateVal ?: 'Field Sales');

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'company_id' => $validated['company_id'] ?? null,
            'rep_code' => strtoupper($validated['rep_code']),
            'state' => $stateVal,
            'city' => $cityVal,
            'assigned_states' => $assignedStates,
            'territory' => $territory,
            'password' => Hash::make($validated['password']),
            'role_id' => $salesRole->id,
            'is_active' => true,
        ]);

        AuditService::log(
            action: 'USER_CREATED',
            entityType: 'User',
            entityId: $user->id,
            newValue: [
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'sales',
                'rep_code' => $user->rep_code,
                'state' => $user->state,
                'city' => $user->city,
                'assigned_states' => $user->assigned_states,
            ],
            notes: "Created sales representative account for {$user->name} ({$user->email})"
        );

        return redirect()->route('admin.users.index')->with('success', "Sales representative {$user->name} registered.");
    }

    /**
     * Update a user (sales representative, manager, or administrator).
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $isRep = $user->isSalesRep();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'company_id' => ['nullable', 'string', 'max:100', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($isRep) {
            $rules['rep_code'] = ['required', 'string', 'max:10'];
            $rules['state'] = ['nullable', 'string', 'max:100'];
            $rules['city'] = ['nullable', 'string', 'max:200'];
            $rules['assigned_states'] = ['nullable', 'array'];
            $rules['assigned_states.*'] = ['string', 'max:100'];
        } else {
            $rules['rep_code'] = ['nullable', 'string', 'max:10'];
            $rules['territory'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        $old = $user->toArray();
        $updateData = [
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'company_id' => $validated['company_id'] ?? null,
        ];

        if ($isRep) {
            $updateData['rep_code'] = strtoupper($validated['rep_code']);
            $assignedStates = $request->input('assigned_states', []);
            $stateVal = $validated['state'] ?? null;
            if ($stateVal && !in_array($stateVal, $assignedStates)) {
                $assignedStates[] = $stateVal;
            }
            $assignedStates = array_values(array_unique(array_filter($assignedStates)));
            if (empty($stateVal) && !empty($assignedStates)) {
                $stateVal = $assignedStates[0];
            }

            $cityVal = $validated['city'] ?? null;
            $updateData['assigned_states'] = $assignedStates;
            $updateData['state'] = $stateVal;
            $updateData['city'] = $cityVal;
            $updateData['territory'] = ($cityVal && $stateVal)
                ? ($cityVal . ', ' . $stateVal)
                : ($stateVal ?: 'Field Sales');
        } else {
            if (isset($validated['territory'])) {
                $updateData['territory'] = $validated['territory'];
            }
            if (isset($validated['rep_code'])) {
                $updateData['rep_code'] = !empty($validated['rep_code']) ? strtoupper($validated['rep_code']) : null;
            }
        }

        if ($request->has('is_active')) {
            $updateData['is_active'] = $request->boolean('is_active', true);
        }

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        $roleTitle = $user->isAdmin() ? 'Administrator' : ($user->isManager() ? 'Manager' : 'Representative');

        AuditService::log(
            action: 'USER_UPDATED',
            entityType: 'User',
            entityId: $user->id,
            oldValue: $old,
            newValue: $updateData,
            notes: "Updated details for {$roleTitle} {$user->name}"
        );

        return redirect()->route('admin.users.index')->with('success', "{$roleTitle} {$user->name} updated.");
    }

    /**
     * Toggle active/deactivated status.
     */
    public function toggle(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot deactivate your own administrator account.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $roleTitle = $user->isAdmin() ? 'Administrator' : ($user->isManager() ? 'Manager' : 'Representative');

        AuditService::log(
            action: 'USER_TOGGLED',
            entityType: 'User',
            entityId: $user->id,
            notes: "{$roleTitle} {$user->name} account " . ($user->is_active ? 'activated' : 'deactivated')
        );

        return redirect()->route('admin.users.index')->with('success', "{$roleTitle} {$user->name} status changed.");
    }

    /**
     * Remove / Delete a user account (with safeguards).
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot remove your own administrator account.');
        }

        $adminRole = Role::where('slug', 'admin')->first();

        if ($user->role_id === $adminRole->id) {
            $adminCount = User::where('role_id', $adminRole->id)->count();
            if ($adminCount <= 1) {
                return redirect()->route('admin.users.index')->with('error', 'Cannot remove the last remaining administrator account.');
            }
        }

        $userName = $user->name;
        $userEmail = $user->email;
        $roleTitle = $user->isAdmin() ? 'Administrator' : ($user->isManager() ? 'Manager' : 'Representative');

        try {
            $user->delete();

            AuditService::log(
                action: 'USER_DELETED',
                entityType: 'User',
                entityId: $user->id,
                notes: "Removed {$roleTitle} account {$userName} ({$userEmail})"
            );

            return redirect()->route('admin.users.index')->with('success', "{$roleTitle} {$userName} has been removed successfully.");
        } catch (\Exception $e) {
            $user->update(['is_active' => false]);

            AuditService::log(
                action: 'USER_TOGGLED',
                entityType: 'User',
                entityId: $user->id,
                notes: "Deactivated {$roleTitle} account {$userName} due to attached database records"
            );

            return redirect()->route('admin.users.index')->with('error', "{$userName} has associated quotes or database records, so the account was deactivated instead of deleted to protect data history.");
        }
    }
}
