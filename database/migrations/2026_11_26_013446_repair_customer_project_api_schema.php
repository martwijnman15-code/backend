<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers_before_api_repair') || Schema::hasTable('customers_repaired')) {
            throw new RuntimeException('Customer migration tables already exist; inspect them before retrying.');
        }

        Schema::create('customers_repaired', function (Blueprint $table): void {
            $table->id();
            $table->string('contact_name')->nullable();
            $table->string('phone_number');
            $table->string('mail_address')->nullable();
            $table->string('company_name');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->timestamps();
        });

        foreach (DB::table('customers')->cursor() as $customer) {
            DB::table('customers_repaired')->insert([
                'phone_number' => $customer->phone_number,
                'company_name' => $customer->company_name,
                'user_id' => $customer->user_id,
                'created_at' => $customer->created_at,
                'updated_at' => $customer->updated_at,
            ]);
        }

        // Keep the original rows available if a later migration step fails.
        Schema::rename('customers', 'customers_before_api_repair');
        Schema::rename('customers_repaired', 'customers');
        // Existing projects have no reliable customer reference to backfill.
        Schema::table('projects', function (Blueprint $table): void {
            $table->foreignId('customer_id')->nullable()->constrained('customers');
        });
    }

    public function down(): void
    {
        throw new RuntimeException('This migration cannot be rolled back without losing customer data. Restore a backup instead.');
    }
};
