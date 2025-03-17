<?php

use App\Http\Controllers\V1\Media\WebMediaController;

Route::group([
    'as' => 'web.',
], function () {

    Route::group([
        'prefix' => 'v1',
        'as' => 'v1.',
    ], function () {

        Route::get('media/{media}/download/{key}', [WebMediaController::class, 'download'])->name('media.download');

    });

});
