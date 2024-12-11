<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();

        // Insert multiple fake user records
        foreach (range(1, 50) as $index) {
            $email = $faker->unique()->safeEmail;

            if ($email !== 'test@example.com') {
                DB::table('users')->insert([
                    'name' => $faker->name,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => bcrypt('password'), // Default password for all users
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }   
    }
}