<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(1000)->create();

        // Barang::factory(200)->create();

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'super@superadmin.com',
            'password' => 'SuperAdmin'
            ]);
    }
}
