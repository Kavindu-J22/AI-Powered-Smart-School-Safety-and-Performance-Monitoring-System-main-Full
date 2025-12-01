<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$userTypes  Allowed user types (as integer values)
     */
    public function handle(Request $request, Closure $next, ...$userTypes): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $allowedTypes = array_map('intval', $userTypes);

        // Get the user's type value (usertype is cast to UserType enum)
        $userTypeValue = $user->usertype instanceof UserType ? $user->usertype->value : $user->usertype;

        // Check if user's type is in the allowed types
        if (!in_array($userTypeValue, $allowedTypes)) {
            // Redirect to appropriate dashboard based on user type
            return $this->redirectToUserDashboard($user->usertype);
        }

        return $next($request);
    }

    /**
     * Redirect user to their appropriate dashboard
     */
    protected function redirectToUserDashboard(UserType|int $userType): Response
    {
        // Handle both enum and integer types
        $route = match (true) {
            $userType === UserType::STUDENT || $userType === UserType::STUDENT->value => '/student/dashboard',
            $userType === UserType::TEACHER || $userType === UserType::TEACHER->value => '/teacher/dashboard',
            $userType === UserType::PARENT || $userType === UserType::PARENT->value => '/parent/dashboard',
            $userType === UserType::SECURITY || $userType === UserType::SECURITY->value => '/security/dashboard',
            $userType === UserType::ADMIN || $userType === UserType::ADMIN->value => '/admin/dashboard',
            $userType === UserType::USER || $userType === UserType::USER->value => '/admin/dashboard',
            default => '/admin/dashboard',
        };

        return redirect($route)->with('error', 'You do not have permission to access that page.');
    }
}
