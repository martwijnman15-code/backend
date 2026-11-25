<?php
namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        if ($user->role == "admin") {
            return true;
        }
    }

    public function view(?User $user, Project $project): bool
    {
        if ($user->role == "admin") {
            return true;
        }
        
        else if ($user->id == $project->customer_id) {
            return true;
        }
    }

    public function store(User $user): bool
    {
        if ($user->role == "admin") {
            return true;
        }
    }

    public function update(User $user, Project $project): bool
    {
        if ($user->id == $project->customer_id) {
            return true;
        }

        if ($user->id == $project->customer_id) {
            return true;
        }

        if ($user->id != $project->customer_id) {
            return false;
        }
    }

    public function delete(User $user, Project $project): bool
    {
        if ($user->id == $project->customer_id) {
            return true;
        }
    }
}