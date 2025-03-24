<?php

use App\Http\Controllers\V1\Application\ApplicationController;
use App\Http\Controllers\V1\Tabulas\TabulasKioskController;
use App\Http\Controllers\V1\Tabulas\TabulasMobileController;
use App\Http\Controllers\V1\User\Authentication\AuthenticationController;
use App\Http\Controllers\V1\Notification\SubscriptionController;
use App\Http\Controllers\V1\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group([
    'as' => 'api.',
], function () {

    Route::group([
        'prefix' => 'v1',
        'as' => 'v1.',
    ], function () {
        Route::get('/test-push', [SubscriptionController::class, 'testPush'])->name('test-push');
        Route::post('/push-notification', [SubscriptionController::class, 'pushSpecificMessage']);
        Route::post('login/microsoft', [AuthenticationController::class, 'loginByMicrosoft'])->name('login-microsoft');
        Route::post('login', [AuthenticationController::class, 'login'])->name('login');
        Route::post('register', [AuthenticationController::class, 'register'])->name('register');
        Route::get('/pushed-messages', [PushedMessageController::class, 'getAllMessages']);


        Route::get('test', function () {
            return [
                'message' => 'Success',
                'fake_mobile' => fake()->numerify('09#########')
            ];
        });

        Route::group([
            'middleware' => 'auth:sanctum',
        ], function () {

            Route::get('/user', [UserController::class, 'user'])->name('user');
            Route::get('users/search', [UserController::class, 'search'])->name("users.search");

            Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
            Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
            Route::post('/applications/{application}/decline', [ApplicationController::class, 'decline']);
            Route::post('/applications/{application}/confirm', [ApplicationController::class, 'confirm']);
            Route::get('/applications/inbox/{userId}', [ApplicationController::class, 'inbox'])
            ->name('applications.inbox');
        Route::get('/applications/outbox/{userId}', [ApplicationController::class, 'outbox'])
            ->name('applications.outbox');

            Route::get('tabulas/mobile/commissioni', [TabulasMobileController::class, 'commissioni'])->name('tabulas.mobile.commissioni');
            Route::get('tabulas/mobile/ultimiatti', [TabulasMobileController::class, 'ultimiatti'])->name('tabulas.mobile.ultimiatti');
            Route::get('tabulas/mobile/ultimdossier', [TabulasMobileController::class, 'ultimdossier'])->name('tabulas.mobile.ultimdossier');
            Route::get('tabulas/mobile/webtv', [TabulasMobileController::class, 'webtv'])->name('tabulas.mobile.webtv');
            Route::get('tabulas/mobile/ebook', [TabulasMobileController::class, 'ebook'])->name('tabulas.mobile.ebook');
            Route::get('tabulas/mobile/guidemanuali', [TabulasMobileController::class, 'guidemanuali'])->name('tabulas.mobile.guidemanuali');
            Route::get('tabulas/mobile/servizi', [TabulasMobileController::class, 'servizi'])->name('tabulas.mobile.servizi');

            Route::get('tabulas/kiosk/assemblea', [TabulasKioskController::class, 'assemblea'])->name('tabulas.kiosk.assemblea');
            Route::get('tabulas/kiosk/commperm', [TabulasKioskController::class, 'commperm'])->name('tabulas.kiosk.commperm');
            Route::get('tabulas/kiosk/giuntealtrecomm', [TabulasKioskController::class, 'giuntealtrecomm'])->name('tabulas.kiosk.giuntealtrecomm');
            Route::get('tabulas/kiosk/bicamedeleg', [TabulasKioskController::class, 'bicamedeleg'])->name('tabulas.kiosk.bicamedeleg');
            Route::get('tabulas/kiosk/webtv', [TabulasKioskController::class, 'webtv'])->name('tabulas.kiosk.webtv');
            Route::get('tabulas/kiosk/pillolevideo', [TabulasKioskController::class, 'pillolevideo'])->name('tabulas.kiosk.pillolevideo');
            Route::post('/save-subscription', [SubscriptionController::class, 'saveSubscription'])->name('save-subscription');
        });

    });
});
