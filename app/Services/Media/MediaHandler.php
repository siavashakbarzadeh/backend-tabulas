<?php

namespace App\Services\Media;

use App\Enums\MediaType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaHandler
{
    /**
     * @param UploadedFile $file
     * @param string $disk
     * @param string|null $directory
     * @return array
     */
    public function upload(UploadedFile $file, string $disk, string $directory = null): array
    {
        $handler = $this->getHandler($file);
        dd(resolve($handler->getHandler()));
        return resolve($handler->getHandler())->upload($file, $disk, $directory);
    }

    /**
     * @param $file
     * @return MediaType
     */
    public function getHandler($file): MediaType
    {
        $mimeType = $file->getClientMimeType();
        return collect(MediaType::cases())->first(function ($type) use ($mimeType) {
            $mimeTypes = $type->getMimeTypes();
            return $mimeTypes && in_array($mimeType, $mimeTypes);
        }, MediaType::DEFAULT);
    }

}
