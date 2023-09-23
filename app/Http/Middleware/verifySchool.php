<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class verifySchool
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return string
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            if (Auth::user()->role !== 'super_admin') {
                $school = School::findOrFail(Auth::user()->school_id);
                if ($school->active) {
                    return $next($request);
                } else {
                    Auth::logout();
                    return redirect()->route('login')->with('error', 'You don`t have access to use this system');
                }
            }
            return $next($request);
        }
    }
}
