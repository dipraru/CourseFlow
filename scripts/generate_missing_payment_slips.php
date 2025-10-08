<?php

// One-off script to generate missing PaymentSlip records for head-approved registrations.
// Run from project root: php scripts/generate_missing_payment_slips.php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CourseRegistration;
use App\Models\PaymentSlip;
use App\Models\Fee;
use Illuminate\Support\Facades\Schema;

$created = 0;
$skipped = 0;
$errors = [];

$regs = CourseRegistration::with(['student.profile.batch', 'semester', 'semesterCourse.course'])
    ->where('status', 'head_approved')
    ->get();

if ($regs->isEmpty()) {
    echo "No head_approved registrations found.\n";
    exit(0);
}

// Group by student_id and semester_id to create one slip per student/semester
$groups = $regs->groupBy(function ($item) {
    return $item->student_id . '|' . $item->semester_id;
});

foreach ($groups as $key => $group) {
    try {
        $first = $group->first();
        $student = $first->student;
        $semester = $first->semester;
        if (! $student || ! $semester) {
            $skipped++;
            echo "Skipping group {$key}: missing student or semester.\n";
            continue;
        }

        $exists = PaymentSlip::where('student_id', $student->id)
            ->where('semester_id', $semester->id)
            ->first();

        if ($exists) {
            $skipped++;
            echo "Slip already exists for student {$student->id}, semester {$semester->id}.\n";
            continue;
        }

        // Fee lookup: try batch-specific, then semester-level
        $fee = null;
        if (Schema::hasColumn('fees', 'batch_id') && ! empty($student->profile->batch_id)) {
            $fee = Fee::where('batch_id', $student->profile->batch_id)
                ->where('semester_id', $semester->id)
                ->first();
        }
        if (! $fee) {
            $fee = Fee::where('semester_id', $semester->id)->first();
        }

        if (! $fee) {
            $skipped++;
            echo "No fee found for semester {$semester->id} (student {$student->id}). Skipping.\n";
            continue;
        }

        // Build registered courses array and credit hours total
        $registeredCourses = [];
        $totalCreditHours = 0;
        foreach ($group as $reg) {
            $course = $reg->semesterCourse->course ?? null;
            if ($course) {
                $registeredCourses[] = [
                    'id' => $course->id,
                    'code' => $course->course_code ?? null,
                    'name' => $course->course_name ?? null,
                    'credit_hours' => (float)($course->credit_hours ?? 0),
                ];
                $totalCreditHours += (float)($course->credit_hours ?? 0);
            }
        }

        // compute totals
        $creditHours = $totalCreditHours;
        $totalAmount = (float)($fee->per_credit_fee ?? 0) * $creditHours
            + (float)($fee->admission_fee ?? 0)
            + (float)($fee->library_fee ?? 0)
            + (float)($fee->lab_fee ?? 0)
            + (float)($fee->other_fees ?? 0);

        $feeBreakdown = [
            'per_credit_fee' => (float)($fee->per_credit_fee ?? 0),
            'credit_hours' => (float)$creditHours,
            'admission_fee' => (float)($fee->admission_fee ?? 0),
            'library_fee' => (float)($fee->library_fee ?? 0),
            'lab_fee' => (float)($fee->lab_fee ?? 0),
            'other_fees' => (float)($fee->other_fees ?? 0),
            'calculated_total' => (float)$totalAmount,
        ];

        $batchName = optional(optional($student->profile)->batch)->name ?? 'BATCH';
        $slipNumber = strtoupper($semester->name) . '-' . $batchName . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT);

        PaymentSlip::create([
            'student_id' => $student->id,
            'semester_id' => $semester->id,
            'slip_number' => $slipNumber,
            'total_amount' => $totalAmount,
            'credit_hours' => $creditHours,
            'fee_breakdown' => $feeBreakdown,
            'registered_courses' => $registeredCourses,
            'payment_status' => 'unpaid',
            'generated_at' => now(),
        ]);

        $created++;
        echo "Created slip {$slipNumber} for student {$student->id}, semester {$semester->id}.\n";

    } catch (\Exception $e) {
        $errors[] = $e->getMessage();
        echo "Error processing group {$key}: " . $e->getMessage() . "\n";
    }
}

echo "Done. Created: {$created}, Skipped: {$skipped}, Errors: " . count($errors) . "\n";
if (! empty($errors)) { print_r($errors); }

return 0;
