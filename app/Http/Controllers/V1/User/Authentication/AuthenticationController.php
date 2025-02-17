<?php

namespace App\Http\Controllers\V1\User\Authentication;

use App\Facades\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\User\Authentication\LoginRequest;
use App\Http\Requests\V1\User\Authentication\MicrosoftLoginRequest;
use App\Http\Requests\V1\User\Authentication\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController extends Controller
{
    /**
     * @param MicrosoftLoginRequest $request
     * @return JsonResponse
     */
    public function loginByMicrosoft(MicrosoftLoginRequest $request)
    {
        $user = User::query()->where('email', $request->email)->first();
        if (is_null($user)) {
            $createdUser = User::query()->create([
                'email' => $request->email,
                'email_verified_at' => now(),
            ]);
            return ApiResponse::addData('token', $createdUser->createAuthToken()->plainTextToken)
                ->success(trans('messages.success'));
        }

        if ($user->isBanned()) {
            return ApiResponse::error(trans('messages.user_is_baned'), Response::HTTP_BAD_REQUEST);
        }

        return ApiResponse::addData('token', $user->createAuthToken()->plainTextToken)
            ->success(trans('messages.success'));
    }

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $user = User::query()->where('email', $request->email)->first();

        if (is_null($user) || !$user->checkPassword($request->password)) {
            return ApiResponse::error(trans('messages.email_or_password_is_incorrect'), Response::HTTP_BAD_REQUEST);
        }

        if ($user->isBanned()) {
            return ApiResponse::error(trans('messages.user_is_baned'), Response::HTTP_BAD_REQUEST);
        }

        return ApiResponse::addData('token', $user->createAuthToken()->plainTextToken)
            ->success(trans('messages.success'));
    }

    /**
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $user = User::query()->where('email', $request->email)->first();

        if ($user) {
            return ApiResponse::error(trans('messages.email_is_already_registered'), Response::HTTP_BAD_REQUEST);
        }

        if ($user->isBanned()) {
            return ApiResponse::error(trans('messages.user_is_baned'), Response::HTTP_BAD_REQUEST);
        }

        //TODO: Add email verification
        $createdUser = User::query()->create([
            'email' => $request->email,
            'password' => $request->password,
        ]);
        return ApiResponse::addData('token', $createdUser->createAuthToken()->plainTextToken)
            ->success(trans('messages.success'));
    }
}
