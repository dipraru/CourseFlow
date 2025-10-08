<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class DemoCoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // For each semester, create exactly 5 theory courses and up to 4 lab courses (no more)
        for ($sem = 1; $sem <= 12; $sem++) {
            // Remove any previously seeded demo courses for this semester by matching the (Sem {n}) suffix
            \App\Models\Course::where('intended_semester', $sem)
                ->where('course_name', 'like', "%({$sem})")
                ->delete();
                // Theory courses: keep first 5 (by id), delete extras, create missing ones
                $theoryCourses = Course::where('intended_semester', $sem)->where('course_type', 'theory')->orderBy('id')->get();
                if ($theoryCourses->count() > 5) {
                    $extras = $theoryCourses->slice(5);
                    foreach ($extras as $c) { $c->delete(); }
                }
                if ($theoryCourses->count() < 5) {
                    $missing = 5 - $theoryCourses->count();
                    // create missing theory courses with T-prefixed codes, ensuring uniqueness
                    $counter = 1;
                    while ($missing > 0) {
                        $code = sprintf('T%02d%02d', $sem, $counter);
                        if (!Course::where('course_code', $code)->exists()) {
                            Course::create([
                                'course_code' => $code,
                                'course_name' => "Theory Course {$counter} (Sem {$sem})",
                                'description' => "Theory Course {$counter} for semester {$sem}",
                                'credit_hours' => 3.0,
                                'intended_semester' => $sem,
                                'course_type' => 'theory',
                                'is_active' => true,
                            ]);
                            $missing--;
                        }
                        $counter++;
                    }
                }

                // Lab courses: keep first 4 (by id) and delete extras; ensure at most 4 labs
                $labCourses = Course::where('intended_semester', $sem)->where('course_type', 'lab')->orderBy('id')->get();
                if ($labCourses->count() > 4) {
                    $labExtras = $labCourses->slice(4);
                    foreach ($labExtras as $c) { $c->delete(); }
                }
                // If labs are fewer than a sensible default (3), create up to 3 labs (but never exceed 4 total)
                $labDesired = 3;
                if ($labCourses->count() < $labDesired) {
                    $toCreate = min($labDesired - $labCourses->count(), 4 - $labCourses->count());
                    $counter = 1;
                    while ($toCreate > 0) {
                        $code = sprintf('L%02d%02d', $sem, $counter);
                        if (!Course::where('course_code', $code)->exists()) {
                            Course::create([
                                'course_code' => $code,
                                'course_name' => "Lab Course {$counter} (Sem {$sem})",
                                'description' => "Lab Course {$counter} for semester {$sem}",
                                'credit_hours' => 1.5,
                                'intended_semester' => $sem,
                                'course_type' => 'lab',
                                'is_active' => true,
                            ]);
                            $toCreate--;
                        }
                        $counter++;
                    }
                }
        }
    }
}
