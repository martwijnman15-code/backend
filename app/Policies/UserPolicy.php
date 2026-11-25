<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
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
        }

        public function store(User $user): bool
        {
            if ($user->role == "admin") {
                return true;
            }
        }

        public function update(User $user, Project $project): bool
        {
            if ($user->role == "admin") {
                return true;
            }
        }

        public function delete(User $user, Project $project): bool
        {
            if ($user->role == "admin") {
                return true;
            }
        }
    }
}
