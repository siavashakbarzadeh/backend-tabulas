<?php

namespace App\Http\Controllers\V1\User\Authentication;

use App\Facades\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\User\Authentication\MicrosoftLoginRequest;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController extends Controller
{
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

    public function login(MicrosoftLoginRequest $request)
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
}
