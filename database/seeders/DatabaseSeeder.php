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
            'last_name'     => 'Student',
            'first_name'    => 'Student',
            'email'         => 'student@codingfactory.com',
            'password'      => Hash::make('123456'),
        ]);

        $user1 = User::create([
            'last_name'     => 'Student1',
            'first_name'    => 'Student',
            'email'         => 'student1@codingfactory.com',
            'password'      => Hash::make('123456'),
        ]);

        $user2 = User::create([
            'last_name'     => 'Student2',
            'first_name'    => 'Student',
            'email'         => 'student2@codingfactory.com',
            'password'      => Hash::make('123456'),
            'average'       =>  13,
        ]);
        $user3 = User::create([
            'last_name'     => 'Student3',
            'first_name'    => 'Student',
            'email'         => 'student3@codingfactory.com',
            'password'      => Hash::make('123456'),
            'average'       =>  15,

        ]);
        $user4 = User::create([
            'last_name'     => 'Student4',
            'first_name'    => 'Student',
            'email'         => 'student4@codingfactory.com',
            'password'      => Hash::make('123456'),
            'average'       =>  20,

        ]);
        $user5 = User::create([
            'last_name'     => 'Student5',
            'first_name'    => 'Student',
            'email'         => 'student5@codingfactory.com',
            'password'      => Hash::make('123456'),
            'average'       =>  11,

        ]);
        $user6 = User::create([
            'last_name'     => 'Student6',
            'first_name'    => 'Student',
            'email'         => 'student6@codingfactory.com',
            'password'      => Hash::make('123456'),
            'average'       =>  14,

        ]);
        $user7 = User::create([
            'last_name'     => 'Student7',
            'first_name'    => 'Student',
            'email'         => 'student7@codingfactory.com',
            'password'      => Hash::make('123456'),
            'average'       =>  17,

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
            'role'      => 'admin'
        ]);

        // Create the teacher role
        UserSchool::create([
            'user_id'   => $teacher->id,
            'school_id' => $school->id,
            'role'      => 'teacher'
        ]);

        // Create the student role
        UserSchool::create([
            'user_id'   => $user->id,
            'school_id' => $school->id,
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
