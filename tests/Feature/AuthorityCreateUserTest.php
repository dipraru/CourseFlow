<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Batch;

class AuthorityCreateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_authority_can_create_student_user_with_profile()
    {
        // Seed minimal data
        $authority = User::factory()->create(['role' => 'authority']);
        $batch = Batch::create(['name' => 'Batch 2030', 'year' => 2030]);
        $advisor = User::factory()->create(['role' => 'advisor']);

        $this->actingAs($authority)
            ->post(route('authority.users.store'), [
                'name' => 'Test Student',
                'email' => 'student@example.com',
                'password' => 'password123',
                'role' => 'student',
                'student_id' => 'S-1001',
                'phone' => '+100',
                'batch_id' => $batch->id,
                'advisor_id' => $advisor->id,
            ])
            ->assertRedirect(route('authority.users'));

        $this->assertDatabaseHas('users', ['email' => 'student@example.com', 'role' => 'student']);
        $user = User::where('email', 'student@example.com')->first();
        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id, 'student_id' => 'S-1001']);
    }
}
