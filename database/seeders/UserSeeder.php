<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

         User::insert([

            [
                'name' => 'Admin Principal',
                'email' => 'towanouluc@gmail.com',
                'password' => Hash::make('Admin0000'),
                'role' => 'admin',
                'email_verified_at' => now(),

            ],

            [
                'name' => 'Merveille',
                'email' => 'merveille@gmail.com',
                'password' => Hash::make('Faker0001'),
                'role' => 'client',
                'email_verified_at' => now(),

            ],
            [
                'name' => 'Socrates',
                'email' => 'socrates@gmail.com',
                'password' => Hash::make('Faker0002'),
                'role' => 'admin',
                'email_verified_at' => now(),

            ],
            [
                'name' => 'Samuel',
                'email' => 'samuel@gmail.com',
                'password' => Hash::make('Faker0003'),
                'role' => 'client',
                'email_verified_at' => now(),

            ],[
                'name' => 'Malik',
                'email' => 'malik@gmail.com',
                'password' => Hash::make('Faker0004'),
                'role' => 'client',
                'email_verified_at' => now(),

            ],[
                'name' => 'Femi',
                'email' => 'femi@gmail.com',
                'password' => Hash::make('Faker0005'),
                'role' => 'client',
                'email_verified_at' => now(),

            ],
        ]);
    }
}
