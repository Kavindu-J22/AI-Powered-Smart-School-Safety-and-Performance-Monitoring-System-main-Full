@extends('admin.layouts.app')

@section('css')
    @vite(['resources/css/admin/settings.css', 'resources/css/components/utilities.css'])
@endsection

@section('content')
    @include('admin.layouts.sidebar')

    <main class="main-content position-relative max-height-vh-100">

        @include('admin.layouts.navbar')

        <div class="container-fluid pt-2">
            <div class="row">
                <div class="ms-3">
                    @php
                        $breadcrumbs = getBreadcrumbs();
                        $breadcrumb = $breadcrumbs[count($breadcrumbs) - 2];
                    @endphp
                    <h3 class="mb-0 h4 font-weight-bolder">{{ ucfirst($breadcrumb) }}</h3>
                    <p class="mb-4 d-flex align-items-center">
                        <i class="material-symbols-rounded opacity-5 me-2">settings</i>
                        Configure your school settings and customize themes
                    </p>
                </div>
            </div>

            <div class="row">
                <!-- School Information Settings -->
                <div class="col-12">
                    <div class="card my-4 glassmorphism-card">
                        <div class="card-header pb-0">
                            <div class="d-flex align-items-center">
                                <i class="material-symbols-rounded me-2">school</i>
                                <h6 class="mb-0">{{ __('settings.school_information') }}</h6>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <form id="school-info-form" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">School Name</label>
                                            <input type="text" class="form-control" name="school_name"
                                                value="{{ $setting->school_name ?? ($setting->title ?? '') }}" required
                                                maxlength="255">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">School Type</label>
                                            <select class="form-control" name="school_type">
                                                <option value="">Select School Type</option>
                                                <option value="Primary"
                                                    {{ ($setting->school_type ?? '') === 'Primary' ? 'selected' : '' }}>
                                                    Primary School</option>
                                                <option value="Secondary"
                                                    {{ ($setting->school_type ?? '') === 'Secondary' ? 'selected' : '' }}>
                                                    Secondary School</option>
                                                <option value="Combined"
                                                    {{ ($setting->school_type ?? '') === 'Combined' ? 'selected' : '' }}>
                                                    Combined School</option>
                                                <option value="International"
                                                    {{ ($setting->school_type ?? '') === 'International' ? 'selected' : '' }}>
                                                    International School</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">School Motto</label>
                                            <input type="text" class="form-control" name="school_motto"
                                                value="{{ $setting->school_motto ?? '' }}" maxlength="255">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Principal Name</label>
                                            <input type="text" class="form-control" name="principal_name"
                                                value="{{ $setting->principal_name ?? '' }}" maxlength="255">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Established Year</label>
                                            <input type="number" class="form-control" name="established_year"
                                                value="{{ $setting->established_year ?? '' }}" min="1800"
                                                max="2030">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Total Capacity (Students)</label>
                                            <input type="number" class="form-control" name="total_capacity"
                                                value="{{ $setting->total_capacity ?? '' }}" min="1">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Website URL</label>
                                            <input type="url" class="form-control" name="website_url"
                                                value="{{ $setting->website_url ?? '' }}"
                                                placeholder="https://www.example.com">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="school-logo-upload-section">
                                            <div class="upload-header mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="upload-icon-circle me-3">
                                                        <i class="material-symbols-rounded">school</i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 upload-title">School Logo</h6>
                                                        <small class="text-muted">Upload your school's official logo</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="logo-upload-container">
                                                <div class="row align-items-center">
                                                    <div class="col-md-4">
                                                        <div class="logo-preview-card">
                                                            <div class="logo-preview-wrapper" id="logo-preview-wrapper">
                                                                @if ($setting->logo ?? '')
                                                                    <img id="logo-preview"
                                                                        src="{{ asset('storage/' . $setting->logo) }}"
                                                                        alt="School Logo Preview"
                                                                        class="logo-preview-image">
                                                                    <div class="logo-overlay">
                                                                        <i class="material-symbols-rounded">edit</i>
                                                                    </div>
                                                                @else
                                                                    <div class="logo-placeholder" id="logo-placeholder">
                                                                        <i
                                                                            class="material-symbols-rounded logo-placeholder-icon">add_photo_alternate</i>
                                                                        <p class="logo-placeholder-text">Click to upload
                                                                            logo</p>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="upload-controls">
                                                            <input type="file" name="logo" id="logo"
                                                                class="d-none"
                                                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                                                                onchange="handleLogoUpload(event)">

                                                            <div class="upload-actions mb-3">
                                                                <button type="button" class="btn btn-primary btn-upload"
                                                                    onclick="document.getElementById('logo').click()">
                                                                    <i
                                                                        class="material-symbols-rounded me-2">cloud_upload</i>
                                                                    Choose Logo
                                                                </button>
                                                                @if ($setting->logo ?? '')
                                                                    <button type="button"
                                                                        class="btn btn-outline-danger ms-2"
                                                                        onclick="removeLogo()">
                                                                        <i class="material-symbols-rounded me-1">delete</i>
                                                                        Remove
                                                                    </button>
                                                                @endif
                                                            </div>

                                                            <div class="upload-requirements">
                                                                <div class="requirement-item">
                                                                    <i
                                                                        class="material-symbols-rounded text-success">check_circle</i>
                                                                    <span>Formats: JPG, PNG, GIF, WebP</span>
                                                                </div>
                                                                <div class="requirement-item">
                                                                    <i
                                                                        class="material-symbols-rounded text-success">check_circle</i>
                                                                    <span>Max size: 2MB</span>
                                                                </div>
                                                                <div class="requirement-item">
                                                                    <i
                                                                        class="material-symbols-rounded text-success">check_circle</i>
                                                                    <span>Recommended: 200x200px (square)</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="material-symbols-rounded me-1">save</i>
                                        Save School Info
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Theme Customization -->
                <div class="col-12">
                    <div class="card my-4 glassmorphism-card theme-customization-card">
                        <div class="card-header pb-0 position-relative">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle-gradient me-3">
                                        <i class="material-symbols-rounded">palette</i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ __('settings.theme_customization') }}</h6>
                                        <small class="text-muted">Customize your brand colors and visual identity</small>
                                    </div>
                                </div>
                                <div class="theme-preview-toggle">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="toggleLivePreview()">
                                        <i class="material-symbols-rounded me-1">visibility</i>
                                        <span id="preview-toggle-text">Live Preview</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <form id="theme-form">
                                @csrf

                                <!-- Quick Color Presets -->
                                <div class="theme-section mb-5">
                                    <div class="section-header mb-4">
                                        <h6 class="section-title">
                                            <i class="material-symbols-rounded me-2">auto_awesome</i>
                                            Quick Theme Presets
                                        </h6>
                                        <p class="section-subtitle">Choose from pre-designed color schemes</p>
                                    </div>
                                    <div class="preset-grid">
                                        <div class="preset-card"
                                            onclick="applyColorPreset('#06C167', '#10B981', '#F0FDF4')">
                                            <div class="preset-preview">
                                                <div class="color-strip" style="background: #06C167;"></div>
                                                <div class="color-strip" style="background: #10B981;"></div>
                                                <div class="color-strip" style="background: #F0FDF4;"></div>
                                            </div>
                                            <span class="preset-name">Nature Green</span>
                                        </div>
                                        <div class="preset-card"
                                            onclick="applyColorPreset('#3B82F6', '#1D4ED8', '#EFF6FF')">
                                            <div class="preset-preview">
                                                <div class="color-strip" style="background: #3B82F6;"></div>
                                                <div class="color-strip" style="background: #1D4ED8;"></div>
                                                <div class="color-strip" style="background: #EFF6FF;"></div>
                                            </div>
                                            <span class="preset-name">Ocean Blue</span>
                                        </div>
                                        <div class="preset-card"
                                            onclick="applyColorPreset('#8B5CF6', '#7C3AED', '#F3E8FF')">
                                            <div class="preset-preview">
                                                <div class="color-strip" style="background: #8B5CF6;"></div>
                                                <div class="color-strip" style="background: #7C3AED;"></div>
                                                <div class="color-strip" style="background: #F3E8FF;"></div>
                                            </div>
                                            <span class="preset-name">Purple Dream</span>
                                        </div>
                                        <div class="preset-card"
                                            onclick="applyColorPreset('#F59E0B', '#D97706', '#FEF3C7')">
                                            <div class="preset-preview">
                                                <div class="color-strip" style="background: #F59E0B;"></div>
                                                <div class="color-strip" style="background: #D97706;"></div>
                                                <div class="color-strip" style="background: #FEF3C7;"></div>
                                            </div>
                                            <span class="preset-name">Golden Sun</span>
                                        </div>
                                        <div class="preset-card"
                                            onclick="applyColorPreset('#EF4444', '#DC2626', '#FEF2F2')">
                                            <div class="preset-preview">
                                                <div class="color-strip" style="background: #EF4444;"></div>
                                                <div class="color-strip" style="background: #DC2626;"></div>
                                                <div class="color-strip" style="background: #FEF2F2;"></div>
                                            </div>
                                            <span class="preset-name">Vibrant Red</span>
                                        </div>
                                        <div class="preset-card"
                                            onclick="applyColorPreset('#059669', '#047857', '#ECFDF5')">
                                            <div class="preset-preview">
                                                <div class="color-strip" style="background: #059669;"></div>
                                                <div class="color-strip" style="background: #047857;"></div>
                                                <div class="color-strip" style="background: #ECFDF5;"></div>
                                            </div>
                                            <span class="preset-name">Forest Green</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Primary Brand Colors -->
                                <div class="theme-section mb-5">
                                    <div class="section-header mb-4">
                                        <h6 class="section-title">
                                            <i class="material-symbols-rounded me-2">brand_awareness</i>
                                            Primary Brand Colors
                                        </h6>
                                        <p class="section-subtitle">Define your main brand identity colors</p>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-lg-4 col-md-6">
                                            <div class="color-input-card">
                                                <label class="color-label">
                                                    <i class="material-symbols-rounded me-2">palette</i>
                                                    Primary Color
                                                </label>
                                                <div class="color-picker-enhanced">
                                                    <div class="color-preview-circle"
                                                        style="background: {{ $setting->primary_color ?? '#06C167' }};"
                                                        onclick="document.getElementById('primary_color').click()"></div>
                                                    <div class="color-inputs">
                                                        <input type="color" class="color-input-hidden"
                                                            id="primary_color" name="primary_color"
                                                            value="{{ $setting->primary_color ?? '#06C167' }}"
                                                            onchange="updateThemePreview()">
                                                        <input type="text" class="form-control color-hex-input"
                                                            id="primary_color_text" name="primary_color_text"
                                                            value="{{ $setting->primary_color ?? '#06C167' }}"
                                                            onchange="updateColorFromText('primary_color')"
                                                            placeholder="#06C167">
                                                    </div>
                                                </div>
                                                <small class="color-description">Main brand color for buttons and
                                                    highlights</small>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="color-input-card">
                                                <label class="color-label">
                                                    <i class="material-symbols-rounded me-2">gradient</i>
                                                    Secondary Color
                                                </label>
                                                <div class="color-picker-enhanced">
                                                    <div class="color-preview-circle"
                                                        style="background: {{ $setting->secondary_color ?? '#10B981' }};"
                                                        onclick="document.getElementById('secondary_color').click()"></div>
                                                    <div class="color-inputs">
                                                        <input type="color" class="color-input-hidden"
                                                            id="secondary_color" name="secondary_color"
                                                            value="{{ $setting->secondary_color ?? '#10B981' }}"
                                                            onchange="updateThemePreview()">
                                                        <input type="text" class="form-control color-hex-input"
                                                            id="secondary_color_text" name="secondary_color_text"
                                                            value="{{ $setting->secondary_color ?? '#10B981' }}"
                                                            onchange="updateColorFromText('secondary_color')"
                                                            placeholder="#10B981">
                                                    </div>
                                                </div>
                                                <small class="color-description">Supporting color for accents and
                                                    variations</small>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="color-input-card">
                                                <label class="color-label">
                                                    <i class="material-symbols-rounded me-2">brightness_5</i>
                                                    Accent Color
                                                </label>
                                                <div class="color-picker-enhanced">
                                                    <div class="color-preview-circle"
                                                        style="background: {{ $setting->accent_color ?? '#F0FDF4' }};"
                                                        onclick="document.getElementById('accent_color').click()"></div>
                                                    <div class="color-inputs">
                                                        <input type="color" class="color-input-hidden"
                                                            id="accent_color" name="accent_color"
                                                            value="{{ $setting->accent_color ?? '#F0FDF4' }}"
                                                            onchange="updateThemePreview()">
                                                        <input type="text" class="form-control color-hex-input"
                                                            id="accent_color_text" name="accent_color_text"
                                                            value="{{ $setting->accent_color ?? '#F0FDF4' }}"
                                                            onchange="updateColorFromText('accent_color')"
                                                            placeholder="#F0FDF4">
                                                    </div>
                                                </div>
                                                <small class="color-description">Light background and subtle
                                                    accents</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status & Alert Colors -->
                                <div class="theme-section mb-5">
                                    <div class="section-header mb-4">
                                        <h6 class="section-title">
                                            <i class="material-symbols-rounded me-2">notification_important</i>
                                            Status & Alert Colors
                                        </h6>
                                        <p class="section-subtitle">Colors for notifications, alerts, and status indicators
                                        </p>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-lg-3 col-md-6">
                                            <div class="status-color-card success-theme">
                                                <div class="status-header">
                                                    <i class="material-symbols-rounded">check_circle</i>
                                                    <span>Success</span>
                                                </div>
                                                <div class="color-picker-enhanced mini">
                                                    <div class="color-preview-circle"
                                                        style="background: {{ $setting->success_color ?? '#10B981' }};"
                                                        onclick="document.getElementById('success-color').click()"></div>
                                                    <div class="color-inputs">
                                                        <input type="color" class="color-input-hidden"
                                                            id="success-color" name="success_color"
                                                            value="{{ $setting->success_color ?? '#10B981' }}"
                                                            onchange="updateThemePreview()">
                                                        <input type="text" class="form-control color-hex-input"
                                                            id="success-color-text" name="success_color_text"
                                                            value="{{ $setting->success_color ?? '#10B981' }}"
                                                            onchange="updateColorFromText('success-color')"
                                                            placeholder="#10B981">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <div class="status-color-card info-theme">
                                                <div class="status-header">
                                                    <i class="material-symbols-rounded">info</i>
                                                    <span>Info</span>
                                                </div>
                                                <div class="color-picker-enhanced mini">
                                                    <div class="color-preview-circle"
                                                        style="background: {{ $setting->info_color ?? '#3B82F6' }};"
                                                        onclick="document.getElementById('info-color').click()"></div>
                                                    <div class="color-inputs">
                                                        <input type="color" class="color-input-hidden" id="info-color"
                                                            name="info_color"
                                                            value="{{ $setting->info_color ?? '#3B82F6' }}"
                                                            onchange="updateThemePreview()">
                                                        <input type="text" class="form-control color-hex-input"
                                                            id="info-color-text" name="info_color_text"
                                                            value="{{ $setting->info_color ?? '#3B82F6' }}"
                                                            onchange="updateColorFromText('info-color')"
                                                            placeholder="#3B82F6">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <div class="status-color-card warning-theme">
                                                <div class="status-header">
                                                    <i class="material-symbols-rounded">warning</i>
                                                    <span>Warning</span>
                                                </div>
                                                <div class="color-picker-enhanced mini">
                                                    <div class="color-preview-circle"
                                                        style="background: {{ $setting->warning_color ?? '#F59E0B' }};"
                                                        onclick="document.getElementById('warning-color').click()"></div>
                                                    <div class="color-inputs">
                                                        <input type="color" class="color-input-hidden"
                                                            id="warning-color" name="warning_color"
                                                            value="{{ $setting->warning_color ?? '#F59E0B' }}"
                                                            onchange="updateThemePreview()">
                                                        <input type="text" class="form-control color-hex-input"
                                                            id="warning-color-text" name="warning_color_text"
                                                            value="{{ $setting->warning_color ?? '#F59E0B' }}"
                                                            onchange="updateColorFromText('warning-color')"
                                                            placeholder="#F59E0B">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <div class="status-color-card danger-theme">
                                                <div class="status-header">
                                                    <i class="material-symbols-rounded">error</i>
                                                    <span>Danger</span>
                                                </div>
                                                <div class="color-picker-enhanced mini">
                                                    <div class="color-preview-circle"
                                                        style="background: {{ $setting->danger_color ?? '#EF4444' }};"
                                                        onclick="document.getElementById('danger-color').click()"></div>
                                                    <div class="color-inputs">
                                                        <input type="color" class="color-input-hidden"
                                                            id="danger-color" name="danger_color"
                                                            value="{{ $setting->danger_color ?? '#EF4444' }}"
                                                            onchange="updateThemePreview()">
                                                        <input type="text" class="form-control color-hex-input"
                                                            id="danger-color-text" name="danger_color_text"
                                                            value="{{ $setting->danger_color ?? '#EF4444' }}"
                                                            onchange="updateColorFromText('danger-color')"
                                                            placeholder="#EF4444">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Gradient Configurations -->
                                <div class="theme-section mb-4">
                                    <div class="section-header mb-4">
                                        <h6 class="section-title">
                                            <i class="material-symbols-rounded me-2">gradient</i>
                                            Gradient Configurations
                                        </h6>
                                        <p class="section-subtitle">Create beautiful gradient combinations for enhanced
                                            visual appeal</p>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="gradient-config-card">
                                                <div class="gradient-header">
                                                    <h6 class="gradient-title">Primary Gradient</h6>
                                                    <small class="text-muted">Used for buttons and key elements</small>
                                                </div>
                                                <div class="gradient-controls">
                                                    <div class="row g-3">
                                                        <div class="col-6">
                                                            <label class="gradient-label">Start Color</label>
                                                            <div class="color-picker-enhanced mini">
                                                                <div class="color-preview-circle"
                                                                    style="background: {{ $setting->primary_gradient_start ?? '#06C167' }};"
                                                                    onclick="document.getElementById('primary-gradient-start').click()">
                                                                </div>
                                                                <div class="color-inputs">
                                                                    <input type="color" class="color-input-hidden"
                                                                        id="primary-gradient-start"
                                                                        name="primary_gradient_start"
                                                                        value="{{ $setting->primary_gradient_start ?? '#06C167' }}"
                                                                        onchange="updateGradientPreview('primary')">
                                                                    <input type="text"
                                                                        class="form-control color-hex-input"
                                                                        id="primary-gradient-start-text"
                                                                        name="primary_gradient_start_text"
                                                                        value="{{ $setting->primary_gradient_start ?? '#06C167' }}"
                                                                        onchange="updateColorFromText('primary-gradient-start')"
                                                                        placeholder="#06C167">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="gradient-label">End Color</label>
                                                            <div class="color-picker-enhanced mini">
                                                                <div class="color-preview-circle"
                                                                    style="background: {{ $setting->primary_gradient_end ?? '#10B981' }};"
                                                                    onclick="document.getElementById('primary-gradient-end').click()">
                                                                </div>
                                                                <div class="color-inputs">
                                                                    <input type="color" class="color-input-hidden"
                                                                        id="primary-gradient-end"
                                                                        name="primary_gradient_end"
                                                                        value="{{ $setting->primary_gradient_end ?? '#10B981' }}"
                                                                        onchange="updateGradientPreview('primary')">
                                                                    <input type="text"
                                                                        class="form-control color-hex-input"
                                                                        id="primary-gradient-end-text"
                                                                        name="primary_gradient_end_text"
                                                                        value="{{ $setting->primary_gradient_end ?? '#10B981' }}"
                                                                        onchange="updateColorFromText('primary-gradient-end')"
                                                                        placeholder="#10B981">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="gradient-preview-enhanced" id="primary-gradient-preview"
                                                    style="background: linear-gradient(135deg, {{ $setting->primary_gradient_start ?? '#06C167' }}, {{ $setting->primary_gradient_end ?? '#10B981' }});">
                                                    <span class="gradient-preview-text">Primary Gradient Preview</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="gradient-config-card">
                                                <div class="gradient-header">
                                                    <h6 class="gradient-title">Secondary Gradient</h6>
                                                    <small class="text-muted">Used for backgrounds and secondary
                                                        elements</small>
                                                </div>
                                                <div class="gradient-controls">
                                                    <div class="row g-3">
                                                        <div class="col-6">
                                                            <label class="gradient-label">Start Color</label>
                                                            <div class="color-picker-enhanced mini">
                                                                <div class="color-preview-circle"
                                                                    style="background: {{ $setting->secondary_gradient_start ?? '#8B5CF6' }};"
                                                                    onclick="document.getElementById('secondary-gradient-start').click()">
                                                                </div>
                                                                <div class="color-inputs">
                                                                    <input type="color" class="color-input-hidden"
                                                                        id="secondary-gradient-start"
                                                                        name="secondary_gradient_start"
                                                                        value="{{ $setting->secondary_gradient_start ?? '#8B5CF6' }}"
                                                                        onchange="updateGradientPreview('secondary')">
                                                                    <input type="text"
                                                                        class="form-control color-hex-input"
                                                                        id="secondary-gradient-start-text"
                                                                        name="secondary_gradient_start_text"
                                                                        value="{{ $setting->secondary_gradient_start ?? '#8B5CF6' }}"
                                                                        onchange="updateColorFromText('secondary-gradient-start')"
                                                                        placeholder="#8B5CF6">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="gradient-label">End Color</label>
                                                            <div class="color-picker-enhanced mini">
                                                                <div class="color-preview-circle"
                                                                    style="background: {{ $setting->secondary_gradient_end ?? '#EC4899' }};"
                                                                    onclick="document.getElementById('secondary-gradient-end').click()">
                                                                </div>
                                                                <div class="color-inputs">
                                                                    <input type="color" class="color-input-hidden"
                                                                        id="secondary-gradient-end"
                                                                        name="secondary_gradient_end"
                                                                        value="{{ $setting->secondary_gradient_end ?? '#EC4899' }}"
                                                                        onchange="updateGradientPreview('secondary')">
                                                                    <input type="text"
                                                                        class="form-control color-hex-input"
                                                                        id="secondary-gradient-end-text"
                                                                        name="secondary_gradient_end_text"
                                                                        value="{{ $setting->secondary_gradient_end ?? '#EC4899' }}"
                                                                        onchange="updateColorFromText('secondary-gradient-end')"
                                                                        placeholder="#EC4899">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="gradient-preview-enhanced" id="secondary-gradient-preview"
                                                    style="background: linear-gradient(135deg, {{ $setting->secondary_gradient_start ?? '#8B5CF6' }}, {{ $setting->secondary_gradient_end ?? '#EC4899' }});">
                                                    <span class="gradient-preview-text">Secondary Gradient Preview</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-between align-items-center mt-4 pt-3"
                                    style="border-top: 1px solid rgba(0,0,0,0.1);">
                                    <button type="button" class="btn btn-outline-secondary" onclick="resetToDefault()">
                                        <i class="material-symbols-rounded me-2">refresh</i>
                                        Reset to Default
                                    </button>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary" onclick="exportTheme()">
                                            <i class="material-symbols-rounded me-2">file_download</i>
                                            Export Theme
                                        </button>
                                        <button type="submit" class="btn btn-primary btn-gradient">
                                            <i class="material-symbols-rounded me-2">save</i>
                                            Save Theme Settings
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Academic Settings -->
                <div class="col-12">
                    <div class="card my-4 glassmorphism-card">
                        <div class="card-header pb-0">
                            <div class="d-flex align-items-center">
                                <i class="material-symbols-rounded me-2">schedule</i>
                                <h6 class="mb-0">{{ __('settings.academic_settings') }}</h6>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <form id="academic-form">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="academic_year_start"
                                                class="form-label">{{ __('school.academic_year_starts') }}</label>
                                            <div class="input-group input-group-outline">
                                                <select class="form-control" name="academic_year_start"
                                                    id="academic_year_start" required>
                                                    <option value="" disabled selected>
                                                        {{ __('school.academic_year_starts') }}</option>
                                                    @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                                        <option value="{{ $month }}"
                                                            {{ ($setting->academic_year_start ?? 'January') === $month ? 'selected' : '' }}>
                                                            {{ __('settings.' . strtolower($month)) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="academic_year_end"
                                                class="form-label">{{ __('school.academic_year_ends') }}</label>
                                            <div class="input-group input-group-outline">
                                                <select class="form-control" name="academic_year_end"
                                                    id="academic_year_end" required>
                                                    <option value="" disabled selected>
                                                        {{ __('school.academic_year_ends') }}</option>
                                                    @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                                        <option value="{{ $month }}"
                                                            {{ ($setting->academic_year_end ?? 'December') === $month ? 'selected' : '' }}>
                                                            {{ __('settings.' . strtolower($month)) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">{{ __('school.school_start_time') }}</label>
                                            <input type="time" class="form-control" name="school_start_time" required
                                                value="{{ $setting->school_start_time ? \Carbon\Carbon::parse($setting->school_start_time)->format('H:i') : '08:00' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">{{ __('school.school_end_time') }}</label>
                                            <input type="time" class="form-control" name="school_end_time" required
                                                value="{{ $setting->school_end_time ? \Carbon\Carbon::parse($setting->school_end_time)->format('H:i') : '15:00' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="material-symbols-rounded me-1">save</i>
                                        {{ __('school.save_academic_settings') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Language Settings -->
                <div class="col-12">
                    <div class="card my-4 glassmorphism-card">
                        <div class="card-header pb-0">
                            <div class="d-flex align-items-center">
                                <i class="material-symbols-rounded me-2">language</i>
                                <h6 class="mb-0">{{ __('settings.language_settings') }}</h6>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <form id="language-form">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">{{ __('settings.language') }}</label>
                                            <select class="form-control" name="language" required
                                                onchange="previewLanguageChange(this.value)">
                                                <option value="">{{ __('settings.select_language') }}</option>
                                                <option value="en"
                                                    {{ ($setting->language ?? 'en') === 'en' ? 'selected' : '' }}>
                                                    {{ __('settings.english') }}
                                                </option>
                                                <option value="si"
                                                    {{ ($setting->language ?? 'en') === 'si' ? 'selected' : '' }}>
                                                    {{ __('settings.sinhala') }}
                                                </option>
                                            </select>
                                        </div>
                                        <small class="text-muted">{{ __('common.preview_below') }}</small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="language-preview p-3"
                                            style="background: #f8f9fa; border-radius: 8px;">
                                            <h6 class="mb-2" id="preview-title">{{ __('settings.language') }}</h6>
                                            <p class="mb-1 text-sm" id="preview-dashboard">{{ __('common.dashboard') }}
                                            </p>
                                            <p class="mb-1 text-sm" id="preview-settings">{{ __('common.settings') }}</p>
                                            <p class="mb-0 text-sm" id="preview-save">{{ __('common.save') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="material-symbols-rounded me-1">save</i>
                                        {{ __('common.save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        /* Enhanced Theme Customization Styles */
        .theme - customization - card {
                background: rgba(255, 255, 255, 0.98);
                backdrop - filter: blur(20 px);
                border: 1 px solid rgba(255, 255, 255, 0.3);
                box - shadow: 0 12 px 40 px rgba(0, 0, 0, 0.08);
                border - radius: 16 px;
                overflow: hidden;
            }

            .icon - circle - gradient {
                width: 48 px;
                height: 48 px;
                background: linear - gradient(135 deg, #06C167, # 10 B981);
                border - radius: 12 px;
                display: flex;
                align - items: center;
                justify - content: center;
                color: white;
                font - size: 24 px;
            }

            .theme - section {
                position: relative;
                padding: 24 px;
                background: rgba(248, 250, 252, 0.6);
                border - radius: 12 px;
                border: 1 px solid rgba(226, 232, 240, 0.8);
            }

            .section - header {
                text - align: center;
                margin - bottom: 24 px;
            }

            .section - title {
                font - size: 18 px;
                font - weight: 600;
                color: #1e293b;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 8px;
            }

            .section-subtitle {
                color: # 64748 b;
                margin: 0;
                font - size: 14 px;
            }

            /* Preset Grid */
            .preset - grid {
                display: grid;
                grid - template - columns: repeat(auto - fit, minmax(160 px, 1 fr));
                gap: 16 px;
            }

            .preset - card {
                background: white;
                border - radius: 12 px;
                padding: 16 px;
                border: 2 px solid transparent;
                cursor: pointer;
                transition: all 0.3 s cubic - bezier(0.4, 0, 0.2, 1);
                box - shadow: 0 4 px 12 px rgba(0, 0, 0, 0.05);
            }

            .preset - card: hover {
                transform: translateY(-4 px);
                box - shadow: 0 12 px 32 px rgba(0, 0, 0, 0.12);
                border - color: #06C167;
            }

            .preset-preview {
                display: flex;
                height: 40px;
                border-radius: 8px;
                overflow: hidden;
                margin-bottom: 12px;
            }

            .color-strip {
                flex: 1;
                transition: transform 0.3s ease;
            }

            .preset-card:hover .color-strip {
                transform: scale(1.05);
            }

            .preset-name {
                font-weight: 500;
                color: # 374151;
                font - size: 14 px;
                text - align: center;
                display: block;
            }

            /* Enhanced Color Inputs */
            .color - input - card {
                background: white;
                border - radius: 12 px;
                padding: 20 px;
                border: 1 px solid rgba(226, 232, 240, 0.8);
                transition: all 0.3 s ease;
                height: 100 % ;
            }

            .color - input - card: hover {
                border - color: #06C167;
                box-shadow: 0 8px 24px rgba(6, 193, 103, 0.12);
            }

            .color-label {
                font-weight: 600;
                color: # 1e293 b;
                margin - bottom: 16 px;
                display: flex;
                align - items: center;
                font - size: 14 px;
            }

            .color - picker - enhanced {
                display: flex;
                align - items: center;
                gap: 12 px;
                margin - bottom: 12 px;
            }

            .color - picker - enhanced.mini {
                gap: 8 px;
                margin - bottom: 8 px;
            }

            .color - preview - circle {
                width: 48 px;
                height: 48 px;
                border - radius: 50 % ;
                border: 3 px solid white;
                box - shadow: 0 4 px 12 px rgba(0, 0, 0, 0.15),
                inset 0 0 0 1 px rgba(0, 0, 0, 0.1);
                cursor: pointer;
                transition: all 0.3 s ease;
                position: relative;
            }

            .color - picker - enhanced.mini.color - preview - circle {
                width: 36 px;
                height: 36 px;
            }

            .color - preview - circle: hover {
                transform: scale(1.1);
                box - shadow: 0 6 px 20 px rgba(0, 0, 0, 0.2),
                inset 0 0 0 1 px rgba(0, 0, 0, 0.1);
            }

            .color - input - hidden {
                display: none;
            }

            .color - inputs {
                flex: 1;
            }

            .color - hex - input {
                font - family: 'JetBrains Mono', 'Monaco', monospace;
                font - size: 12 px;
                text - transform: uppercase;
                font - weight: 500;
                border: 1 px solid #e2e8f0;
                border - radius: 8 px;
                padding: 8 px 12 px;
                background: #f8fafc;
                transition: all 0.3 s ease;
            }

            .color - hex - input: focus {
                border - color: #06C167;
                box-shadow: 0 0 0 3px rgba(6, 193, 103, 0.1);
                background: white;
            }

            .color-description {
                color: # 64748 b;
                font - size: 12 px;
                line - height: 1.4;
            }

            /* Status Color Cards */
            .status - color - card {
                background: white;
                border - radius: 12 px;
                padding: 16 px;
                border: 1 px solid rgba(226, 232, 240, 0.8);
                transition: all 0.3 s ease;
                height: 100 % ;
            }

            .status - color - card: hover {
                transform: translateY(-2 px);
                box - shadow: 0 8 px 24 px rgba(0, 0, 0, 0.08);
            }

            .status - color - card.success - theme: hover {
                border - color: #10B981;
                box-shadow: 0 8px 24px rgba(16, 185, 129, 0.15);
            }

            .status-color-card.info-theme:hover {
                border-color: # 3 B82F6;
                box - shadow: 0 8 px 24 px rgba(59, 130, 246, 0.15);
            }

            .status - color - card.warning - theme: hover {
                border - color: #F59E0B;
                box - shadow: 0 8 px 24 px rgba(245, 158, 11, 0.15);
            }

            .status - color - card.danger - theme: hover {
                border - color: #EF4444;
                box - shadow: 0 8 px 24 px rgba(239, 68, 68, 0.15);
            }

            .status - header {
                display: flex;
                align - items: center;
                gap: 8 px;
                margin - bottom: 16 px;
                font - weight: 600;
                color: #374151;
            }

            /* Gradient Configuration Cards */
            .gradient-config-card {
                background: white;
                border-radius: 12px;
                padding: 20px;
                border: 1px solid rgba(226, 232, 240, 0.8);
                transition: all 0.3s ease;
                height: 100%;
            }

            .gradient-config-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
                border-color: # 06 C167;
            }

            .gradient - header {
                margin - bottom: 20 px;
            }

            .gradient - title {
                font - weight: 600;
                color: #1e293b;
                margin-bottom: 4px;
            }

            .gradient-controls {
                margin-bottom: 20px;
            }

            .gradient-label {
                font-size: 12px;
                font-weight: 500;
                color: # 64748 b;
                margin - bottom: 8 px;
                display: block;
            }

            .gradient - preview - enhanced {
                height: 60 px;
                border - radius: 12 px;
                display: flex;
                align - items: center;
                justify - content: center;
                color: white;
                font - weight: 600;
                text - shadow: 0 2 px 4 px rgba(0, 0, 0, 0.3);
                box - shadow: 0 4 px 12 px rgba(0, 0, 0, 0.15);
                transition: all 0.3 s ease;
                position: relative;
                overflow: hidden;
            }

            .gradient - preview - enhanced: hover {
                transform: scale(1.02);
                box - shadow: 0 8 px 24 px rgba(0, 0, 0, 0.2);
            }

            .gradient - preview - text {
                font - size: 14 px;
                z - index: 2;
                position: relative;
            }

            /* Enhanced Buttons */
            .btn - gradient {
                background: linear - gradient(135 deg, #06C167, # 10 B981);
                border: none;
                color: white;
                font - weight: 600;
                padding: 12 px 24 px;
                border - radius: 8 px;
                transition: all 0.3 s ease;
            }

            .btn - gradient: hover {
                transform: translateY(-2 px);
                box - shadow: 0 8 px 24 px rgba(6, 193, 103, 0.3);
                background: linear - gradient(135 deg, #059669, # 047857);
            }

            /* Legacy color picker styles */
            .color - picker - group {
                display: flex;
                gap: 8 px;
                align - items: center;
            }

            .color - picker {
                width: 60 px;
                height: 40 px;
                border: none;
                border - radius: 8 px;
                cursor: pointer;
                padding: 0;
            }

            .color - text {
                flex: 1;
                font - family: monospace;
                text - transform: uppercase;
            }

            .color - presets {
                margin - top: 8 px;
            }

            .color - preset {
                border: 2 px solid transparent;
                border - radius: 8 px;
                color: white;
                font - weight: 500;
                text - shadow: 0 1 px 2 px rgba(0, 0, 0, 0.3);
                transition: all 0.3 s ease;
            }

            .color - preset: hover {
                transform: translateY(-2 px);
                box - shadow: 0 4 px 12 px rgba(0, 0, 0, 0.2);
            }

            .glassmorphism - card {
                background: rgba(255, 255, 255, 0.95);
                backdrop - filter: blur(20 px);
                border: 1 px solid rgba(255, 255, 255, 0.2);
                box - shadow: 0 8 px 32 px rgba(0, 0, 0, 0.1);
            }

        /* Responsive Design */
        @media(max - width: 768 px) {
            .preset - grid {
                    grid - template - columns: repeat(2, 1 fr);
                }

                .color - preview - circle {
                    width: 40 px;
                    height: 40 px;
                }

                .color - picker - enhanced.mini.color - preview - circle {
                    width: 32 px;
                    height: 32 px;
                }

                .theme - section {
                    padding: 16 px;
                }
        }

        /* Animation for smooth transitions */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20 px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .theme - section {
                animation: fadeInUp 0.6 s ease - out;
            } <
            script >
            // Logo preview callback for immediate sidebar update
            window.onFilePreviewLogo = function(dataUrl, file) {
                // Update sidebar logo immediately with preview
                const sidebarLogo = document.querySelector('.sidebar-logo');
                if (sidebarLogo) {
                    sidebarLogo.src = dataUrl;
                }
            };

        // Beautiful logo upload handler
        function handleLogoUpload(event) {
            const file = event.target.files[0];
            const previewWrapper = document.getElementById('logo-preview-wrapper');
            const maxSize = 2048 * 1024; // 2MB in bytes
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

            if (!file) return;

            // Validate file size
            if (file.size > maxSize) {
                showNotification('File size must be less than 2MB', 'error');
                event.target.value = '';
                return;
            }

            // Validate file type
            if (!allowedTypes.includes(file.type)) {
                showNotification('Please select a valid image file (JPG, PNG, GIF, WebP)', 'error');
                event.target.value = '';
                return;
            }

            // Show loading state
            previewWrapper.innerHTML = `
                <div class="logo-placeholder">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="logo-placeholder-text mt-2">Uploading...</p>
                </div>
            `;

            const reader = new FileReader();
            reader.onload = function(e) {
                // Create new preview with image
                previewWrapper.innerHTML = `
                    <img id="logo-preview" src="${e.target.result}"
                         alt="School Logo Preview" class="logo-preview-image upload-success">
                    <div class="logo-overlay">
                        <i class="material-symbols-rounded">edit</i>
                    </div>
                `;

                // Add click handler to preview for re-upload
                previewWrapper.onclick = function() {
                    document.getElementById('logo').click();
                };

                // Update sidebar logo immediately
                const sidebarLogo = document.querySelector('.sidebar-logo');
                if (sidebarLogo) {
                    sidebarLogo.src = e.target.result;
                }

                // Show success notification
                showNotification('Logo uploaded successfully!', 'success');
            };

            reader.onerror = function() {
                showNotification('Error reading file', 'error');
                resetLogoPlaceholder();
            };

            reader.readAsDataURL(file);
        }

        // Remove logo function
        function removeLogo() {
            if (confirm('Are you sure you want to remove the school logo?')) {
                document.getElementById('logo').value = '';
                resetLogoPlaceholder();

                // Reset sidebar logo to default
                const sidebarLogo = document.querySelector('.sidebar-logo');
                if (sidebarLogo) {
                    sidebarLogo.src = '{{ asset('assets/img/default-logo.png') }}'; // Add your default logo path
                }

                showNotification('Logo removed successfully!', 'success');
            }
        }

        // Reset logo placeholder
        function resetLogoPlaceholder() {
            const previewWrapper = document.getElementById('logo-preview-wrapper');
            previewWrapper.innerHTML = `
                <div class="logo-placeholder" id="logo-placeholder">
                    <i class="material-symbols-rounded logo-placeholder-icon">add_photo_alternate</i>
                    <p class="logo-placeholder-text">Click to upload logo</p>
                </div>
            `;

            // Add click handler to placeholder
            previewWrapper.onclick = function() {
                document.getElementById('logo').click();
            };
        }

        // Add click handler to logo preview wrapper on page load
        document.addEventListener('DOMContentLoaded', function() {
            const previewWrapper = document.getElementById('logo-preview-wrapper');
            if (previewWrapper) {
                previewWrapper.onclick = function() {
                    document.getElementById('logo').click();
                };
            }
        });

        // Theme customization functions
        function updateThemePreview() {
            const primaryColor = document.getElementById("primary_color").value;
            const secondaryColor = document.getElementById("secondary_color").value;
            const accentColor = document.getElementById("accent_color").value;

            // Update text inputs for primary colors
            document.getElementById("primary_color_text").value = primaryColor;
            document.getElementById("secondary_color_text").value = secondaryColor;
            document.getElementById("accent_color_text").value = accentColor;

            // Get status colors if they exist
            const successColor = document.getElementById("success-color")?.value || '#10B981';
            const infoColor = document.getElementById("info-color")?.value || '#3B82F6';
            const warningColor = document.getElementById("warning-color")?.value || '#F59E0B';
            const dangerColor = document.getElementById("danger-color")?.value || '#EF4444';

            // Update status color text inputs
            if (document.getElementById("success-color-text")) {
                document.getElementById("success-color-text").value = successColor;
            }
            if (document.getElementById("info-color-text")) {
                document.getElementById("info-color-text").value = infoColor;
            }
            if (document.getElementById("warning-color-text")) {
                document.getElementById("warning-color-text").value = warningColor;
            }
            if (document.getElementById("danger-color-text")) {
                document.getElementById("danger-color-text").value = dangerColor;
            }

            // Apply comprehensive theme colors
            const root = document.documentElement;
            root.style.setProperty('--primary-green', primaryColor);
            root.style.setProperty('--light-green', secondaryColor);
            root.style.setProperty('--dark-green', secondaryColor);
            root.style.setProperty('--accent-green', accentColor);
            root.style.setProperty('--success-green', successColor);
            root.style.setProperty('--info-blue', infoColor);
            root.style.setProperty('--warning-orange', warningColor);
            root.style.setProperty('--danger-red', dangerColor);

            // Convert colors to RGB for rgba usage
            const primaryRgb = hexToRgb(primaryColor);
            const secondaryRgb = hexToRgb(secondaryColor);
            const accentRgb = hexToRgb(accentColor);

            if (primaryRgb) {
                root.style.setProperty('--primary-rgb', `${primaryRgb.r}, ${primaryRgb.g}, ${primaryRgb.b}`);
            }
            if (secondaryRgb) {
                root.style.setProperty('--secondary-rgb', `${secondaryRgb.r}, ${secondaryRgb.g}, ${secondaryRgb.b}`);
            }
            if (accentRgb) {
                root.style.setProperty('--accent-rgb', `${accentRgb.r}, ${accentRgb.g}, ${accentRgb.b}`);
            }

            // Apply colors immediately for preview
            applyThemeColors(primaryColor, secondaryColor, accentColor);

            // Update gradient previews
            updateGradientPreview('primary');
            updateGradientPreview('secondary');

            // Show preview badge
            showColorPreview(primaryColor, secondaryColor, accentColor);

            console.log('Comprehensive theme colors applied:', {
                primaryColor,
                secondaryColor,
                accentColor,
                successColor,
                infoColor,
                warningColor,
                dangerColor
            });
        }

        function updateGradientPreview(type) {
            const startColor = document.getElementById(`${type}-gradient-start`)?.value;
            const endColor = document.getElementById(`${type}-gradient-end`)?.value;

            if (startColor && endColor) {
                const preview = document.getElementById(`${type}-gradient-preview`);
                if (preview) {
                    preview.style.background = `linear-gradient(135deg, ${startColor}, ${endColor})`;
                }

                // Update text inputs
                const startText = document.getElementById(`${type}-gradient-start-text`);
                const endText = document.getElementById(`${type}-gradient-end-text`);
                if (startText) startText.value = startColor;
                if (endText) endText.value = endColor;

                // Update color preview circles
                const startCircle = document.querySelector(`#${type}-gradient-start`).parentElement.querySelector(
                    '.color-preview-circle');
                const endCircle = document.querySelector(`#${type}-gradient-end`).parentElement.querySelector(
                    '.color-preview-circle');
                if (startCircle) startCircle.style.background = startColor;
                if (endCircle) endCircle.style.background = endColor;

                // Apply gradient to theme system
                const root = document.documentElement;
                if (type === 'primary') {
                    root.style.setProperty('--primary-gradient-start', startColor);
                    root.style.setProperty('--primary-gradient-end', endColor);
                } else if (type === 'secondary') {
                    root.style.setProperty('--secondary-gradient-start', startColor);
                    root.style.setProperty('--secondary-gradient-end', endColor);
                }
            }
        }

        // Enhanced color update function
        function updateColorFromText(colorType) {
            const textInput = document.getElementById(colorType.replace('-', '_') + "_text") || document.getElementById(
                colorType + "-text");
            const colorInput = document.getElementById(colorType.replace('_', '-'));
            const colorCircle = colorInput?.parentElement.querySelector('.color-preview-circle');

            if (textInput && isValidHexColor(textInput.value)) {
                if (colorInput) {
                    colorInput.value = textInput.value;
                }
                if (colorCircle) {
                    colorCircle.style.background = textInput.value;
                }

                // Update theme preview or gradient preview
                if (colorType.includes('gradient')) {
                    const gradientType = colorType.includes('primary') ? 'primary' : 'secondary';
                    updateGradientPreview(gradientType);
                } else {
                    updateThemePreview();
                }
            } else if (textInput) {
                // Show error for invalid color
                textInput.style.borderColor = '#EF4444';
                setTimeout(() => {
                    textInput.style.borderColor = '';
                }, 2000);
            }
        }

        // Enhanced theme preview with color circle updates
        function updateThemePreview() {
            const primaryColor = document.getElementById("primary_color").value;
            const secondaryColor = document.getElementById("secondary_color").value;
            const accentColor = document.getElementById("accent_color").value;

            // Update text inputs for primary colors
            document.getElementById("primary_color_text").value = primaryColor;
            document.getElementById("secondary_color_text").value = secondaryColor;
            document.getElementById("accent_color_text").value = accentColor;

            // Update color preview circles
            const primaryCircle = document.querySelector('#primary_color').parentElement.querySelector(
                '.color-preview-circle');
            const secondaryCircle = document.querySelector('#secondary_color').parentElement.querySelector(
                '.color-preview-circle');
            const accentCircle = document.querySelector('#accent_color').parentElement.querySelector(
                '.color-preview-circle');

            if (primaryCircle) primaryCircle.style.background = primaryColor;
            if (secondaryCircle) secondaryCircle.style.background = secondaryColor;
            if (accentCircle) accentCircle.style.background = accentColor;

            // Get status colors if they exist
            const successColor = document.getElementById("success-color")?.value || '#10B981';
            const infoColor = document.getElementById("info-color")?.value || '#3B82F6';
            const warningColor = document.getElementById("warning-color")?.value || '#F59E0B';
            const dangerColor = document.getElementById("danger-color")?.value || '#EF4444';

            // Update status color text inputs and circles
            const statusColors = [{
                    id: 'success-color',
                    color: successColor
                },
                {
                    id: 'info-color',
                    color: infoColor
                },
                {
                    id: 'warning-color',
                    color: warningColor
                },
                {
                    id: 'danger-color',
                    color: dangerColor
                }
            ];

            statusColors.forEach(({
                id,
                color
            }) => {
                const textInput = document.getElementById(id + "-text");
                const circle = document.querySelector(`#${id}`).parentElement.querySelector(
                    '.color-preview-circle');

                if (textInput) textInput.value = color;
                if (circle) circle.style.background = color;
            });

            // Apply comprehensive theme colors
            const root = document.documentElement;
            root.style.setProperty('--primary-green', primaryColor);
            root.style.setProperty('--light-green', secondaryColor);
            root.style.setProperty('--dark-green', secondaryColor);
            root.style.setProperty('--accent-green', accentColor);
            root.style.setProperty('--success-green', successColor);
            root.style.setProperty('--info-blue', infoColor);
            root.style.setProperty('--warning-orange', warningColor);
            root.style.setProperty('--danger-red', dangerColor);

            // Convert colors to RGB for rgba usage
            const primaryRgb = hexToRgb(primaryColor);
            const secondaryRgb = hexToRgb(secondaryColor);
            const accentRgb = hexToRgb(accentColor);

            if (primaryRgb) {
                root.style.setProperty('--primary-rgb', `${primaryRgb.r}, ${primaryRgb.g}, ${primaryRgb.b}`);
            }
            if (secondaryRgb) {
                root.style.setProperty('--secondary-rgb', `${secondaryRgb.r}, ${secondaryRgb.g}, ${secondaryRgb.b}`);
            }
            if (accentRgb) {
                root.style.setProperty('--accent-rgb', `${accentRgb.r}, ${accentRgb.g}, ${accentRgb.b}`);
            }

            // Apply colors immediately for preview
            applyThemeColors(primaryColor, secondaryColor, accentColor);

            // Update gradient previews
            updateGradientPreview('primary');
            updateGradientPreview('secondary');

            // Show preview badge
            showColorPreview(primaryColor, secondaryColor, accentColor);

            console.log('Comprehensive theme colors applied:', {
                primaryColor,
                secondaryColor,
                accentColor,
                successColor,
                infoColor,
                warningColor,
                dangerColor
            });
        }

        // Enhanced apply color preset function
        function applyColorPreset(primary, secondary, accent) {
            // Update primary colors
            document.getElementById('primary_color').value = primary;
            document.getElementById('secondary_color').value = secondary;
            document.getElementById('accent_color').value = accent;

            // Update text inputs
            document.getElementById('primary_color_text').value = primary;
            document.getElementById('secondary_color_text').value = secondary;
            document.getElementById('accent_color_text').value = accent;

            // Update color circles
            const primaryCircle = document.querySelector('#primary_color').parentElement.querySelector(
                '.color-preview-circle');
            const secondaryCircle = document.querySelector('#secondary_color').parentElement.querySelector(
                '.color-preview-circle');
            const accentCircle = document.querySelector('#accent_color').parentElement.querySelector(
                '.color-preview-circle');

            if (primaryCircle) primaryCircle.style.background = primary;
            if (secondaryCircle) secondaryCircle.style.background = secondary;
            if (accentCircle) accentCircle.style.background = accent;

            // Update gradients to match
            if (document.getElementById('primary-gradient-start')) {
                document.getElementById('primary-gradient-start').value = primary;
                document.getElementById('primary-gradient-end').value = secondary;
                updateGradientPreview('primary');
            }

            updateThemePreview();

            // Add visual feedback
            showNotification('Color preset applied successfully!', 'success');
        }

        // New functions for enhanced features
        function toggleLivePreview() {
            const toggleBtn = document.getElementById('preview-toggle-text');
            const isActive = toggleBtn.textContent === 'Stop Preview';

            if (isActive) {
                toggleBtn.textContent = 'Live Preview';
                hideColorPreview();
            } else {
                toggleBtn.textContent = 'Stop Preview';
                updateThemePreview();
            }
        }

        function exportTheme() {
            const themeData = {
                primary_color: document.getElementById('primary_color').value,
                secondary_color: document.getElementById('secondary_color').value,
                accent_color: document.getElementById('accent_color').value,
                success_color: document.getElementById('success-color').value,
                info_color: document.getElementById('info-color').value,
                warning_color: document.getElementById('warning-color').value,
                danger_color: document.getElementById('danger-color').value,
                primary_gradient_start: document.getElementById('primary-gradient-start').value,
                primary_gradient_end: document.getElementById('primary-gradient-end').value,
                secondary_gradient_start: document.getElementById('secondary-gradient-start').value,
                secondary_gradient_end: document.getElementById('secondary-gradient-end').value,
                exported_at: new Date().toISOString()
            };

            const dataStr = JSON.stringify(themeData, null, 2);
            const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);

            const exportFileDefaultName = 'school-theme-export.json';

            const linkElement = document.createElement('a');
            linkElement.setAttribute('href', dataUri);
            linkElement.setAttribute('download', exportFileDefaultName);
            linkElement.click();

            showNotification('Theme exported successfully!', 'success');
        }

        function isValidHexColor(hex) {
            return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hex);
        }

        function showColorPreview(primary, secondary, accent) {
            // Create or update preview badge
            let previewBadge = document.getElementById('color-preview-badge');
            if (!previewBadge) {
                previewBadge = document.createElement('div');
                previewBadge.id = 'color-preview-badge';
                previewBadge.style.cssText = `
              position: fixed;
              top: 80px;
              right: 20px;
              background: white;
              padding: 10px;
              border-radius: 8px;
              box-shadow: 0 4px 12px rgba(0,0,0,0.15);
              z-index: 1050;
              display: flex;
              align-items: center;
              gap: 8px;
              font-size: 12px;
              font-weight: 500;
              color: #374151;
            `;
                document.body.appendChild(previewBadge);
            }

            previewBadge.innerHTML = `
            <span>Preview:</span>
            <div style="width: 20px; height: 20px; background: ${primary}; border-radius: 4px; border: 1px solid #e5e7eb;"></div>
            <div style="width: 20px; height: 20px; background: ${secondary}; border-radius: 4px; border: 1px solid #e5e7eb;"></div>
            <div style="width: 20px; height: 20px; background: ${accent}; border-radius: 4px; border: 1px solid #e5e7eb;"></div>
            <button onclick="hideColorPreview()" style="background: none; border: none; color: #6B7280; cursor: pointer; padding: 2px;">×</button>
          `;

            previewBadge.style.display = 'flex';
        }

        function hideColorPreview() {
            const previewBadge = document.getElementById('color-preview-badge');
            if (previewBadge) {
                previewBadge.style.display = 'none';
            }
        }

        function hexToRgb(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : null;
        }

        function applyThemeColors(primary, secondary, accent) {
            const root = document.documentElement;

            // Convert hex to RGB for glassmorphism effects
            const primaryRgb = hexToRgb(primary);
            const secondaryRgb = hexToRgb(secondary);
            const accentRgb = hexToRgb(accent);

            if (primaryRgb && secondaryRgb && accentRgb) {
                // Update CSS custom properties
                root.style.setProperty('--primary-green', primary);
                root.style.setProperty('--light-green', secondary);
                root.style.setProperty('--dark-green', secondary);
                root.style.setProperty('--accent-green', accent);

                // Update Bootstrap variables
                root.style.setProperty('--bs-primary', primary);
                root.style.setProperty('--bs-secondary', secondary);
                root.style.setProperty('--bs-success', primary);

                // Update RGB values for transparency effects
                root.style.setProperty('--primary-rgb', `${primaryRgb.r}, ${primaryRgb.g}, ${primaryRgb.b}`);
                root.style.setProperty('--secondary-rgb', `${secondaryRgb.r}, ${secondaryRgb.g}, ${secondaryRgb.b}`);
                root.style.setProperty('--accent-rgb', `${accentRgb.r}, ${accentRgb.g}, ${accentRgb.b}`);

                // Apply to all themed elements
                const themedElements = [
                    '.btn-primary',
                    '.bg-gradient-primary',
                    '.bg-gradient-dark',
                    '.bg-gradient-secondary',
                    '.bg-primary',
                    '.text-primary',
                    '.border-primary',
                    '.navbar-brand',
                    '.nav-link.active',
                    '.btn-outline-primary',
                    '.stat-icon',
                    '.quick-action-btn',
                    '.card-primary .card-header',
                    '.progress-bar',
                    '.badge-primary',
                    '.icon-background',
                    '.avatar-primary',
                    '.notification-primary'
                ];

                themedElements.forEach(selector => {
                    const elements = document.querySelectorAll(selector);
                    elements.forEach(element => {
                        if (selector.includes('bg-gradient-primary') || selector.includes(
                                'bg-gradient-dark') || selector.includes('bg-gradient-secondary')) {
                            element.style.background = `linear-gradient(135deg, ${primary}, ${secondary})`;
                        } else if (selector.includes('btn-primary') || selector.includes(
                                'quick-action-btn') || selector.includes('stat-icon')) {
                            element.style.background = `linear-gradient(135deg, ${primary}, ${secondary})`;
                            element.style.borderColor = primary;
                            element.style.color = 'white';
                        } else if (selector.includes('bg-primary')) {
                            element.style.backgroundColor = primary;
                        } else if (selector.includes('text-primary')) {
                            element.style.color = primary;
                        } else if (selector.includes('border-primary')) {
                            element.style.borderColor = primary;
                        } else if (selector.includes('btn-outline-primary')) {
                            element.style.color = primary;
                            element.style.borderColor = primary;
                        } else if (selector.includes('nav-link.active') || selector.includes(
                                'card-primary')) {
                            element.style.background = `linear-gradient(135deg, ${primary}, ${secondary})`;
                            element.style.color = 'white';
                        }
                    });
                });

                // Special handling for Material Dashboard classes
                const materialElements = document.querySelectorAll(
                    '.bg-gradient-faded-primary, .bg-gradient-faded-success');
                materialElements.forEach(element => {
                    element.style.background = `linear-gradient(135deg, ${primary}cc, ${secondary}cc)`;
                });

                // Update progress bars
                const progressBars = document.querySelectorAll('.progress-bar');
                progressBars.forEach(bar => {
                    bar.style.background = `linear-gradient(135deg, ${primary}, ${secondary})`;
                });

                // Update form control focus colors
                const style = document.createElement('style');
                style.textContent = `
                    .form-control:focus {
                        border-color: ${primary} !important;
                        box-shadow: 0 0 0 0.2rem ${primary}40 !important;
                    }
                    .form-check-input:checked {
                        background-color: ${primary} !important;
                        border-color: ${primary} !important;
                    }
                `;
                document.head.appendChild(style);
            }
        }

        // Form submission handlers
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('school-info-form').addEventListener('submit', function(e) {
                e.preventDefault();
                submitForm('school-info', '{{ route('admin.setup.settings.school-info') }}');
            });

            document.getElementById('theme-form').addEventListener('submit', function(e) {
                e.preventDefault();
                submitForm('theme', '{{ route('admin.setup.settings.theme') }}');
            });

            document.getElementById('academic-form').addEventListener('submit', function(e) {
                e.preventDefault();
                submitForm('academic', '{{ route('admin.setup.settings.academic') }}');
            });

            // Language form handler
            document.getElementById('language-form').addEventListener('submit', function(e) {
                e.preventDefault();
                submitForm('language', '{{ route('admin.setup.settings.language') }}');
            });
        });

        function submitForm(type, url) {
            const formData = new FormData(document.getElementById(type + '-form'));

            fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Server error');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showNotification('Settings saved successfully!', 'success');
                        hideColorPreview(); // Hide preview after successful save

                        // If logo was uploaded, update sidebar logo
                        if (type === 'school-info' && data.logo_url) {
                            updateSidebarLogo(data.logo_url);
                        }
                    } else {
                        console.error('Validation errors:', data.errors);
                        let errorMessage = 'Error saving settings';
                        if (data.errors) {
                            const errorFields = Object.keys(data.errors);
                            errorMessage += ': ' + errorFields.join(', ') + ' validation failed';
                        }
                        showNotification(errorMessage, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error saving settings: ' + error.message, 'error');
                });
        }

        function applyColorPreset(primary, secondary, accent) {
            // Update primary colors
            document.getElementById('primary_color').value = primary;
            document.getElementById('secondary_color').value = secondary;
            document.getElementById('accent_color').value = accent;

            // Update text inputs
            document.getElementById('primary_color_text').value = primary;
            document.getElementById('secondary_color_text').value = secondary;
            document.getElementById('accent_color_text').value = accent;

            // Update color circles
            const primaryCircle = document.querySelector('#primary_color').parentElement.querySelector(
                '.color-preview-circle');
            const secondaryCircle = document.querySelector('#secondary_color').parentElement.querySelector(
                '.color-preview-circle');
            const accentCircle = document.querySelector('#accent_color').parentElement.querySelector(
                '.color-preview-circle');

            if (primaryCircle) primaryCircle.style.background = primary;
            if (secondaryCircle) secondaryCircle.style.background = secondary;
            if (accentCircle) accentCircle.style.background = accent;

            // Update gradients to match
            if (document.getElementById('primary-gradient-start')) {
                document.getElementById('primary-gradient-start').value = primary;
                document.getElementById('primary-gradient-end').value = secondary;
                updateGradientPreview('primary');
            }

            updateThemePreview();
        }

        function resetToDefault() {
            applyColorPreset('#06C167', '#10B981', '#F0FDF4');
            showNotification('Theme reset to default colors!', 'success');
        }

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1060;
                min-width: 300px;
            `;
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;

            document.body.appendChild(notification);

            // Auto remove after 3 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 3000);
        }

        function updateSidebarLogo(logoUrl) {
            // Update sidebar logo immediately without page refresh
            const sidebarLogo = document.querySelector('.sidebar-logo');
            if (sidebarLogo) {
                sidebarLogo.src = logoUrl;
            }

            // Also update the x-input preview if it exists
            const logoPreview = document.getElementById('logo-preview');
            if (logoPreview && logoPreview.tagName === 'IMG') {
                logoPreview.src = logoUrl;
            }
        }

        // Language preview functionality
        const translations = {
            'en': {
                'language': 'Language',
                'dashboard': 'Dashboard',
                'settings': 'Settings',
                'save': 'Save'
            },
            'si': {
                'language': 'භාෂාව',
                'dashboard': 'පාලක පුවරුව',
                'settings': 'සැකසීම්',
                'save': 'සුරකින්න'
            }
        };

        function previewLanguageChange(lang) {
            if (lang && translations[lang]) {
                const previewTitle = document.getElementById('preview-title');
                const previewDashboard = document.getElementById('preview-dashboard');
                const previewSettings = document.getElementById('preview-settings');
                const previewSave = document.getElementById('preview-save');

                if (previewTitle) previewTitle.textContent = translations[lang]['language'];
                if (previewDashboard) previewDashboard.textContent = translations[lang]['dashboard'];
                if (previewSettings) previewSettings.textContent = translations[lang]['settings'];
                if (previewSave) previewSave.textContent = translations[lang]['save'];
            }
        }
    </script>
@endsection
