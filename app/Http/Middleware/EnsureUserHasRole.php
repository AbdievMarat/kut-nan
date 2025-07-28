<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param $roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles): mixed
    {
        if (auth()->check() && !auth()->user()) {
            auth()->logout();

            return redirect('/login');
        }

        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        return redirect('/')->with('error', ['text' => "You don't have access to this page."]);
    }
}
