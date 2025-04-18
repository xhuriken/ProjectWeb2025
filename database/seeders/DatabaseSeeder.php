<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\School;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\UserCohort;
use App\Models\UserSchool;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create the default user
        $admin = User::create([
            'last_name'     => 'Admin',
            'first_name'    => 'Admin',
            'email'         => 'admin@codingfactory.com',
            'password'      => Hash::make('123456'),
        ]);

        $teacher = User::create([
            'last_name'     => 'Teacher',
            'first_name'    => 'Teacher',
            'email'         => 'teacher@codingfactory.com',
            'password'      => Hash::make('123456'),
        ]);

        $user = User::create([
            'last_name'     => 'Durand',
            'first_name'    => 'Alice',
            'email'         => 'alice.durand@codingfactory.com',
            'password'      => Hash::make('123456'),
            'average'       => 14,
        ]);

        $user1 = User::create([
            'last_name'     => 'Martin',
            'first_name'    => 'Lucas',
            'email'         => 'lucas.martin@codingfactory.com',
            'password'      => Hash::make('123456'),
            'average'       => 12,
        ]);

        $user2 = User::create([
            'last_name'     => 'Bernard',
            'first_name'    => 'Emma',
            'email'         => 'emma.bernard@codingfactory.com',
            'password'      => Hash::make('123456'),
            'average'       => 13,
        ]);

        $user3 = User::create([
            'last_name'     => 'Petit',
            'first_name'    => 'Hugo',
            'email'         => 'hugo.petit@codingfactory.com',
            'password'      => Hash::make('123456'),
            'average'       => 15,
        ]);

        $user4 = User::create([
            'last_name'     => 'Robert',
            'first_name'    => 'Léa',
            'email'         => 'lea.robert@codingfactory.com',
            'password'      => Hash::make('123456'),
            'average'       => 20,
        ]);

        $user5 = User::create([
            'last_name'     => 'Richard',
            'first_name'    => 'Tom',
            'email'         => 'tom.richard@codingfactory.com',
            'password'      => Hash::make('123456'),
            'average'       => 11,
        ]);

        $user6 = User::create([
            'last_name'     => 'Moreau',
            'first_name'    => 'Chloé',
            'email'         => 'chloe.moreau@codingfactory.com',
            'password'      => Hash::make('123456'),
            'average'       => 14,
        ]);

        $user7 = User::create([
            'last_name'     => 'Simon',
            'first_name'    => 'Nathan',
            'email'         => 'nathan.simon@codingfactory.com',
            'password'      => Hash::make('123456'),
            'average'       => 17,
        ]);

        // Create the default school
        $school = School::create([
            'user_id'   => $user->id,
            'name'      => 'Coding Factory',
        ]);

        // Create the admin role
        UserSchool::create([
            'user_id'   => $admin->id,
            'school_id' => $school->id,
            'cohort_id' => 1,
            'role'      => 'admin'
        ]);

        // Create the teacher role
        UserSchool::create([
            'user_id'   => $teacher->id,
            'school_id' => $school->id,
            'cohort_id' => 1,
            'role'      => 'teacher'
        ]);

        // Create the student role
        UserSchool::create([
            'user_id'   => $user->id,
            'school_id' => $school->id,
            'cohort_id' => 1,
            'role'      => 'student'
        ]);

        $cohort = Cohort::create([
            'school_id'   => $school->id,
            'name'      => 'Promotion B1',
            'description'   => 'Cergy',
            'start_date'  => '2025-09-02',
            'end_date'  => '2026-08-02',
        ]);

        UserCohort::create([
            'user_id'   => $user->id,
            'cohort_id' => $cohort->id,
        ]);
        UserCohort::create([
            'user_id'   => $user1->id,
            'cohort_id' => $cohort->id,
        ]);
        UserCohort::create([
            'user_id'   => $user2->id,
            'cohort_id' => $cohort->id,
        ]);
        UserCohort::create([
            'user_id'   => $user3->id,
            'cohort_id' => $cohort->id,
        ]);
        UserCohort::create([
            'user_id'   => $user4->id,
            'cohort_id' => $cohort->id,
        ]);
        UserCohort::create([
            'user_id'   => $user5->id,
            'cohort_id' => $cohort->id,
        ]);
        UserCohort::create([
            'user_id'   => $user6->id,
            'cohort_id' => $cohort->id,
        ]);
        UserCohort::create([
            'user_id'   => $user7->id,
            'cohort_id' => $cohort->id,
        ]);

    }
}
