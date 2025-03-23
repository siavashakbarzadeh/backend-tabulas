<?php

namespace App\Http\Controllers\V1\Notification;

use App\Facades\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\PushedMessage;
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
    public function testPush(Request $request)
    {
        // Retrieve message data from the request or use default values
        $messageData = $request->input('messageData', [
            'title' => 'Ciao Senato!',
            'body'  => 'Hai una nuova notifica!',
            'icon'  => '/favicon.svg',
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
        $pushedMessage = PushedMessage::create($messageData);
    }
    public function pushSpecificMessage(Request $request)
    {
        $messageData = $request->messageData;

        $messageData['icon']='/favicon.svg';
        // Save the pushed message details to the database
        $pushedMessage = PushedMessage::create($messageData);

        // VAPID authentication configuration (use your env or hard-coded keys)
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

        return $pushedMessage;
    }


    public function saveSubscription(Request $request)
    {
        $subscriptionData = $request->all();


        $existingSubscription = Subscription::where('auth', $subscriptionData['keys']['auth'])->first();

        if (!$existingSubscription) {
            Subscription::create([
                'endpoint' => $subscriptionData['endpoint'],
                'p256dh'   => $subscriptionData['keys']['p256dh'],
                'auth'     => $subscriptionData['keys']['auth'],
            ]);
        }

        return response()->json(['success' => true]);
    }
}
