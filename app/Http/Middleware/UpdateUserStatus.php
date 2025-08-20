<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is authenticated, ensure their status is set to active
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->status !== 'active') {
                DB::table('users')->where('id', $user->id)->update(['status' => 'active']);
            }
        }

        $response = $next($request);

        // If the response indicates the user is no longer authenticated (session expired)
        // we don't need to do anything here as the logout method will handle setting status to inactive

        return $response;
    }
}
