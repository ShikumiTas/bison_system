<?php

namespace Database\Seeders;

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
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'EIJI WATANABE',
        //     'email' => 'jolokia106.work@gmail.com',
        // ]);

        $this->call([
            CitiesTableSeeder::class,
            LocationAliasesTableSeeder::class,
        ]);
    }
}
