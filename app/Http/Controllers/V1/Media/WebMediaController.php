<?php

namespace App\Http\Controllers\V1\Media;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WebMediaController
{
    /**
     * @param $media
     * @param $file
     * @return BinaryFileResponse
     */
    public function download($media, $file)
    {
        $media = Media::findOrFail($media);
        $files = $media->files;

        if (!array_key_exists($file, $files)) {
            abort(404);
        }

        return response()->file(Storage::disk($media->disk)->path($files[$file]));
    }
}
