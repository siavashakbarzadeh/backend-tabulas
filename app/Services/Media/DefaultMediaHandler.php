<?php

namespace App\Services\Media;

use App\Services\Media\Interfaces\MediaInterface;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DefaultMediaHandler implements MediaInterface
{
    /**
     * @param UploadedFile $file
     * @param string $disk
     * @param string|null $directory
     * @return array
     */
    public function upload(UploadedFile $file, string $disk, string $directory = null): array
    {
        return [
            'original' => Storage::disk($disk)->put($directory, new File($file)),
        ];
    }
}
