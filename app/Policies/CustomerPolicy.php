<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->role === 'admin';
    }

    public function store(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->role === 'admin';
    }
}
