<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'logo',
        'date_format',
        'timezone',
        'country',
        'copyright_text',
        'language', // Add language field
        // Theme colors
        'primary_color',
        'secondary_color',
        'accent_color',
        'success_color',
        'warning_color',
        'danger_color',
        'info_color',
        // School specific settings
        'school_name',
        'school_motto',
        'school_type',
        'established_year',
        'principal_name',
        'vice_principal_name',
        'total_capacity',
        'website_url',
        'social_facebook',
        'social_twitter',
        'social_instagram',
        // Academic settings
        'academic_year_start',
        'academic_year_end',
        'school_start_time',
        'school_end_time',
        'working_days',
        'theme_mode',
        'enable_animations',
        'sidebar_style',
        'navbar_style',
    ];

    protected $casts = [
        'working_days' => 'array',
        'enable_animations' => 'boolean',
        'established_year' => 'integer',
        'total_capacity' => 'integer',
        'school_start_time' => 'datetime:H:i',
        'school_end_time' => 'datetime:H:i',
    ];

    protected $attributes = [
        'working_days' => '["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"]',
        'language' => 'en', // Default language
        'primary_color' => '#06C167',
        'secondary_color' => '#10B981',
        'accent_color' => '#F0FDF4',
        'success_color' => '#10B981',
        'warning_color' => '#F59E0B',
        'danger_color' => '#EF4444',
        'info_color' => '#3B82F6',
        'theme_mode' => 'light',
        'enable_animations' => true,
        'sidebar_style' => 'modern',
        'navbar_style' => 'glass',
        'academic_year_start' => 'January',
        'academic_year_end' => 'December',
        'school_start_time' => '08:00:00',
        'school_end_time' => '15:00:00',
    ];

    protected static function active()
    {
        return self::withoutTrashed();
    }

    // Get theme colors as CSS variables
    public function getThemeColorsAttribute()
    {
        return [
            '--primary-color' => $this->primary_color ?? '#06C167',
            '--secondary-color' => $this->secondary_color ?? '#10B981',
            '--accent-color' => $this->accent_color ?? '#F0FDF4',
            '--success-color' => $this->success_color ?? '#10B981',
            '--warning-color' => $this->warning_color ?? '#F59E0B',
            '--danger-color' => $this->danger_color ?? '#EF4444',
            '--info-color' => $this->info_color ?? '#3B82F6',
        ];
    }

    // Get school information
    public function getSchoolInfoAttribute()
    {
        return [
            'name' => $this->school_name ?? $this->title,
            'motto' => $this->school_motto,
            'type' => $this->school_type,
            'established' => $this->established_year,
            'principal' => $this->principal_name,
            'vice_principal' => $this->vice_principal_name,
            'capacity' => $this->total_capacity,
            'website' => $this->website_url,
        ];
    }

    // Get social media links
    public function getSocialLinksAttribute()
    {
        return [
            'facebook' => $this->social_facebook,
            'twitter' => $this->social_twitter,
            'instagram' => $this->social_instagram,
        ];
    }

    // Generate CSS variables string
    public function getCssVariablesAttribute()
    {
        $colors = $this->theme_colors;
        $css = '';
        foreach ($colors as $property => $value) {
            $css .= "{$property}: {$value}; ";
        }

        return $css;
    }
}
