<?php

namespace App\Http\Controllers\V1\Application;

use App\Facades\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Request\ApplicationRequest;
use App\Http\Resources\V1\Application\ApplicationResource;
use App\Jobs\ProcessApplicationJob;
use App\Models\Application;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ApplicationController extends Controller
{
    /**
     * Store a new application in the database.
     */
    public function store(ApplicationRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                // Parse the submission date
                $submissionDate = Carbon::parse($request->submission_date);

                // If 'name' is the email of the user, find that user's ID
                $user = User::where('email', $request->name)->first();
                if (!$user) {
                    throw new \Exception("User with email {$request->name} not found.");
                }

                // Prepare data for insertion
                $applicationData = [
                    'user_id'          => $user->id,
                    'name'             => $request->name, // "Nome atto" from the form
                    'act_type'         => $request->act_type,
                    'recipient_office' => $request->recipient_office,
                    'submission_date'  => $request->submission_date,
                    'status'           => 'pending',
                ];

                // Create the application record
                $application = Application::query()->create($applicationData);

                // Attach firmatarios if provided (expects an array of user IDs)
                // e.g. "firmatario" => [2, 5, 10]
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
                $application->refresh()->load(['document', 'firmatarios']);

                return ApiResponse::addData('application', new ApplicationResource($application))
                    ->success(trans('messages.success'));
            });
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    /**
     * Show a single application (including relationships).
     */
    public function show(Request $request, Application $application): JsonResponse
    {
        // Eager load any relationships you need (document, firmatarios, etc.)
        $application->load(['document', 'firmatarios']);

        // Return the data via your resource
        return ApiResponse::addData('application', new ApplicationResource($application))
            ->success(trans('messages.success'));
    }

    /**
     * Decline the application (e.g. set status to "declined" or delete it).
     */
    public function decline(Request $request, Application $application): JsonResponse
    {
        // Example: just update the status to 'declined'
        $application->update(['status' => 'declined']);

        return ApiResponse::addData('application', new ApplicationResource($application))
            ->success('Application has been declined.');
    }
    public function confirm(Request $request, Application $application): JsonResponse
    {
        // Example: just update the status to 'declined'
        $application->update(['status' => 'confirmed']);

        return ApiResponse::addData('application', new ApplicationResource($application))
            ->success('Application has been confirmed.');
    }
}
