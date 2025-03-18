<?php
namespace App\Jobs;

use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessApplicationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $applicationId;

    public function __construct(int $applicationId)
    {
        $this->applicationId = $applicationId;
    }

    public function handle()
    {
        // e.g. finalize or set status to 'finalized'
        $application = Application::find($this->applicationId);
        if ($application && $application->status === 'pending') {
            $application->update(['status' => 'finalized']);
            // do other logic as needed
        }
    }
}
