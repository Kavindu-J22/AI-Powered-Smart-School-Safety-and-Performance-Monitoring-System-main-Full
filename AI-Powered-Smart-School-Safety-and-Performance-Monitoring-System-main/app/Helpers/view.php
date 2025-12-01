<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

function getBreadcrumbs()
{
    return explode('.', Route::currentRouteName());
}

function pageTitle(): string
{
    $breadcrumbs = getBreadcrumbs();

    return Session::get('title') ?? (is_array($breadcrumbs) && isset($breadcrumbs[count($breadcrumbs) - 2]) ? ucfirst($breadcrumbs[count($breadcrumbs) - 2]) : '');
}
function pageTitleForHead(): string
{
    $breadcrumbs = getBreadcrumbs();
    $title = Session::get('title') ?? (is_array($breadcrumbs) && isset($breadcrumbs[count($breadcrumbs) - 2]) ? ucfirst($breadcrumbs[count($breadcrumbs) - 2]) : '');

    return $title ? ' - '.$title : '';
}
