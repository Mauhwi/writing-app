<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Project $project): bool
    {
        return $project->user_id === $user->id
            || $project->readers()
                ->where('user_id', $user->id)
                ->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAuthor();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isAuthor()
            && $project->user_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAuthor() && $project->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }
}
