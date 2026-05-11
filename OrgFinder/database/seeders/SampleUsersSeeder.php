<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        $student1 = User::updateOrCreate(
            ['email' => 'student1@orgfinder.com'],
            [
                'first_name'     => 'Maria',
                'last_name'      => 'Santos',
                'password'       => Hash::make('password'),
                'role'           => 'student',
                'student_number' => '2021-00001',
                'year_level'     => 3,
                'status'         => 'active',
            ]
        );
        $student1->profile()->updateOrCreate([], [
            'year_level'        => 3,
            'program'           => 'BS Information Technology',
            'interest'          => ['Technology', 'Design', 'Music'],
            'skill_to_improve'  => ['Programming', 'UI/UX Design'],
            'preferred_activity'=> ['Hackathons', 'Seminars'],
            'profile_completed' => true,
        ]);

        $student2 = User::updateOrCreate(
            ['email' => 'student2@orgfinder.com'],
            [
                'first_name'     => 'Juan',
                'last_name'      => 'dela Cruz',
                'password'       => Hash::make('password'),
                'role'           => 'student',
                'student_number' => '2022-00002',
                'year_level'     => 2,
                'status'         => 'active',
            ]
        );
        $student2->profile()->updateOrCreate([], [
            'year_level'        => 2,
            'program'           => 'BS Computer Science',
            'interest'          => ['Sports', 'Technology', 'Volunteering'],
            'skill_to_improve'  => ['Web Development', 'Public Speaking'],
            'preferred_activity'=> ['Sports Fest', 'Community Service'],
            'profile_completed' => true,
        ]);

        $student3 = User::updateOrCreate(
            ['email' => 'student3@orgfinder.com'],
            [
                'first_name'     => 'Ana',
                'last_name'      => 'Reyes',
                'password'       => Hash::make('password'),
                'role'           => 'student',
                'student_number' => '2023-00003',
                'year_level'     => 1,
                'status'         => 'active',
            ]
        );
        $student3->profile()->updateOrCreate([], [
            'profile_completed' => false,
        ]);

        User::updateOrCreate(
            ['email' => 'admin@orgfinder.com'],
            [
                'first_name' => 'Carlos',
                'last_name'  => 'Mendoza',
                'password'   => Hash::make('password'),
                'role'       => 'admin_officer',
                'status'     => 'active',
            ]
        );
    }
}
