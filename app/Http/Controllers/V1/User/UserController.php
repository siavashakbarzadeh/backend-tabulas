<?php

namespace App\Http\Controllers\V1\User;

use App\Facades\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\SingleUserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function user(Request $request)
    {
        return ApiResponse::addData(
            'user',
            new SingleUserResource($request->user())
        )->success(trans('messages.success'));
    }

    public function search(Request $request)
    {
        $query = $request->get('query', '');

        // Query the User model, not the Resource
        $users = User::where('email', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        // Wrap the collection in your Resource class
        return ApiResponse::addData(
            'users',
            SingleUserResource::collection($users)
        )->success(trans('messages.success'));
    }
}
