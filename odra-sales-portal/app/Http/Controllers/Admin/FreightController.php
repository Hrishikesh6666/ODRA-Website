<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FreightRule;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FreightController extends Controller
{
    /**
     * Display a listing of freight destination rates.
     */
    public function index(Request $request): View
    {
        $query = FreightRule::orderBy('state')->orderBy('city');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('state', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('state')) {
            $query->where('state', $request->input('state'));
        }

        $freightRules = $query->paginate(25)->withQueryString();
        $states = FreightRule::distinct()->orderBy('state')->pluck('state');

        return view('admin.freight.index', compact('freightRules', 'states'));
    }

    /**
     * Store a new freight destination rate.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:200'],
            'customer_rate' => ['required', 'numeric', 'min:0'],
            'municipality_rate' => ['required', 'numeric', 'min:0'],
        ]);

        $rule = FreightRule::create([
            ...$validated,
            'updated_by' => auth()->id(),
        ]);

        AuditService::log(
            action: 'FREIGHT_RULE_CREATED',
            entityType: 'FreightRule',
            entityId: $rule->id,
            newValue: $validated,
            notes: "Created freight destination for {$rule->city} (\${$rule->customer_rate})"
        );

        return redirect()->route('admin.freight.index')->with('success', "Freight rate for {$rule->city} added.");
    }

    /**
     * Update an existing freight rule.
     */
    public function update(Request $request, FreightRule $freight): RedirectResponse
    {
        $validated = $request->validate([
            'customer_rate' => ['required', 'numeric', 'min:0'],
            'municipality_rate' => ['required', 'numeric', 'min:0'],
        ]);

        $old = $freight->toArray();
        $freight->update([
            ...$validated,
            'updated_by' => auth()->id(),
        ]);

        AuditService::log(
            action: 'FREIGHT_RULE_UPDATED',
            entityType: 'FreightRule',
            entityId: $freight->id,
            oldValue: $old,
            newValue: $validated,
            notes: "Updated freight rates for {$freight->city}: Customer \${$validated['customer_rate']}, Municipality \${$validated['municipality_rate']}"
        );

        return redirect()->route('admin.freight.index')->with('success', "Freight rate for {$freight->city} updated.");
    }
}
