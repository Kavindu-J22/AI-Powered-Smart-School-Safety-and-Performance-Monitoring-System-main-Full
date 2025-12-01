<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    protected $directory = 'admin.pages.setup.settings.';

    public function index()
    {
        $settings = Setting::first() ?? new Setting;

        return view($this->directory . 'index', compact('settings'));
    }

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
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $settings = Setting::first() ?? new Setting;

            $settings->update([
                'school_name' => $request->school_name,
                'school_type' => $request->school_type,
                'school_motto' => $request->school_motto,
                'principal_name' => $request->principal_name,
                'established_year' => $request->established_year,
                'total_capacity' => $request->total_capacity,
                'website_url' => $request->website_url,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'School information updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating school information: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateTheme(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'primary_color' => ['required', 'string', 'size:7', 'regex:~^#[0-9a-fA-F]{6}$~'],
                'secondary_color' => ['required', 'string', 'size:7', 'regex:~^#[0-9a-fA-F]{6}$~'],
                'accent_color' => ['required', 'string', 'size:7', 'regex:~^#[0-9a-fA-F]{6}$~'],
                'theme_mode' => 'nullable|in:light,dark,auto',
                'enable_animations' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $settings = Setting::first() ?? new Setting;

            $settings->update([
                'primary_color' => $request->primary_color,
                'secondary_color' => $request->secondary_color,
                'accent_color' => $request->accent_color,
                'theme_mode' => $request->theme_mode,
                'enable_animations' => $request->boolean('enable_animations'),
            ]);

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

            $settings = Setting::first() ?? new Setting;

            $settings->update([
                'academic_year_start' => $request->academic_year_start,
                'academic_year_end' => $request->academic_year_end,
                'school_start_time' => $request->school_start_time,
                'school_end_time' => $request->school_end_time,
            ]);

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

    public function updateSocialMedia(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'social_facebook' => 'nullable|url|max:255',
                'social_twitter' => 'nullable|url|max:255',
                'social_instagram' => 'nullable|url|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $settings = Setting::first() ?? new Setting;

            $settings->update([
                'social_facebook' => $request->social_facebook,
                'social_twitter' => $request->social_twitter,
                'social_instagram' => $request->social_instagram,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Social media settings updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating social media settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getThemeColors()
    {
        try {
            $settings = Setting::first() ?? new Setting;

            return response()->json([
                'success' => true,
                'colors' => $settings->theme_colors,
                'css_variables' => $settings->css_variables,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching theme colors: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateLanguage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'language' => 'required|string|in:en,si',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $settings = Setting::first() ?? new Setting;

            $settings->update([
                'language' => $request->language,
            ]);

            // Set application locale for immediate effect
            app()->setLocale($request->language);
            session(['locale' => $request->language]);

            return response()->json([
                'success' => true,
                'message' => __('settings.language_settings_saved'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update language settings: ' . $e->getMessage(),
            ], 500);
        }
    }
}
