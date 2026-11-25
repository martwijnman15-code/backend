<?php
namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        if ($user->role == "admin") {
            return true;
        }
    }

    public function view(?User $user, Customer $customer): bool
    {
        if ($user->role == "admin") {
            return true;
        }
    }

    public function store(User $user): bool
    {
        if ($user->role == "admin") {
            return true;
        };
    }

    public function update(User $user, Customer $customer): bool
    {
        if ($user->role == "admin") {
            return true;
        }
    }

    public function delete(User $user, Customer $customer): bool
    {
        if ($user->role == "admin") {
            return true;
        }
    }
}