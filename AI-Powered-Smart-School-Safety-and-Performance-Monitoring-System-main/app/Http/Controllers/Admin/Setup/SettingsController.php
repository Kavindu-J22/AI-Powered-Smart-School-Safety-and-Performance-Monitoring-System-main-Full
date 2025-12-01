<?php

namespace App\Http\Controllers\Admin\Setup;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Repositories\Admin\Setup\SettingsRepository;
use App\Repositories\Interfaces\Admin\Setup\SettingsRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    protected SettingsRepositoryInterface $settingsRepository;

    public function __construct(SettingsRepository $settingsRepository)
    {
        $this->settingsRepository = $settingsRepository;
    }

    protected function index(Request $request)
    {
        $setting = Setting::first() ?? new Setting;

        return view('admin.pages.setup.settings.index', compact('setting'));
    }

    protected function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'school_name' => 'nullable|string|max:255',
            'school_type' => 'nullable|in:Primary,Secondary,Combined,International',
            'school_motto' => 'nullable|string|max:500',
            'principal_name' => 'nullable|string|max:255',
            'established_year' => 'nullable|integer|min:1800|max:'.date('Y'),
            'total_capacity' => 'nullable|integer|min:1',
            'website_url' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $setting = Setting::first() ?? new Setting;

        $setting->fill([
            'title' => $request->title,
            'school_name' => $request->school_name ?? $request->title,
            'school_type' => $request->school_type,
            'school_motto' => $request->school_motto,
            'principal_name' => $request->principal_name,
            'established_year' => $request->established_year,
            'total_capacity' => $request->total_capacity,
            'website_url' => $request->website_url,
        ]);

        $setting->save();

        return redirect()->back()->with('success', 'School settings updated successfully');
    }

    // AJAX endpoint for school information updates
    public function updateSchoolInfo(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'school_name' => 'nullable|string|max:255',
                'school_type' => 'nullable|in:Primary,Secondary,Combined,International',
                'school_motto' => 'nullable|string|max:500',
                'principal_name' => 'nullable|string|max:255',
                'established_year' => 'nullable|integer|min:1800|max:'.date('Y'),
                'total_capacity' => 'nullable|integer|min:1',
                'website_url' => 'nullable|url|max:255',
                'logo' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $setting = Setting::first() ?? new Setting;

            $data = [
                'school_name' => $request->school_name,
                'school_type' => $request->school_type,
                'school_motto' => $request->school_motto,
                'principal_name' => $request->principal_name,
                'established_year' => $request->established_year,
                'total_capacity' => $request->total_capacity,
                'website_url' => $request->website_url,
            ];

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($setting->logo && Storage::disk('public')->exists($setting->logo)) {
                    Storage::disk('public')->delete($setting->logo);
                }

                // Store new logo
                $logoPath = $request->file('logo')->store('logos', 'public');
                $data['logo'] = $logoPath;
            }

            $setting->fill($data);
            $setting->save();

            return response()->json([
                'success' => true,
                'message' => 'School information updated successfully',
                'logo_url' => $setting->logo ? asset('storage/' . $setting->logo) : null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating school information: ' . $e->getMessage(),
            ], 500);
        }
    }

    // AJAX endpoint for theme updates
    public function updateTheme(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'primary_color' => ['required', 'string', 'size:7', 'regex:~^#[0-9a-fA-F]{6}$~'],
                'secondary_color' => ['required', 'string', 'size:7', 'regex:~^#[0-9a-fA-F]{6}$~'],
                'accent_color' => ['required', 'string', 'size:7', 'regex:~^#[0-9a-fA-F]{6}$~'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $setting = Setting::first() ?? new Setting;

            $setting->fill([
                'primary_color' => $request->primary_color,
                'secondary_color' => $request->secondary_color,
                'accent_color' => $request->accent_color,
            ]);

            $setting->save();

            return response()->json([
                'success' => true,
                'message' => 'Theme updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating theme: ' . $e->getMessage(),
            ], 500);
        }
    }

    // AJAX endpoint for academic settings
    public function updateAcademic(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'academic_year_start' => 'required|in:January,February,March,April,May,June,July,August,September,October,November,December',
                'academic_year_end' => 'required|in:January,February,March,April,May,June,July,August,September,October,November,December|different:academic_year_start',
                'school_start_time' => 'required|date_format:H:i',
                'school_end_time' => 'required|date_format:H:i|after:school_start_time',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $setting = Setting::first() ?? new Setting;

            $setting->fill([
                'academic_year_start' => $request->academic_year_start,
                'academic_year_end' => $request->academic_year_end,
                'school_start_time' => $request->school_start_time,
                'school_end_time' => $request->school_end_time,
            ]);

            $setting->save();

            return response()->json([
                'success' => true,
                'message' => 'Academic settings updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating academic settings: ' . $e->getMessage(),
            ], 500);
        }
    }
}
