<?php

use Illuminate\Support\Facades\Session;

function flashResponse(string $message, string $color = 'success'): void
{
    // Map old color names to new notification types
    $typeMap = [
        'primary' => 'info',
        'green' => 'success',
        'red' => 'error',
        'yellow' => 'warning',
        'blue' => 'info',
        'danger' => 'error',
        'warning' => 'warning',
        'success' => 'success',
        'info' => 'info',
    ];

    $type = $typeMap[$color] ?? 'success';

    Session::flash($type, $message);
}

function getDataTableAction(string $title, string $route, string $color = 'secondary'): string
{
    return '<a href="'.$route.'" class="btn btn-sm text-'.$color.' font-weight-bold text-xs" data-toggle="tooltip" data-original-title="'.$title.'">'.$title.'</a>';
}

function uploadImage(string $driver, $image): string
{
    return $image->store($driver, 'public');
}

function confirmActionButton(string $title, string $route, string $id, string $color = 'danger', string $icon = 'fa-trash'): string
{
    return '<a href="#" class="btn btn-sm btn-'.$color.' confirm-action"
            data-id="'.$id.'"
            data-route="'.$route.'"
            data-title="'.$title.'"
            data-toggle="tooltip"
            data-original-title="'.$title.'">
            <i class="fa '.$icon.'"></i>
        </a>';
}

function checkPermission(string $permission): bool
{
    if (! \Illuminate\Support\Facades\Auth::check()) {
        return false;
    }

    // Convert dots to spaces to match the permission format in database
    $permission = str_replace('.', ' ', $permission);

    $user = \Illuminate\Support\Facades\Auth::user();

    return $user instanceof \App\Models\User && $user->can($permission);
}

function checkPermissionAndRedirect(string $permission): void
{
    if (! checkPermission($permission)) {
        flashResponse('Unauthorized action. You do not have permission to access this resource.', 'danger');
        abort(403, 'Unauthorized action.');
    }
}

function hexToRgb(string $hex): string
{
    // Remove # if present
    $hex = ltrim($hex, '#');

    // Convert hex to RGB
    if (strlen($hex) == 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }

    if (strlen($hex) != 6) {
        return '6, 193, 103'; // Default green RGB
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    return "$r, $g, $b";
}
