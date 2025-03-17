<?php

namespace App\Policies\Application;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function show(User $user, Application $application)
    {
        dd($user,$application);
    }
}
