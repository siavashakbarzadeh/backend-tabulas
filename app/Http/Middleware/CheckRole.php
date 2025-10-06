<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CheckRole
{
    /**
     * بررسی نقش کاربر بر اساس JWT که از Microsoft دریافت شده.
     */
    public function handle(Request $request, Closure $next, string $requiredRole): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $jwt = substr($authHeader, 7);

        try {
            // 1️⃣ ابتدا سعی کن JWKها را از cache بگیری
            $keys = Cache::remember('azure_jwks_keys', now()->addHours(24), function () {
                $jwksUri = "https://login.microsoftonline.com/common/discovery/v2.0/keys";
                $jwks = Http::get($jwksUri)->json();
                return JWK::parseKeySet($jwks);
            });

            // 2️⃣ Decode + Verify JWT با کلیدهای cached
            $decoded = (array) JWT::decode($jwt, $keys);

            // ذخیره‌ی payload برای دسترسی بعدی در controller
            $request->merge(['jwt_payload' => $decoded]);

            // 3️⃣ بررسی نقش
            $roles = $decoded['roles'] ?? [];

            if (!in_array($requiredRole, $roles)) {
                return response()->json(['error' => 'Forbidden: missing role ' . $requiredRole], 403);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid token',
                'message' => $e->getMessage(),
            ], 401);
        }

        return $next($request);
    }
}
