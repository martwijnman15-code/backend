<?php

use App\Models\User;
use App\Policies\UserPolicy;
use Tests\TestCase;

uses(TestCase::class);

it('allows only administrators to manage users', function (): void {
    $policy = new UserPolicy;
    $admin = User::factory()->make(['role' => 'admin']);
    $user = User::factory()->make(['role' => 'user']);

    expect($policy->viewAny($admin))->toBeTrue()
        ->and($policy->store($admin))->toBeTrue()
        ->and($policy->view($admin, $user))->toBeTrue()
        ->and($policy->update($admin, $user))->toBeTrue()
        ->and($policy->delete($admin, $user))->toBeTrue()
        ->and($policy->viewAny($user))->toBeFalse()
        ->and($policy->store($user))->toBeFalse()
        ->and($policy->view($user, $admin))->toBeFalse()
        ->and($policy->update($user, $admin))->toBeFalse()
        ->and($policy->delete($user, $admin))->toBeFalse();
});
