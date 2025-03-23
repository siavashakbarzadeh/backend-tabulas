<?php

namespace App\Http\Controllers\V1\Notification;

use App\Facades\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription as WebPushSubscription;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{

    /**
     * Send a push notification to all subscribed clients.
     *
     * @param Request $request
     * @return void
     */
    public function sendPushNotification(Request $request)
    {
        // Retrieve message data from the request or use default values
        $messageData = $request->input('messageData', [
            'title' => 'Hello!',
            'body'  => 'You have a new notification.',
            'icon'  => '/icon.png',
            'url'   => '/',
        ]);

        // VAPID authentication configuration
        $auth = [
            'VAPID' => [
                'subject' => 'mailto: <a.allahverdi@m.icoa.it>',
                'publicKey' => 'BDHaWPVr-4KGYKxoavcU_w2TUq5XqCDQHQQdJj4nhBBp2dqTExCrr8f2vUCr5Enp-dGkCD4Omohgk8qRjHtszBs',
                'privateKey' => 'XxzQohtjPmhgGE1IbWVXIdJYUGSXIBddDLf1Qv_j-Us',
            ],
        ];

        $webPush = new WebPush($auth);

        // Retrieve all subscriptions from the database
        $subscriptions = Subscription::all();

        foreach ($subscriptions as $sub) {
            $subscription = WebPushSubscription::create([
                'endpoint' => $sub->endpoint,
                'publicKey' => $sub->p256dh,
                'authToken' => $sub->auth,
                'contentEncoding' => 'aesgcm', // Adjust if necessary
            ]);

            $webPush->queueNotification($subscription, json_encode($messageData));
        }

        // Flush the queue and send notifications
        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            if ($report->isSuccess()) {
                echo "Notification sent successfully for subscription: {$endpoint}\n";
            } else {
                echo "Failed to send notification for subscription: {$endpoint}. Reason: {$report->getReason()}\n";
            }
        }
    }


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
