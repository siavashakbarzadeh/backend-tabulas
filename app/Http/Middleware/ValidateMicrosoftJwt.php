<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValidateMicrosoftJwt
{
    /**
     * Handle an incoming request.
     * Validates the Microsoft JWT token and allows the request to proceed.
     * The token is also stored in the request for forwarding to downstream APIs.
     */
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'error' => 'Unauthorized. Please provide a valid Bearer token.',
            ], 401);
        }

        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix

        try {
            // Fetch Azure AD public keys (JWKs)
            $jwksUri = "https://login.microsoftonline.com/common/discovery/v2.0/keys";
            $jwks = Http::get($jwksUri)->json();
            $keys = JWK::parseKeySet($jwks);

            // Decode and verify the JWT
            $decoded = (array) JWT::decode($token, $keys);

            // Store decoded claims in request for later use
            $request->attributes->set('jwt_claims', $decoded);
            $request->attributes->set('jwt_token', $token);

        } catch (\Exception $e) {
            Log::warning('Microsoft JWT validation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Invalid or expired token: ' . $e->getMessage(),
            ], 401);
        }

        return $next($request);
    }
}
