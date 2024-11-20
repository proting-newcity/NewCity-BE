<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyuUsername;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureuUsernameIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() ||
            ($request->user() instanceof MustVerifyuUsername &&
            ! $request->user()->hasVerifieduUsername())) {
            return response()->json(['message' => 'Your username address is not verified.'], 409);
        }

        return $next($request);
    }
}
