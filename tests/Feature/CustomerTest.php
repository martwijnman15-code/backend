<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function customer_store(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
    
        $customer = Customer::create([
                'contact_name' => 'Nuh Ibn Anouar',
                'phone_number' => '+62 123456789',
                'mail_address' => 'info@martabak-barista.id',
                'company_name' => 'Martabak Barista',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'contact_name' => 'Nuh Ibn Anouar',
            'phone_number' => '+62 123456789',
            'mail_address' => 'info@martabak-barista.id',
            'company_name' => 'Martabak Barista',
        ]);
    }

    public function customer_update(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
    
        $customer = Customer::create([
                'contact_name' => 'Nuh Ibn Anouar',
                'phone_number' => '+62 123456789',
                'mail_address' => 'info@martabak-barista.id',
                'company_name' => 'Martabak Barista',
            ]);

        $response = $this->putJson('/v1/api/customers', [
                'contact_name' => 'Nuh Ibn Anouar',
                'phone_number' => '+62 123456789',
                'mail_address' => 'info@martabak-barista.id',
                'company_name' => 'Martabak and Barista',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'company_name' => 'Martabak and Barista',
        ]);
    }

    public function test_customer_can_be_deleted(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $customer = Customer::create([
            'contact_name' => 'Nuh Ibn Anouar',
            'phone_number' => '+62 123456789',
            'mail_address' => 'info@martabak-barista.id',
            'company_name' => 'Martabak Barista',
        ]);

        $response = $this->deleteJson('/api/v1/customers/' . $customer->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }
}
