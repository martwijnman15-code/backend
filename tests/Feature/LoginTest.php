<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    // Keep authentication tests independent of the unrelated customer migrations.
    $this->artisan('migrate', ['--path' => [
        'database/migrations/0001_01_01_000000_create_users_table.php',
        'database/migrations/2026_11_12_061050_create_personal_access_tokens_table.php',
        'database/migrations/2026_11_16_082659_add_role_to_users_table.php',
    ]])->assertSuccessful();
});

it('returns a bearer token and safe user details for valid credentials', function (): void {
    $user = User::factory()->create([
        'email' => 'pacific@nexoq.com',
        'password' => 'password',
        'role' => 'admin',
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'pacific@nexoq.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.email', 'pacific@nexoq.com')
        ->assertJsonPath('user.role', 'admin')
        ->assertJsonMissingPath('user.password')
        ->assertJsonMissingPath('user.remember_token');

    $token = PersonalAccessToken::findToken($response->json('token'));

    expect($token)->not->toBeNull()
        ->and($token->tokenable_id)->toBe($user->id)
        ->and(Hash::check('password', $user->fresh()->password))->toBeTrue();

    $this->assertDatabaseCount('personal_access_tokens', 1);
});

it('returns 401 without issuing a token for incorrect credentials', function (string $email, string $password): void {
    User::factory()->create(['email' => 'pacific@nexoq.com', 'password' => 'password', 'role' => 'admin']);

    $this->postJson('/api/v1/login', compact('email', 'password'))
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'The provided credentials are incorrect.']);

    $this->assertDatabaseCount('personal_access_tokens', 0);
})->with([
    'wrong password' => ['pacific@nexoq.com', 'incorrect'],
    'unknown email' => ['unknown@example.com', 'password'],
]);

it('returns 422 for invalid login input', function (array $input, string $field): void {
    $this->postJson('/api/v1/login', $input)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);

    $this->assertDatabaseCount('personal_access_tokens', 0);
})->with([
    'missing email' => [['password' => 'password'], 'email'],
    'invalid email' => [['email' => 'invalid', 'password' => 'password'], 'email'],
    'missing password' => [['email' => 'pacific@nexoq.com'], 'password'],
    'non-string password' => [['email' => 'pacific@nexoq.com', 'password' => ['password']], 'password'],
]);

it('normalizes the login email before looking up the user', function (): void {
    User::factory()->create(['email' => 'pacific@nexoq.com', 'password' => 'password', 'role' => 'admin']);

    $this->postJson('/api/v1/login', ['email' => ' PACIFIC@NEXOQ.COM ', 'password' => 'password'])
        ->assertOk()
        ->assertJsonPath('user.email', 'pacific@nexoq.com');
});

it('returns 429 after too many login attempts', function (): void {
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->postJson('/api/v1/login', ['email' => 'unknown@example.com', 'password' => 'incorrect'])
            ->assertUnauthorized();
    }

    $this->postJson('/api/v1/login', ['email' => 'unknown@example.com', 'password' => 'incorrect'])
        ->assertTooManyRequests();

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('revokes only the current token and rejects it on subsequent requests', function (): void {
    $user = User::factory()->create(['role' => 'admin']);
    $current = $user->createToken('current-device');
    $other = $user->createToken('other-device');
    $anotherUser = User::factory()->create(['role' => 'user']);
    $anotherToken = $anotherUser->createToken('another-user');

    $this->withToken($current->plainTextToken)->postJson('/api/v1/logout')->assertNoContent();

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $current->accessToken->id]);
    $this->assertDatabaseHas('personal_access_tokens', ['id' => $other->accessToken->id]);
    $this->assertDatabaseHas('personal_access_tokens', ['id' => $anotherToken->accessToken->id]);

    $this->app['auth']->forgetGuards();

    $this->withToken($current->plainTextToken)->postJson('/api/v1/logout')->assertUnauthorized();
});

it('returns 401 when logging out without a valid bearer token', function (?string $token): void {
    $user = User::factory()->create(['role' => 'admin']);
    $user->createToken('keep-this-token');

    $this->withHeaders($token === null ? [] : ['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/logout')
        ->assertUnauthorized();

    $this->assertDatabaseCount('personal_access_tokens', 1);
})->with(['missing token' => [null], 'invalid token' => ['invalid-token']]);

it('returns 401 for session authentication on the token logout endpoint', function (): void {
    $user = User::factory()->create(['role' => 'admin']);
    $user->createToken('keep-this-token');

    $this->actingAs($user, 'web')->postJson('/api/v1/logout')->assertUnauthorized();

    $this->assertDatabaseCount('personal_access_tokens', 1);
});
