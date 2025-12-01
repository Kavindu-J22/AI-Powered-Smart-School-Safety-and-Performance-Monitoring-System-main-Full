<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login (default fallback).
     *
     * @var string
     */
    protected $redirectTo = '/admin/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Get the post-login redirect path based on user type.
     *
     * @return string
     */
    protected function redirectTo(): string
    {
        $user = auth()->user();

        if (!$user) {
            return '/admin/dashboard';
        }

        // Redirect based on user type (usertype is cast to UserType enum)
        return match ($user->usertype) {
            UserType::STUDENT => '/student/dashboard',
            UserType::TEACHER => '/teacher/dashboard',
            UserType::PARENT => '/parent/dashboard',
            UserType::SECURITY => '/security/dashboard',
            UserType::ADMIN, UserType::USER => '/admin/dashboard',
            default => '/admin/dashboard',
        };
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Redirect based on user type after successful authentication
        return redirect($this->redirectTo());
    }
}
