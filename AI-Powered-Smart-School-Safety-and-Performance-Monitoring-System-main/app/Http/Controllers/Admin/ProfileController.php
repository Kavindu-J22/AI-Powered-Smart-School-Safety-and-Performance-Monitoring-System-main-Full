<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;

class ProfileController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::id());

        return view('admin.pages.profile.index', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();

        return view('admin.pages.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = User::find(Auth::id());

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'bio' => 'nullable|string|max:1000',
            'date_of_birth' => 'nullable|date',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $validatedData['profile_image'] = $imagePath;
        }

        $user->update($validatedData);

        return redirect()->route('admin.profile.index')
            ->with('success', 'Profile updated successfully!');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::find(Auth::id());

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('admin.profile.index')
            ->with('success', 'Password changed successfully!');
    }

    public function deleteProfileImage()
    {
        $user = Auth::user();

        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->profile_image = null;
        if ($user instanceof \App\Models\User) {
            $user->save();
        }

        return response()->json(['success' => true, 'message' => 'Profile image deleted successfully!']);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $user = User::find(Auth::id());

            // Delete old image if exists
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Store new image
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $user->profile_image = $imagePath;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile image uploaded successfully!',
                'image_url' => Storage::url($imagePath)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProfileStats()
    {
        $user = Auth::user();

        // Get some basic stats (you can customize based on your needs)
        $stats = [
            'total_logins' => $user->login_count ?? 0,
            'account_created' => $user->created_at->format('M d, Y'),
            'last_login' => $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never',
            'profile_completion' => $this->calculateProfileCompletion($user),
        ];

        return response()->json($stats);
    }

    private function calculateProfileCompletion($user)
    {
        $fields = ['name', 'email', 'phone', 'address', 'bio', 'date_of_birth', 'profile_image'];
        $completedFields = 0;

        foreach ($fields as $field) {
            if (! empty($user->$field)) {
                $completedFields++;
            }
        }

        return round(($completedFields / count($fields)) * 100);
    }
}
