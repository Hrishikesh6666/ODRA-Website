<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;

class QuotePolicy
{
    /**
     * Perform pre-authorization checks. Admins have global permission.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any quotes (sales reps only their own list).
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
        return $user->is_active && $quote->user_id === $user->id;
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
        // Only active owner can update, and only if not archived
        if (! $user->is_active || $quote->user_id !== $user->id) {
            return false;
        }

        return $quote->status !== 'Archived';
    }

    /**
     * Determine whether the user can finalize the quote.
     */
    public function finalize(User $user, Quote $quote): bool
    {
        return $user->is_active && $quote->user_id === $user->id && $quote->status === 'Draft';
    }

    /**
     * Determine whether the user can create a revision of the quote.
     */
    public function revise(User $user, Quote $quote): bool
    {
        return $user->is_active && $quote->user_id === $user->id && in_array($quote->status, ['Finalized', 'Revised']);
    }

    /**
     * Determine whether the user can download the quote PDF.
     */
    public function downloadPdf(User $user, Quote $quote): bool
    {
        return $user->is_active && ($quote->user_id === $user->id || $user->isAdmin());
    }

    /**
     * Determine whether the user can delete the quote.
     */
    public function delete(User $user, Quote $quote): bool
    {
        // Only draft quotes can be deleted by sales reps; admins can delete any
        return $user->is_active && $quote->user_id === $user->id && $quote->status === 'Draft';
    }
}
