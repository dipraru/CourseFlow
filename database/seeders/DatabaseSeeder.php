<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Batch;
use App\Models\Semester;
use App\Models\Course;
use App\Models\SemesterCourse;
use App\Models\Fee;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Department Head
        $departmentHead = User::create([
            'name' => 'Dr. John Smith',
            'email' => 'head@cs.edu',
            'password' => Hash::make('password'),
            'role' => 'department_head',
            'user_id' => 'HEAD001',
            'is_active' => true,
        ]);

        UserProfile::create([
            'user_id' => $departmentHead->id,
            'phone' => '+1234567890',
            'department' => 'Computer Science',
            'designation' => 'Department Head & Professor',
        ]);

        // Create Authority User
        $authority = User::create([
            'name' => 'Admin User',
            'email' => 'admin@cs.edu',
            'password' => Hash::make('password'),
            'role' => 'authority',
            'user_id' => 'ADMIN001',
            'is_active' => true,
        ]);

        UserProfile::create([
            'user_id' => $authority->id,
            'phone' => '+1234567891',
            'department' => 'Computer Science',
            'designation' => 'Administrative Officer',
        ]);

        // Create Advisors
        $advisor1 = User::create([
            'name' => 'Dr. Sarah Johnson',
            'email' => 'advisor1@cs.edu',
            'password' => Hash::make('password'),
            'role' => 'advisor',
            'user_id' => 'ADV001',
            'is_active' => true,
        ]);

        UserProfile::create([
            'user_id' => $advisor1->id,
            'phone' => '+1234567892',
            'department' => 'Computer Science',
            'designation' => 'Associate Professor',
        ]);

        $advisor2 = User::create([
            'name' => 'Dr. Michael Brown',
            'email' => 'advisor2@cs.edu',
            'password' => Hash::make('password'),
            'role' => 'advisor',
            'user_id' => 'ADV002',
            'is_active' => true,
        ]);

        UserProfile::create([
            'user_id' => $advisor2->id,
            'phone' => '+1234567893',
            'department' => 'Computer Science',
            'designation' => 'Assistant Professor',
        ]);

        // Create Batches
        $batch2023 = Batch::create([
            'name' => 'Batch 2023',
            'year' => 2023,
            'total_semesters' => 8,
            'is_active' => true,
        ]);

        $batch2024 = Batch::create([
            'name' => 'Batch 2024',
            'year' => 2024,
            'total_semesters' => 8,
            'is_active' => true,
        ]);

        // Create Sample Students for Batch 2024
        for ($i = 1; $i <= 5; $i++) {
            $student = User::create([
                'name' => "Student $i",
                'email' => "student$i@cs.edu",
                'password' => Hash::make('password'),
                'role' => 'student',
                'user_id' => 'STU' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);

            UserProfile::create([
                'user_id' => $student->id,
                'batch_id' => $batch2024->id,
                'advisor_id' => $i <= 2 ? $advisor1->id : $advisor2->id,
                'phone' => '+1234567' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'department' => 'Computer Science',
            ]);
        }

        // Create Semesters
        $semester = Semester::create([
            'name' => 'Fall 2024',
            'type' => 'Fall',
            'year' => 2024,
            'semester_number' => 1,
            'registration_start_date' => '2024-08-01',
            'registration_end_date' => '2024-08-15',
            'semester_start_date' => '2024-09-01',
            'semester_end_date' => '2024-12-31',
            'is_active' => true,
            'is_current' => true,
        ]);

        // Create Courses
        $courses = [
            [
                'course_code' => 'CSE101',
                'course_name' => 'Introduction to Programming',
                'description' => 'Basic programming concepts using C/C++',
                'credit_hours' => 3.0,
                'intended_semester' => 1,
                'course_type' => 'theory',
            ],
            [
                'course_code' => 'CSE102',
                'course_name' => 'Programming Lab',
                'description' => 'Hands-on programming practice',
                'credit_hours' => 1.5,
                'intended_semester' => 1,
                'course_type' => 'lab',
            ],
            [
                'course_code' => 'CSE103',
                'course_name' => 'Discrete Mathematics',
                'description' => 'Mathematical foundations for computer science',
                'credit_hours' => 3.0,
                'intended_semester' => 1,
                'course_type' => 'theory',
            ],
            [
                'course_code' => 'CSE104',
                'course_name' => 'Digital Logic Design',
                'description' => 'Introduction to digital circuits and logic gates',
                'credit_hours' => 3.0,
                'intended_semester' => 1,
                'course_type' => 'theory_lab',
            ],
            [
                'course_code' => 'ENG101',
                'course_name' => 'English Composition',
                'description' => 'Academic writing and communication skills',
                'credit_hours' => 3.0,
                'intended_semester' => 1,
                'course_type' => 'theory',
            ],
        ];

        foreach ($courses as $courseData) {
            $course = Course::create($courseData);
            
            // Add courses to current semester
            SemesterCourse::create([
                'semester_id' => $semester->id,
                'course_id' => $course->id,
                'max_students' => 60,
                'enrolled_students' => 0,
                'is_available' => true,
            ]);
        }

        // Create Fee Structure for the semester
        Fee::create([
            'semester_id' => $semester->id,
            'per_credit_fee' => 500.00,
            'admission_fee' => 1000.00,
            'library_fee' => 200.00,
            'lab_fee' => 300.00,
            'other_fees' => 100.00,
            'fee_description' => 'Fall 2024 fee structure',
            'is_active' => true,
        ]);

        // Populate demo profile data for sample students
        $this->call(DemoStudentProfileSeeder::class);

        // Populate demo courses across 12 semesters
        $this->call(DemoCoursesSeeder::class);
    }
}
