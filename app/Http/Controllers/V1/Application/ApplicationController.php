<?php

namespace App\Http\Controllers\V1\Application;

use App\Facades\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Request\ApplicationRequest;
use App\Http\Resources\V1\Application\ApplicationResource;
use App\Jobs\ProcessApplicationJob; // Your job to process applications
use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Throwable;

class ApplicationController extends Controller
{
    /**
     * Store a newly created Application (Option B).
     */
    public function store(ApplicationRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                // Parse the submission date
                $submissionDate = Carbon::parse($request->submission_date);
                
                $userID = User::where('email',$request->name)->first()->id;
                // Prepare data for insertion
                $applicationData = [
                    'user_id'          => $userID,
                    'name'             => $request->name, // "Nome atto" comes from the logged-in user
                    'act_type'         => $request->act_type,
                    'recipient_office' => $request->recipient_office,
                    'submission_date'  => $request->submission_date,
                    'status'           => $submissionDate->isToday() ? 'finalized' : 'pending',
                ];


                // Create the application record
                $application = Application::query()->create($applicationData);

                // Attach firmatarios if provided (expects an array of user IDs)
                if ($request->has('firmatario')) {
                    $application->firmatarios()->sync($request->firmatario);
                }

                // If submission_date is not today, schedule a job to process/finalize the application later.
                if (!$submissionDate->isToday()) {
                    ProcessApplicationJob::dispatch($application->id)
                        ->delay($submissionDate->startOfDay());
                }

                // Upload the document (sign upload removed as requested)
                $application->privateDisk()
                    ->setCollection(Application::MEDIA_DOCUMENT_COLLECTION)
                    ->setDirectory('applications/documents')
                    ->uploadMedia($request->file('document'));

                // Refresh and load relationships (including firmatarios)
                $application = $application->refresh()->load(['document', 'firmatarios']);

                return ApiResponse::addData('application', new ApplicationResource($application))
                    ->success(trans('messages.success'));
            });
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    /**
     * (Optional) Show method as before, or any other methods.
     */
    public function show(Request $request, $application)
    {
        $application = Application::with(['document', 'firmatarios'])->findOrFail($application);
        Gate::authorize('show', $application);

        return ApiResponse::addData('application', new ApplicationResource($application))
            ->success(trans('messages.success'));
    }
}

