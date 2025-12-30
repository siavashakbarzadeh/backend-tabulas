<?php

use App\Http\Controllers\V1\Application\ApplicationController;
use App\Http\Controllers\V1\Tabulas\TabulasKioskController;
use App\Http\Controllers\V1\Tabulas\TabulasMobileController;
use App\Http\Controllers\V1\User\Authentication\AuthenticationController;
use App\Http\Controllers\V1\Notification\SubscriptionController;
use App\Http\Controllers\V1\User\UserController;
use App\Http\Controllers\OAuthController;
use Illuminate\Support\Facades\Route;

Route::group([
    'as' => 'api.',
], function () {

    Route::group([
        'prefix' => 'v1',
        'as' => 'v1.',
    ], function () {
        // --- Push Notification test endpoints
        Route::get('/test-push', [SubscriptionController::class, 'testPush'])->name('test-push');
        Route::post('/push-notification', [SubscriptionController::class, 'pushSpecificMessage']);
        Route::get('/pushed-messages', [SubscriptionController::class, 'getAllMessages']);

        // --- Auth endpoints
        Route::post('login/microsoft', [AuthenticationController::class, 'loginByMicrosoft'])->name('login-microsoft');
        Route::post('login', [AuthenticationController::class, 'login']);
        Route::get('login', function () {
            return response()->json(['error' => 'Unauthenticated. Please provide a valid Bearer token.'], 401);
        })->name('login');
        Route::post('register', [AuthenticationController::class, 'register'])->name('register');

        // --- Mobile device push management
        Route::post('device/register',   [SubscriptionController::class, 'registerMob']);
        Route::delete('device/{token}',  [SubscriptionController::class, 'unregisterMob']);
        Route::post('push',              [SubscriptionController::class, 'pushMob']);

        // --- Test route
        Route::get('test', function () {
            return [
                'message' => 'Success',
                'fake_mobile' => fake()->numerify('09#########')
            ];
        });

        // ✅ PUBLIC ROUTE (Make sure this is the only one)
        Route::get('tabulas/kiosk/assemblea', [TabulasKioskController::class, 'assemblea'])->name('tabulas.kiosk.assemblea');

        // --- Tabulas routes with Microsoft JWT authentication
        Route::group([
            'middleware' => 'microsoft.jwt',
        ], function () {
            // --- Tabulas Mobile
            Route::get('tabulas/mobile/commissioni', [TabulasMobileController::class, 'commissioni'])->name('tabulas.mobile.commissioni');
            Route::get('tabulas/mobile/ultimiatti', [TabulasMobileController::class, 'ultimiatti'])->name('tabulas.mobile.ultimiatti');
            Route::get('tabulas/mobile/ultimdossier', [TabulasMobileController::class, 'ultimdossier'])->name('tabulas.mobile.ultimdossier');
            Route::get('tabulas/mobile/webtv', [TabulasMobileController::class, 'webtv'])->name('tabulas.mobile.webtv');
            Route::get('tabulas/mobile/ebook', [TabulasMobileController::class, 'ebook'])->name('tabulas.mobile.ebook');
            Route::get('tabulas/mobile/guidemanuali', [TabulasMobileController::class, 'guidemanuali'])->name('tabulas.mobile.guidemanuali');
            Route::get('tabulas/mobile/servizi', [TabulasMobileController::class, 'servizi'])->name('tabulas.mobile.servizi');

            // --- Tabulas Kiosk
            Route::get('tabulas/kiosk/commperm', [TabulasKioskController::class, 'commperm'])->name('tabulas.kiosk.commperm');
            Route::get('tabulas/kiosk/giuntealtrecomm', [TabulasKioskController::class, 'giuntealtrecomm'])->name('tabulas.kiosk.giuntealtrecomm');
            Route::get('tabulas/kiosk/bicamedeleg', [TabulasKioskController::class, 'bicamedeleg'])->name('tabulas.kiosk.bicamedeleg');
            Route::get('tabulas/kiosk/webtv', [TabulasKioskController::class, 'webtv'])->name('tabulas.kiosk.webtv');
            Route::get('tabulas/kiosk/pillolevideo', [TabulasKioskController::class, 'pillolevideo'])->name('tabulas.kiosk.pillolevideo');
        });

        // --- Protected routes with Sanctum (non-Tabulas routes)
        Route::group([
            'middleware' => 'auth:sanctum',
        ], function () {

            Route::get('/user', [UserController::class, 'user'])->name('user');
            Route::get('users/search', [UserController::class, 'search'])->name("users.search");

            Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
            Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
            Route::post('/applications/{application}/decline', [ApplicationController::class, 'decline']);
            Route::post('/applications/{application}/confirm', [ApplicationController::class, 'confirm']);
            Route::get('/applications/inbox/{userId}', [ApplicationController::class, 'inbox'])->name('applications.inbox');
            Route::get('/applications/outbox/{userId}', [ApplicationController::class, 'outbox'])->name('applications.outbox');

            // --- Save push subscription
            Route::post('/save-subscription', [SubscriptionController::class, 'saveSubscription'])->name('save-subscription');

            Route::get('/dashboard', function () {
                return 'ok';
            })->middleware('checkrole');

            Route::get('/admin', function () {
                return 'ok';
            })->middleware('checkrole:Admin');

            // --- New OAuth test endpoints
            Route::prefix('oauth')->group(function () {
                Route::get('/authorities', [OAuthController::class, 'authorities']);
                Route::get('/jwt', [OAuthController::class, 'jwt']);
                Route::get('/user', [OAuthController::class, 'user'])->middleware('checkrole:TBL_USER');
                Route::get('/guest', [OAuthController::class, 'guest'])->middleware('checkrole:TBL_GUEST');
                Route::get('/admin', [OAuthController::class, 'admin'])->middleware('checkrole:TBL_ADMIN');
            });
        });
    });
});
