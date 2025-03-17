<?php

namespace App\Policies\Application;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * @param User $user
     * @param Application $application
     * @return bool
     */
    public function show(User $user, Application $application): bool
    {
        return $user->id == $application->user_id;
    }
}
