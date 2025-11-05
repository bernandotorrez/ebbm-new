<?php

namespace Database\Seeders;

use App\Models\KantorSar;
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
        // Create a default KantorSar first
        $kantorSar = KantorSar::create([
            'kantor_sar_id' => 1,
            'kantor_sar' => 'Default Kantor SAR',
        ]);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'kantor_sar_id' => $kantorSar->kantor_sar_id,
        ]);
    }
}
