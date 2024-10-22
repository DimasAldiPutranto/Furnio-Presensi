<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckApiDocsAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check){
            abort(403, 'Unauthorized');
        }

        if (Auth::user()->hasRole('super_admin')) {
            return $next($request);
        }

        abort(403, 'Sorry, You dont have permission to accsess pages');
    }
}
