<?php

namespace App\Http\Controllers\V1\Media;

use App\Models\Media;

class WebMediaController
{
    public function download($media,$file)
    {
        $media = Media::find($media);
        dd($media,$file);
    }
}
