<?php

namespace App\Http\Controllers\V1\User\Authentication;

use App\Facades\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\User\Authentication\LoginRequest;
use App\Http\Requests\V1\User\Authentication\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController extends Controller
{
    /**
     * Handle Microsoft Login: Expects an ID token (JWT) from the front end.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function loginByMicrosoft(Request $request)
    {
        $idToken = $request->input('id_token');

        if (!$idToken) {
            return ApiResponse::error('Missing id_token', Response::HTTP_BAD_REQUEST);
        }

        // 1) Decode the JWT (in a real app, verify the signature).
        $parts = explode('.', $idToken);
        if (count($parts) < 2) {
            return ApiResponse::error('Invalid id_token format', Response::HTTP_BAD_REQUEST);
        }
        $claims = json_decode(base64_decode($parts[1]), true);

        if (!is_array($claims)) {
            return ApiResponse::error('Could not decode ID token payload', Response::HTTP_BAD_REQUEST);
        }

        // 2) Extract the user's email from the claims. Typically "preferred_username" or "email"
        $email = $claims['preferred_username'] ?? $claims['email'] ?? null;
        if (!$email) {
            return ApiResponse::error('No email found in ID token', Response::HTTP_BAD_REQUEST);
        }

        // 3) Optionally parse roles if present, e.g. $claims['roles'] or something similar
        $roles = $claims['roles'] ?? [];

        // 4) Look up or create a user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'email' => $email,
                'email_verified_at' => now(),
            ]);
            // If you want to store roles, do it here
            // $user->assignRoles($roles); // or some custom logic
        }

        // 5) Check if banned
        if ($user->isBanned()) {
            return ApiResponse::error(trans('messages.user_is_baned'), Response::HTTP_BAD_REQUEST);
        }

        // 6) Return a local token (Laravel Sanctum or Passport, etc.)
        $token = $user->createAuthToken()->plainTextToken;

        return ApiResponse::addData('token', $token)
            ->success(trans('messages.success'));
    }

    /**
     * Standard email/password login.
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->checkPassword($request->password)) {
            return ApiResponse::error(trans('messages.email_or_password_is_incorrect'), Response::HTTP_BAD_REQUEST);
        }

        if ($user->isBanned()) {
            return ApiResponse::error(trans('messages.user_is_baned'), Response::HTTP_BAD_REQUEST);
        }

        return ApiResponse::addData('token', $user->createAuthToken()->plainTextToken)
            ->success(trans('messages.success'));
    }

    /**
     * Standard registration.
     */
    public function register(RegisterRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            return ApiResponse::error(trans('messages.email_is_already_registered'), Response::HTTP_BAD_REQUEST);
        }

        // If needed, check isBanned or other logic
        // if ($user->isBanned()) { ... }

        //TODO: Add email verification
        $createdUser = User::create([
            'email' => $request->email,
            'password' => $request->password,
        ]);

        return ApiResponse::addData('token', $createdUser->createAuthToken()->plainTextToken)
            ->success(trans('messages.success'));
    }
}
