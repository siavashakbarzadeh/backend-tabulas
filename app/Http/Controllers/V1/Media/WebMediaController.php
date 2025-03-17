<?php

namespace App\Http\Controllers\V1\Media;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class WebMediaController
{
    public function download($media, $file)
    {
        $media = Media::findOrFail($media);
        $files = $media->files;
        if (!array_key_exists($file, $files)) {
            abort(404);
        }
        return response()->download(Storage::disk($media->disk)->path($file));
    }
}
