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
     * استفاده:
     *   فقط دامنه: ->middleware('checkrole')
     *   دامنه + نقش لازم: ->middleware('checkrole:Admin')
     * دامنه از .env خوانده می‌شود: ALLOWED_MS_DOMAIN=senato.it
     */
    public function handle(Request $request, Closure $next, ?string $requiredRole = null): Response
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $jwt = substr($authHeader, 7);

        try {
            // تنظیمات از env
            $tenantId = config('services.azure.tenant_id', env('AZURE_TENANT_ID'));
            $clientId = config('services.azure.client_id', env('AZURE_CLIENT_ID')); // aud
            $allowedDomain = env('ALLOWED_MS_DOMAIN', 'senato.it');

            if (!$tenantId || !$clientId) {
                return response()->json(['error' => 'Server misconfigured: missing AZURE_TENANT_ID / AZURE_CLIENT_ID'], 500);
            }

            // کش کردن کلیدهای JWK مخصوص همان tenant
            $keys = Cache::remember("azure_jwks_keys_{$tenantId}", now()->addHours(24), function () use ($tenantId) {
                $jwksUri = "https://login.microsoftonline.com/{$tenantId}/discovery/v2.0/keys";
                $jwks = Http::timeout(10)->get($jwksUri)->json();
                return JWK::parseKeySet($jwks);
            });

            // جبران اختلاف ساعت
            JWT::$leeway = 60;

            // دیکد و امضا
            $decoded = (array) JWT::decode($jwt, $keys);

            // اعتبارسنجی پایه iss/aud
            $issExpected = "https://login.microsoftonline.com/{$tenantId}/v2.0";
            if (($decoded['iss'] ?? null) !== $issExpected) {
                return response()->json(['error' => 'Invalid issuer'], 401);
            }
            if (($decoded['aud'] ?? null) !== $clientId) {
                return response()->json(['error' => 'Invalid audience'], 401);
            }

            // استخراج ایمیل از کلِیم‌های رایج AAD
            $email = $decoded['email']
                ?? $decoded['preferred_username']
                ?? $decoded['upn']
                ?? $decoded['unique_name']
                ?? null;

            if (!$email) {
                return response()->json(['error' => 'Email not found in token'], 403);
            }

            // محدودیت دامنه: فقط @senato.it (یا هرچی در env گذاشتی)
            if (!preg_match('/@' . preg_quote($allowedDomain, '/') . '$/i', $email)) {
                return response()->json(['error' => "Forbidden: email must end with @{$allowedDomain}"], 403);
            }

            // بررسی نقش اختیاری (App Roles در Azure AD -> claim: roles)
            if ($requiredRole) {
                $roles = $decoded['roles'] ?? [];
                if (!in_array($requiredRole, $roles, true)) {
                    return response()->json(['error' => "Forbidden: missing role {$requiredRole}"], 403);
                }
            }

            // در اختیار controller بگذار
            $request->merge([
                'jwt_payload' => $decoded,
                'jwt_email'   => $email,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Invalid token',
                'message' => app()->hasDebugModeEnabled() ? $e->getMessage() : 'Signature/claims check failed',
            ], 401);
        }

        return $next($request);
    }
}
