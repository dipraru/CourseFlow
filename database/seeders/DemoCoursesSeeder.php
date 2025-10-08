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
        // Basic templates to populate each semester
        $templates = [
            ['CSE101', 'Introduction to Programming', 3.0, 'theory'],
            ['CSE102', 'Programming Lab', 1.5, 'lab'],
            ['CSE103', 'Discrete Mathematics', 3.0, 'theory'],
            ['CSE104', 'Digital Logic Design', 3.0, 'theory_lab'],
            ['ENG101', 'English Composition', 3.0, 'theory'],
            ['MAT101', 'Calculus I', 3.0, 'theory'],
            ['PHY101', 'Physics I', 3.0, 'theory_lab'],
        ];

        // Ensure multiple courses per semester (1..12)
        for ($sem = 1; $sem <= 12; $sem++) {
            foreach ($templates as $idx => $tpl) {
                // create unique course code per semester+template
                $code = $tpl[0] . ($sem < 10 ? '0' . $sem : $sem) . ($idx+1);
                Course::updateOrCreate([
                    'course_code' => $code,
                ], [
                    'course_name' => $tpl[1] . " (Sem {$sem})",
                    'description' => $tpl[1] . " for semester {$sem}",
                    'credit_hours' => $tpl[2],
                    'intended_semester' => $sem,
                    'course_type' => $tpl[3],
                    'is_active' => true,
                ]);
            }
        }
    }
}
