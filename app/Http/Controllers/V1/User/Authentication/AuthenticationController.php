<?php

namespace App\Http\Controllers\V1\User\Authentication;

use App\Facades\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\User\Authentication\LoginRequest;
use App\Http\Requests\V1\User\Authentication\RegisterRequest;
use App\Models\User;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController extends Controller
{
    /**
     * Microsoft OAuth2 Login
     *
     * فرانت‌اند یک id_token (JWT) از Azure AD می‌فرستد.
     * اینجا:
     *   1) امضا را با JWKهای Azure اعتبارسنجی می‌کنیم
     *   2) claimها را استخراج می‌کنیم (ایمیل/نام/نقش‌ها)
     *   3) کاربر را پیدا یا ایجاد می‌کنیم
     *   4) توکن محلی (Sanctum/Passport) برمی‌گردانیم
     */
    public function loginByMicrosoft(Request $request): JsonResponse
    {
        $idToken = $request->input('id_token');

        if (!$idToken) {
            return ApiResponse::error('Missing id_token', Response::HTTP_BAD_REQUEST);
        }

        try {
            // --- 1) دریافت کلیدهای عمومی Azure AD (JWKs)
            $jwksUri = "https://login.microsoftonline.com/common/discovery/v2.0/keys";
            $jwks = Http::get($jwksUri)->json();
            $keys = JWK::parseKeySet($jwks);

            // --- 2) Decode + verify JWT
            $decoded = (array) JWT::decode($idToken, $keys);

        } catch (\Exception $e) {
            return ApiResponse::error("Invalid ID Token: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        // --- 3) استخراج اطلاعات مهم از claimها
        $email = $decoded['preferred_username'] ?? $decoded['email'] ?? null;
        $name  = $decoded['name'] ?? null;
        $oid   = $decoded['oid'] ?? null;   // object id (unique per tenant)
        $roles = $decoded['roles'] ?? [];

        if (!$email) {
            return ApiResponse::error('No email found in ID token', Response::HTTP_BAD_REQUEST);
        }

        // --- 4) پیدا یا ایجاد کاربر
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'email' => $email,
                'name' => $name,
                'microsoft_oid' => $oid,
                'email_verified_at' => now(),
            ]);
            // اگر خواستی نقش‌ها رو ذخیره کنی:
            // $user->syncRoles($roles);
        }

        // --- 5) چک کاربر مسدود
        if ($user->isBanned()) {
            return ApiResponse::error(trans('messages.user_is_baned'), Response::HTTP_FORBIDDEN);
        }

        // --- 6) ساخت توکن محلی و ریترن
        $token = $user->createAuthToken()->plainTextToken;

        return ApiResponse::addData('token', $token)
            ->addData('roles', $roles)
            ->addData('email', $email)
            ->success(trans('messages.success'));
    }

    /**
     * Standard email/password login.
     *
     * ورود کاربر با ایمیل/پسورد
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->checkPassword($request->password)) {
            return ApiResponse::error(trans('messages.email_or_password_is_incorrect'), Response::HTTP_BAD_REQUEST);
        }

        if ($user->isBanned()) {
            return ApiResponse::error(trans('messages.user_is_baned'), Response::HTTP_FORBIDDEN);
        }

        return ApiResponse::addData('token', $user->createAuthToken()->plainTextToken)
            ->success(trans('messages.success'));
    }

    /**
     * Standard registration.
     *
     * ثبت‌نام کاربر جدید با ایمیل و پسورد
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            return ApiResponse::error(trans('messages.email_is_already_registered'), Response::HTTP_BAD_REQUEST);
        }

        $createdUser = User::create([
            'email' => $request->email,
            'password' => $request->password,
        ]);

        return ApiResponse::addData('token', $createdUser->createAuthToken()->plainTextToken)
            ->success(trans('messages.success'));
    }
}
