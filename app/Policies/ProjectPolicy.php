<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    // A project belongs to the user linked to its customer, not to a matching numeric ID.
    public function view(User $user, Project $project): bool
    {
        return $user->role === 'admin' || ($project->customer && $project->customer->user_id === $user->id);
    }

    public function store(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Project $project): bool
    {
        return $user->role === 'admin' || ($project->customer && $project->customer->user_id === $user->id);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->role === 'admin' || ($project->customer && $project->customer->user_id === $user->id);
    }
}
