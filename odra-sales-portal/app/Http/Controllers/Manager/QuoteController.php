<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuoteController extends Controller
{
    /**
     * Display all company quotations with commercial and financial visibility.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Quote::withoutGlobalScopes()
            ->with(['user', 'latestVersion.snapshot'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('latestVersion.snapshot', fn ($sq) => $sq->where('customer_company', 'like', "%{$search}%"));
            });
        }

        $quotes = $query->paginate(15)->withQueryString();

        return view('manager.quotes.index', [
            'quotes' => $quotes,
            'currentStatus' => $status,
            'search' => $search,
        ]);
    }
}
