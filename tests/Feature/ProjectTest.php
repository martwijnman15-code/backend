<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function project_store(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
                'contact_name' => 'John Doe',
                'phone_number' => '+62 123456789',
                'mail_address' => 'info@company.id',
                'company_name' => 'Company',
            ]);

        Sanctum::actingAs($user);
    
        $project = Project::create([
            'project_name' => '',
            'price' => 1000.99,
            'customer_id' => $customer->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'project_name' => '',
            'price' => 1000.99,
            'customer_id' => $customer->id,
        ]);
    }

    public function project_update(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $customer = Customer::create([
                'contact_name' => 'Nuh Ibn Anouar',
                'phone_number' => '+62 123456789',
                'mail_address' => 'info@martabak-barista.id',
                'company_name' => 'Martabak Barista',
            ]);

        Sanctum::actingAs($user);
    
        $response = $this->putJson('/api/v1/projects/' . $project->id, [
            'project_name' => 'New Project',
            'price' => 1500.99,
            'customer_id' => $customer->id,
        ]);

        $response->assertStatus(200);
        
        $response->assertOk(); // 201

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'project_name' => '',
            'price' => 1000.99,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_project_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
                'contact_name' => 'Nuh Ibn Anouar',
                'phone_number' => '+62 123456789',
                'mail_address' => 'info@martabak-barista.id',
                'company_name' => 'Martabak Barista',
            ]);

        Sanctum::actingAs($user);

        $project = Project::create([
            'project_name' => 'New Project',
            'price' => 1500.99,
            'customer_id' => $customer->id,
        ]);

        $response = $this->deleteJson('/api/v1/projects/' . $project->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }
}
