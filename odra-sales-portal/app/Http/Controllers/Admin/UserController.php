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
     * Display a listing of sales representatives.
     */
    public function index(Request $request): View
    {
        $salesRole = Role::where('slug', 'sales')->first();

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

        return view('admin.users.index', compact('users'));
    }

    /**
     * Store a newly created sales representative.
     */
    public function store(Request $request): RedirectResponse
    {
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
     * Update a sales representative.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'company_id' => ['nullable', 'string', 'max:100', Rule::unique('users')->ignore($user->id)],
            'rep_code' => ['required', 'string', 'max:10'],
            'territory' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['boolean'],
        ]);

        $old = $user->toArray();
        $updateData = [
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'company_id' => $validated['company_id'] ?? null,
            'rep_code' => strtoupper($validated['rep_code']),
            'territory' => $validated['territory'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];

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
            notes: "Updated details for representative {$user->name}"
        );

        return redirect()->route('admin.users.index')->with('success', "Sales representative {$user->name} updated.");
    }

    /**
     * Toggle active/deactivated status.
     */
    public function toggle(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        AuditService::log(
            action: 'USER_TOGGLED',
            entityType: 'User',
            entityId: $user->id,
            notes: "Representative {$user->name} account " . ($user->is_active ? 'activated' : 'deactivated')
        );

        return redirect()->route('admin.users.index')->with('success', "Representative {$user->name} status changed.");
    }
}
