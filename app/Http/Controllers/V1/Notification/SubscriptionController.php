<?php

namespace App\Http\Controllers\V1\Notification;

use App\Facades\Api\ApiResponse;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function saveSubscription(Request $request)
{
    $subscriptionData = $request->all();

    // Optionally validate the subscription data here

    // Save the subscription in the database (assuming a Subscription model exists)
    Subscription::create([
        'endpoint' => $subscriptionData['endpoint'],
        'p256dh'   => $subscriptionData['keys']['p256dh'],
        'auth'     => $subscriptionData['keys']['auth'],
        // Optionally, associate this with a user if authentication is implemented
    ]);

    return response()->json(['success' => true]);
}
}
