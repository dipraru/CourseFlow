<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class DemoStudentProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Expecting students created as student1..student5 in DatabaseSeeder
        for ($i = 1; $i <= 5; $i++) {
            $email = "student{$i}@cs.edu";
            $user = User::where('email', $email)->first();
            if (! $user) {
                continue;
            }

            $profile = $user->profile;
            if (! $profile) {
                $profile = UserProfile::create(['user_id' => $user->id]);
            }

            $demo = [
                'gender' => $i % 2 === 0 ? 'Female' : 'Male',
                'father_name' => "Father of Student {$i}",
                'mother_name' => "Mother of Student {$i}",
                'address' => "123 Demo St, City {$i}",
                'roll_number' => 'ROLL' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'date_of_birth' => now()->subYears(18 + $i)->format('Y-m-d'),
            ];

            $profile->update($demo);
        }
    }
}
