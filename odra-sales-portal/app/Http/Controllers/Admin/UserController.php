<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        // Get all system administrators
        $admins = User::with('role')
            ->where('role_id', $adminRole->id)
            ->orderBy('name')
            ->get();

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
                    ->orWhere('rep_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users', 'admins'));
    }

    /**
     * Store a newly created sales representative or administrator.
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
                'territory' => 'Headquarters',
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
                notes: "Created administrator account for {$user->name} ({$user->email})"
            );

            return redirect()->route('admin.users.index')->with('success', "Administrator {$user->name} created successfully.");
        }

        // Standard sales representative creation
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'company_id' => ['nullable', 'string', 'max:100', 'unique:users,company_id'],
            'rep_code' => ['required', 'string', 'max:10'],
            'territory' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $salesRole = Role::where('slug', 'sales')->first();

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'company_id' => $validated['company_id'] ?? null,
            'rep_code' => strtoupper($validated['rep_code']),
            'territory' => $validated['territory'] ?? null,
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
            ],
            notes: "Created sales representative account for {$user->name} ({$user->email})"
        );

        return redirect()->route('admin.users.index')->with('success', "Sales representative {$user->name} registered.");
    }

    /**
     * Update a user (sales representative or administrator).
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $isAdmin = $user->isAdmin();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'company_id' => ['nullable', 'string', 'max:100', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if (! $isAdmin) {
            $rules['rep_code'] = ['required', 'string', 'max:10'];
            $rules['territory'] = ['nullable', 'string', 'max:255'];
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

        if (! $isAdmin) {
            $updateData['rep_code'] = strtoupper($validated['rep_code']);
            $updateData['territory'] = $validated['territory'] ?? null;
        } else {
            if (isset($validated['territory'])) {
                $updateData['territory'] = $validated['territory'];
            }
        }

        if ($request->has('is_active')) {
            $updateData['is_active'] = $request->boolean('is_active', true);
        }

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        AuditService::log(
            action: 'USER_UPDATED',
            entityType: 'User',
            entityId: $user->id,
            oldValue: $old,
            newValue: $updateData,
            notes: "Updated details for " . ($isAdmin ? 'administrator' : 'representative') . " {$user->name}"
        );

        return redirect()->route('admin.users.index')->with('success', ($isAdmin ? 'Administrator ' : 'Sales representative ') . "{$user->name} updated.");
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

        $roleTitle = $user->isAdmin() ? 'Administrator' : 'Representative';

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
        $isAdmin = $user->isAdmin();

        try {
            $user->delete();

            AuditService::log(
                action: 'USER_DELETED',
                entityType: 'User',
                entityId: $user->id,
                notes: "Removed " . ($isAdmin ? 'administrator' : 'representative') . " account {$userName} ({$userEmail})"
            );

            return redirect()->route('admin.users.index')->with('success', ($isAdmin ? 'Administrator ' : 'User ') . "{$userName} has been removed successfully.");
        } catch (\Exception $e) {
            $user->update(['is_active' => false]);

            AuditService::log(
                action: 'USER_TOGGLED',
                entityType: 'User',
                entityId: $user->id,
                notes: "Deactivated " . ($isAdmin ? 'administrator' : 'representative') . " account {$userName} due to attached database records"
            );

            return redirect()->route('admin.users.index')->with('error', "{$userName} has associated quotes or database records, so the account was deactivated instead of deleted to protect data history.");
        }
    }
}
