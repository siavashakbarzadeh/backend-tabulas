<?php

namespace App\Services\Media\Interfaces;

use Illuminate\Http\UploadedFile;

interface MediaInterface
{
    /**
     * @param UploadedFile $file
     * @param string $disk
     * @param string|null $directory
     * @return array
     */
    public function upload(UploadedFile $file, string $disk, string $directory = null): array;
}
