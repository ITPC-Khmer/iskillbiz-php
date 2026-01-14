<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyFacebookToken
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): Response $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verify that the authenticated user has a Facebook connection
        if ($request->user() && !$request->user()->isConnectedToFacebook()) {
            return response()->json([
                'success' => false,
                'message' => 'User is not connected to Facebook',
            ], 403);
        }

        return $next($request);
    }
}
