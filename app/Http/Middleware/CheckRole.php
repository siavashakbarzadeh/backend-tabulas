<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
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
            // دریافت JWKs از Azure برای اعتبارسنجی توکن
            $jwksUri = "https://login.microsoftonline.com/common/discovery/v2.0/keys";
            $jwks = Http::get($jwksUri)->json();
            $keys = JWK::parseKeySet($jwks);

            // Decode + Verify
            $decoded = (array) JWT::decode($jwt, $keys);

            // ذخیره payload داخل request برای دسترسی در Controller
            $request->merge(['jwt_payload' => $decoded]);

            // گرفتن نقش‌ها
            $roles = $decoded['roles'] ?? [];

            if (!in_array($requiredRole, $roles)) {
                return response()->json(['error' => 'Forbidden: missing role ' . $requiredRole], 403);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid token',
                'message' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}
