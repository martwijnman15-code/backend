<?php

use App\Jobs\DeleteProjectMailJob;
use App\Jobs\StoreProjectMailJob;
use App\Jobs\UpdateCustomerMailJob;
use App\Jobs\UserCreateMailJob;
use App\Mail\CreateCustomerMail;
use App\Mail\DeleteCustomerMail;
use App\Mail\DeleteProjectMail;
use App\Mail\StoreProjectMail;
use App\Mail\UpdateCustomerMail;
use App\Mail\UserCreateMail;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('lets an administrator manage customers, projects and users', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($admin);
    $customer = $this->postJson('/api/v1/customers', ['contact_name' => 'Jane', 'company_name' => 'Example', 'phone_number' => '12345', 'mail_address' => 'jane@example.com'])
        ->assertCreated()->assertJsonPath('data.contact_name', 'Jane')->json('data.id');
    $this->getJson('/api/v1/customers?search=Example')->assertOk()->assertJsonCount(1, 'data');
    $project = $this->postJson('/api/v1/projects', ['project_name' => 'Website', 'price' => 100, 'customer_id' => $customer])
        ->assertCreated()->assertJsonPath('data.price', 100)->json('data.id');
    $this->getJson('/api/v1/projects?search=Website')->assertOk()->assertJsonCount(1, 'data');
    $this->putJson("/api/v1/projects/{$project}", ['project_name' => 'New website', 'price' => 200, 'customer_id' => $customer])->assertNoContent();
    $this->getJson("/api/v1/projects/{$project}")->assertOk()->assertJsonPath('data.project_name', 'New website');
    $user = $this->postJson('/api/v1/users', ['name' => 'New User', 'email' => 'new@example.com', 'role' => 'user', 'password' => 'password123'])
        ->assertCreated()->assertJsonPath('data.email', 'new@example.com')->json('data.id');
    $this->putJson("/api/v1/users/{$user}", ['name' => 'Updated', 'email' => 'new@example.com', 'role' => 'user'])->assertNoContent();
    $this->getJson("/api/v1/users/{$user}")->assertOk()->assertJsonPath('data.name', 'Updated');
    $this->deleteJson("/api/v1/users/{$user}")->assertNoContent();
    $this->deleteJson("/api/v1/projects/{$project}")->assertNoContent();
    $this->deleteJson("/api/v1/customers/{$customer}")->assertNoContent();
});

it('denies ordinary users administrative access', function (): void {
    Sanctum::actingAs(User::factory()->create(['role' => 'user']));
    $this->getJson('/api/v1/customers')->assertForbidden();
    $this->getJson('/api/v1/users')->assertForbidden();
    $this->getJson('/api/v1/projects')->assertForbidden();
});

it('rejects invalid user roles and duplicate email addresses', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($admin);
    $this->postJson('/api/v1/users', ['name' => 'Test', 'email' => $admin->email, 'role' => 'superadmin', 'password' => 'password123'])
        ->assertUnprocessable()->assertJsonValidationErrors(['email', 'role']);
});

it('prevents a user from moving their project to another customer', function (): void {
    $owner = User::factory()->create(['role' => 'user']);
    $other = User::factory()->create(['role' => 'user']);
    $customer = Customer::create(['company_name' => 'Owned', 'contact_name' => 'One', 'phone_number' => '1', 'mail_address' => 'one@example.com', 'user_id' => $owner->id]);
    $otherCustomer = Customer::create(['company_name' => 'Other', 'contact_name' => 'Two', 'phone_number' => '2', 'mail_address' => 'two@example.com', 'user_id' => $other->id]);
    $project = Project::create(['project_name' => 'Project', 'price' => 100, 'customer_id' => $customer->id, 'user_id' => $owner->id]);
    Sanctum::actingAs($owner);
    $this->getJson("/api/v1/projects/{$project->id}")->assertOk();
    $this->putJson("/api/v1/projects/{$project->id}", ['project_name' => 'Project', 'price' => 100, 'customer_id' => $otherCustomer->id])->assertForbidden();
    $this->assertDatabaseHas('projects', ['id' => $project->id, 'customer_id' => $customer->id]);
});

it('can send customer and user notification jobs', function (): void {
    Mail::fake();
    $customer = Customer::factory()->make(['mail_address' => 'customer@example.com']);
    $user = User::factory()->make(['email' => 'user@example.com']);
    (new UpdateCustomerMailJob($customer))->handle();
    (new UserCreateMailJob($user))->handle();
    Mail::assertSent(UpdateCustomerMail::class, fn ($mail) => $mail->hasTo('customer@example.com'));
    Mail::assertSent(UserCreateMail::class, fn ($mail) => $mail->hasTo('user@example.com'));
});

it('queues project notifications for the related customer', function (): void {
    Queue::fake();
    $admin = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($admin);
    $customer = Customer::create(['company_name' => 'Example', 'contact_name' => 'Jane', 'phone_number' => '123', 'mail_address' => 'jane@example.com', 'user_id' => $admin->id]);
    $project = $this->postJson('/api/v1/projects', ['project_name' => 'Website', 'price' => 100, 'customer_id' => $customer->id])->assertCreated()->json('data.id');
    $this->deleteJson("/api/v1/projects/{$project}")->assertNoContent();
    Queue::assertPushed(StoreProjectMailJob::class, fn ($job) => $job->customer->is($customer));
    Queue::assertPushed(DeleteProjectMailJob::class, fn ($job) => $job->customer->is($customer));
});

it('prevents a user from accessing another customer project', function (): void {
    $owner = User::factory()->create(['role' => 'user']);
    $outsider = User::factory()->create(['role' => 'user']);
    $customer = Customer::create(['company_name' => 'Owned', 'phone_number' => '1', 'user_id' => $owner->id]);
    $project = Project::create(['project_name' => 'Project', 'price' => 100, 'customer_id' => $customer->id, 'user_id' => $owner->id]);
    Sanctum::actingAs($outsider);
    $this->getJson("/api/v1/projects/{$project->id}")->assertForbidden();
    $this->deleteJson("/api/v1/projects/{$project->id}")->assertForbidden();
    $this->assertDatabaseHas('projects', ['id' => $project->id]);
});

it('renders notification emails', function (): void {
    foreach ([CreateCustomerMail::class, UpdateCustomerMail::class, DeleteCustomerMail::class, StoreProjectMail::class, DeleteProjectMail::class, UserCreateMail::class] as $mailable) {
        expect((new $mailable)->render())->toContain('Nexoraq Pacific');
    }
});

it('does not delete a user who still owns customer records', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create(['role' => 'user']);
    Customer::create(['company_name' => 'Owned', 'phone_number' => '1', 'user_id' => $owner->id]);
    Sanctum::actingAs($admin);
    $this->deleteJson("/api/v1/users/{$owner->id}")->assertStatus(409);
    $this->assertDatabaseHas('users', ['id' => $owner->id]);
});
