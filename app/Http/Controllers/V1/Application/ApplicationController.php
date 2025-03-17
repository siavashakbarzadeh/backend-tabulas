<?php

namespace App\Http\Controllers\V1\Application;

use App\Facades\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Request\ApplicationRequest;
use App\Http\Resources\V1\Application\ApplicationResource;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class ApplicationController extends Controller
{
    public function show(Request $request, $application)
    {
        $application = Application::with([
            'document',
            'sign',
        ])->findOrFail($application);
        Gate::authorize('show', $application);
        return ApiResponse::addData('application', new ApplicationResource($application))
            ->success(trans('messages.success'));
    }

    /**
     * @param ApplicationRequest $request
     * @return JsonResponse
     */
    public function store(ApplicationRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $application = Application::query()->create([
                    'user_id' => $request->user()->id,
                    'name' => $request->name,
                    'act_type' => $request->act_type,
                    'recipient_office' => $request->recipient_office,
                    'submission_date' => $request->submission_date,
                ]);
                $application->privateDisk()
                    ->setCollection(Application::MEDIA_DOCUMENT_COLLECTION)
                    ->setDirectory('applications/documents')
                    ->uploadMedia($request->file('document'));
                $application->privateDisk()
                    ->setCollection(Application::MEDIA_SIGN_COLLECTION)
                    ->setDirectory('applications/signs')
                    ->uploadMedia($request->file('sign'));
                $new_application = Application::query()
                    ->with([
                        'document',
                        'sign',
                    ])
                    ->findOrFail($application->id);
                return ApiResponse::addData('application', new ApplicationResource($new_application))
                    ->success(trans('messages.success'));
            });
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage());
        }
    }
}
