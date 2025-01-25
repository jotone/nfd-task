<?php

namespace Database\Seeders;

use App\Models\{Company, Employee, User};
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        Company::factory(10)->create();

        Employee::factory(10)->create();
    }
}
