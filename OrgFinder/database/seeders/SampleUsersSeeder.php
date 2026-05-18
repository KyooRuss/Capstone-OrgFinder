<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['email' => 'student1@orgfinder.com', 'password' => 'password'],
            ['email' => 'student2@orgfinder.com', 'password' => 'password'],
            ['email' => 'student3@orgfinder.com', 'password' => 'password'],
            ['email' => 'student4@orgfinder.com', 'password' => 'password'],
            ['email' => 'student5@orgfinder.com', 'password' => 'password'],
            ['email' => 'student6@orgfinder.com', 'password' => 'password'],
            ['email' => 'student7@orgfinder.com', 'password' => 'password'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'first_name' => '',
                    'last_name'  => '',
                    'password'   => Hash::make($user['password']),
                    'role'       => 'student',
                    'status'     => 'active',
                ]
            );
        }
    }
}
