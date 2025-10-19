<?php

namespace App\Policies;

use App\Models\ReviewAssignment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewAssignmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ReviewAssignment $assignment): bool
    {
        // للمراجع: فقط تعييناته الخاصة
        if ($user->hasRole('reviewer')) {
            return $assignment->reviewer_id === $user->id;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return ! $user->hasRole('reviewer');
    }

    public function update(User $user, ReviewAssignment $assignment): bool
    {
        return ! $user->hasRole('reviewer');
    }

    public function delete(User $user, ReviewAssignment $assignment): bool
    {
        return ! $user->hasRole('reviewer');
    }
}
