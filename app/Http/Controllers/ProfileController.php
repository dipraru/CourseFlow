<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Batch;
use App\Models\UserProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        // Ensure batches exist (create some default cohorts if missing)
        if (Batch::count() === 0) {
            $years = [2022, 2023, 2024, 2025];
            foreach ($years as $y) {
                Batch::create([
                    'name' => "Batch {$y}",
                    'year' => $y,
                    'total_semesters' => 8,
                    'is_active' => true,
                ]);
            }
        }

        // Ensure the user has a profile row; for students assign a default batch if missing
        if (! $user->profile) {
            $defaultBatch = Batch::first();
            UserProfile::create([
                'user_id' => $user->id,
                'batch_id' => $defaultBatch?->id,
            ]);
            // reload relation
            $user->load('profile');
        }

        $batches = Batch::orderBy('year', 'desc')->get();

        return view('profile.edit', [
            'user' => $user,
            'batches' => $batches,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Students should not be able to change their name or batch here.
        if ($user->isStudent()) {
            // Ensure name/batch_id are not modified even if present in payload
            unset($validated['name'], $validated['batch_id']);
        }

        $user->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $user->save();

        // Update or create profile fields that are editable by the user
        $profileData = [];
        if ($request->filled('phone')) {
            $profileData['phone'] = $request->input('phone');
        }

        // Students are allowed to edit address now per request
        if ($request->filled('address')) {
            $profileData['address'] = $request->input('address');
        }

        if (! $user->isStudent()) {
            if ($request->filled('batch_id')) {
                $profileData['batch_id'] = $request->input('batch_id');
            }
        }

        if (! empty($profileData)) {
            $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Students are not allowed to delete their account via UI
        if ($user->isStudent()) {
            abort(403, 'Students are not allowed to delete their account.');
        }

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
