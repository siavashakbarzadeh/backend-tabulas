<?php

Route::get('/link-storage', function () {
    Artisan::call('storage:link');
});
