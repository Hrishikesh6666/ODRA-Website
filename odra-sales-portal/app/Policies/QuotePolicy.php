<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;

class QuotePolicy
{
    /**
     * Perform pre-authorization checks. Managers have global commercial authority.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isManager()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any quotes.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the specific quote.
     */
    public function view(User $user, Quote $quote): bool
    {
        return $user->is_active && ($quote->user_id === $user->id || $user->isManager() || $user->isAdmin());
    }

    /**
     * Determine whether the user can create quotes.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can update the quote.
     */
    public function update(User $user, Quote $quote): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isManager()) {
            return true;
        }

        return $quote->user_id === $user->id && $quote->status !== 'Archived';
    }

    /**
     * Determine whether the user can finalize the quote.
     */
    public function finalize(User $user, Quote $quote): bool
    {
        return $user->is_active && ($quote->user_id === $user->id || $user->isManager()) && $quote->status === 'Draft';
    }

    /**
     * Determine whether the user can create a revision of the quote.
     */
    public function revise(User $user, Quote $quote): bool
    {
        return $user->is_active && ($quote->user_id === $user->id || $user->isManager()) && in_array($quote->status, ['Finalized', 'Revised']);
    }

    /**
     * Determine whether the user can download the quote PDF.
     * Note: Technical admins are barred from downloading financial quotation PDFs.
     */
    public function downloadPdf(User $user, Quote $quote): bool
    {
        return $user->is_active && ($user->isManager() || $quote->user_id === $user->id);
    }

    /**
     * Determine whether the user can delete the quote.
     */
    public function delete(User $user, Quote $quote): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isManager()) {
            return true;
        }

        return $quote->user_id === $user->id && $quote->status === 'Draft';
    }
}
