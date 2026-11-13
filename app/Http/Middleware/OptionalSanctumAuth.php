<?php
// app/Http/Middleware/OptionalSanctumAuth.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class OptionalSanctumAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if Authorization header exists
        if ($token = $request->bearerToken()) {
            try {
                // Use Sanctum's token resolution
                $accessToken = PersonalAccessToken::findToken($token);
                
                if ($accessToken && $accessToken->tokenable) {
                    // Set the user in the current request context
                    $request->setUserResolver(function () use ($accessToken) {
                        return $accessToken->tokenable;
                    });
                    
                    // Also set in Auth facade for consistency
                    Auth::setUser($accessToken->tokenable);
                    
                    // Store token instance for ability checking
                    $request->attributes->set('sanctum_token', $accessToken);
                }
            } catch (\Exception $e) {
                // Log error if needed, but continue without authentication
                \Log::debug('Optional Sanctum Auth failed: ' . $e->getMessage());
            }
        }
        
        return $next($request);
    }
}