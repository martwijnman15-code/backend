<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_store(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $customer = Customer::create(['contact_name' => 'Jane', 'phone_number' => '123', 'mail_address' => 'jane@example.com', 'company_name' => 'Example', 'user_id' => $admin->id]);
        $data = ['project_name' => 'Website', 'price' => 100, 'customer_id' => $customer->id];
        $this->postJson('/api/v1/projects', $data)->assertCreated();
        $this->assertDatabaseHas('projects', $data);
    }

    public function test_project_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $customer = Customer::create(['contact_name' => 'Jane', 'phone_number' => '123', 'mail_address' => 'jane@example.com', 'company_name' => 'Example', 'user_id' => $admin->id]);
        $project = Project::create(['project_name' => 'Website', 'price' => 100, 'customer_id' => $customer->id, 'user_id' => $admin->id]);
        $this->putJson("/api/v1/projects/{$project->id}", ['project_name' => 'Updated', 'price' => 200, 'customer_id' => $customer->id])->assertNoContent();
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'project_name' => 'Updated', 'price' => 200]);
    }

    public function test_project_can_be_deleted(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $customer = Customer::create(['contact_name' => 'Jane', 'phone_number' => '123', 'mail_address' => 'jane@example.com', 'company_name' => 'Example', 'user_id' => $admin->id]);
        $project = Project::create(['project_name' => 'Website', 'price' => 100, 'customer_id' => $customer->id, 'user_id' => $admin->id]);
        $this->deleteJson("/api/v1/projects/{$project->id}")->assertNoContent();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
