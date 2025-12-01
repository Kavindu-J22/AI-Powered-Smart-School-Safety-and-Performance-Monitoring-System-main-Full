{{-- Beautiful Notification System --}}
@vite('resources/css/components/notifications.css')

{{-- Material Symbols Font --}}
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

<div class="notification-container" id="notificationContainer">
    {{-- Notifications will be dynamically added here --}}
</div>

@vite('resources/js/components/notifications.js')

<script>
    // Laravel Flash Message Integration
    document.addEventListener('DOMContentLoaded', function() {
        @if (session('success'))
            notificationManager.success('Success', '{{ session('success') }}');
        @endif

        @if (session('error'))
            notificationManager.error('Error', '{{ session('error') }}');
        @endif

        @if (session('warning'))
            notificationManager.warning('Warning', '{{ session('warning') }}');
        @endif

        @if (session('info'))
            notificationManager.info('Information', '{{ session('info') }}');
        @endif

        @if ($errors->any())
            notificationManager.error('Validation Error', '{{ $errors->first() }}');
        @endif
    });
</script>
