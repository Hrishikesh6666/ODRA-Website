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
        // Deletion must always enforce permanent preservation of finalized quotations
        if ($ability === 'delete') {
            return null;
        }

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
        return $user->is_active 
            && ($quote->user_id === $user->id || $user->isManager()) 
            && ($quote->status === 'Draft' || $quote->versions()->where('version_number', $quote->current_version)->where('status', 'Draft')->exists());
    }

    /**
     * Determine whether the user can create a revision of the quote.
     */
    public function revise(User $user, Quote $quote): bool
    {
        return $user->is_active 
            && ($quote->user_id === $user->id || $user->isManager()) 
            && ($quote->status === 'Finalized' || $quote->versions()->where('status', 'Finalized')->exists());
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
     * Permanent preservation: Quotes with any finalized revision can never be deleted.
     */
    public function delete(User $user, Quote $quote): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($quote->versions()->where('status', 'Finalized')->exists()) {
            return false;
        }

        if ($user->isManager()) {
            return true;
        }

        return $quote->user_id === $user->id && $quote->status === 'Draft';
    }
}
