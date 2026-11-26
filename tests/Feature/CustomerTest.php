<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_store(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
        $data = ['contact_name' => 'Jane', 'phone_number' => '123', 'mail_address' => 'jane@example.com', 'company_name' => 'Example'];
        $this->postJson('/api/v1/customers', $data)->assertCreated();
        $this->assertDatabaseHas('customers', $data);
    }

    public function test_customer_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $customer = Customer::create(['contact_name' => 'Jane', 'phone_number' => '123', 'mail_address' => 'jane@example.com', 'company_name' => 'Example', 'user_id' => $admin->id]);
        $this->putJson("/api/v1/customers/{$customer->id}", ['contact_name' => 'Jane', 'phone_number' => '123', 'mail_address' => 'jane@example.com', 'company_name' => 'Updated'])->assertNoContent();
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'company_name' => 'Updated']);
    }

    public function test_customer_can_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $customer = Customer::create(['contact_name' => 'Jane', 'phone_number' => '123', 'mail_address' => 'jane@example.com', 'company_name' => 'Example', 'user_id' => $admin->id]);
        $this->deleteJson("/api/v1/customers/{$customer->id}")->assertNoContent();
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
