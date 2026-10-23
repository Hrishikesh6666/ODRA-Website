<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;

class QuotePolicy
{
    /**
     * Perform pre-authorization checks.
     * Deletion must always enforce permanent preservation of finalized quotations.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Deletion must always enforce permanent preservation of finalized quotations
        if ($ability === 'delete') {
            return null;
        }

        // Return null so specific ability methods evaluate ownership and manager permissions
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
     * Salespeople can only view their own quotations.
     * Commercial Managers and Technical Administrators can view team quotations.
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
     * Managers can view salespeople's quotes but CANNOT modify them.
     * Salespeople and managers can only modify quotations they created themselves.
     */
    public function update(User $user, Quote $quote): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($quote->status === 'Archived') {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Only the original creator can update their own quotation
        return $quote->user_id === $user->id;
    }

    /**
     * Determine whether the user can finalize the quote.
     * Managers cannot finalize a quotation created by a salesperson.
     */
    public function finalize(User $user, Quote $quote): bool
    {
        if (! $user->is_active) {
            return false;
        }

        $hasDraft = ($quote->status === 'Draft' || $quote->versions()->where('version_number', $quote->current_version)->where('status', 'Draft')->exists());
        if (! $hasDraft) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Only the original creator can finalize their own quotation
        return $quote->user_id === $user->id;
    }

    /**
     * Determine whether the user can create a revision of the quote.
     * Managers cannot create revisions of a quotation created by a salesperson.
     */
    public function revise(User $user, Quote $quote): bool
    {
        if (! $user->is_active) {
            return false;
        }

        $hasFinalized = ($quote->status === 'Finalized' || $quote->versions()->where('status', 'Finalized')->exists());
        if (! $hasFinalized) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Only the original creator can create revisions of their own quotation
        return $quote->user_id === $user->id;
    }

    /**
     * Determine whether the user can download the quote PDF.
     * Commercial managers and owning salespeople can download quote PDFs.
     */
    public function downloadPdf(User $user, Quote $quote): bool
    {
        return $user->is_active && ($user->isManager() || $quote->user_id === $user->id);
    }

    /**
     * Determine whether the user can delete the quote.
     * Permanent preservation: Quotes with any finalized revision can never be deleted.
     * Managers cannot delete quotations created by salespeople.
     */
    public function delete(User $user, Quote $quote): bool
    {
        if (! $user->is_active) {
            return false;
        }

        // Permanent preservation: never delete if any finalized revision exists
        if ($quote->versions()->where('status', 'Finalized')->exists()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $quote->user_id === $user->id && $quote->status === 'Draft';
    }
}
