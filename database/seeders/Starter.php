<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class Starter extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use Faker
        $faker = Faker::create(env('APP_FAKER_LOCALE'));

        // Create Admin Account
        User::create([
            'name'      => 'Cristiano Ronaldo',
            'email'     => 'admin@admin.com',
            'password'  => bcrypt('admin'),
            'role'      => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Create Regular Users
        for ($i = 0; $i < 50; $i++) {
            User::create([
                'name'      => $faker->name(),
                'email'     => $faker->unique()->safeEmail(),
                'password'  => bcrypt('password'),
                'role'      => 'user',
                'is_active' => $faker->randomElement([true, false]),
                'email_verified_at' => $faker->randomElement([now(), null]),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }
}
