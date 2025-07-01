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
    public function getAllMessages(Request $request)
    {
        $messages = PushedMessage::orderBy('created_at', 'desc')->get();
        return response()->json($messages);
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


        /** POST /device/register */
    public function registerMob(Request $request)
    {
        $validated = $request->validate([
            'token'    => 'required|string',
            'platform' => 'required|in:android,ios,web',
        ]);

        $device = DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id'      => $request->user()->id,
                'platform'     => $validated['platform'],
                'last_seen_at' => now(),
            ],
        );

        return response()->json($device, $device->wasRecentlyCreated ? 201 : 200);
    }

        /** DELETE /device/{token} */
    public function unregisterMob(Request $request, string $token)
    {
        DeviceToken::where('token', $token)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->noContent();
    }

        /** POST /push – Send a single- or multi-device push via FCM */
    public function pushMob(Request $request)
    {
        $payload = $request->validate([
            'title'   => 'required|string|max:64',
            'body'    => 'required|string|max:512',
            'data'    => 'array',
            'user_id' => 'integer|exists:users,id',    // optional: broadcast to a user
            'token'   => 'string',                     // optional: broadcast to a single device
        ]);

        // --- 1. Resolve device tokens ---------------------------------------
        $query = DeviceToken::query()->whereNotNull('token');

        if (isset($payload['user_id'])) {
            $query->where('user_id', $payload['user_id']);
        } elseif (isset($payload['token'])) {
            $query->where('token', $payload['token']);
        }

        $tokens = $query->pluck('token')->all();
        if (blank($tokens)) {
            return response()->json(['message' => 'No target devices'], 404);
        }

        // --- 2. Build FCM request body --------------------------------------
        $message = [
            'message' => [
                'token'        => count($tokens) === 1 ? $tokens[0] : null,
                'tokens'       => count($tokens) > 1  ? $tokens       : null,
                'notification' => [
                    'title' => $payload['title'],
                    'body'  => $payload['body'],
                ],
                'data' => $payload['data'] ?? [],
                'android' => [
                    'priority' => 'high',
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                ],
            ],
        ];

        // --- 3. Send ---------------------------------------------------------
        $accessToken = cache()->remember('fcm_access_token', now()->addMinutes(50), function () {
            // Uses service-account JSON stored as env var
            $keyFile    = storage_path('app/tabulas-62017-firebase-adminsdk-fbsvc-6cf4c9f1bf.json');
            $client     = new \Google_Client();
            $client->setAuthConfig($keyFile);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();
            return $client->getAccessToken()['access_token'] ?? null;
        });

        $response = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'application/json; UTF-8'])
            ->post(sprintf(
                'https://fcm.googleapis.com/v1/projects/%s/messages:send',
                'tabulas-62017'
            ), $message);

        return response()->json($response->json(), $response->status());
    }



}
