<?php

use App\Http\Controllers\V1\Media\WebMediaController;

Route::group([
    'as' => 'web.',
], function () {

    Route::group([
        'as' => 'v1.',
    ], function () {

        Route::get('media/{media}/download/{file}', [WebMediaController::class, 'download'])
            ->middleware(['signed'])
            ->name('media.download');

    });

});
