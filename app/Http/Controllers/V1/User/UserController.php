<?php

namespace App\Http\Controllers\V1\User;

use App\Facades\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\SingleUserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function user(Request $request){
        return ApiResponse::addData('user', new SingleUserResource($request->user()))
            ->success(trans('messages.success'));
    }
}
