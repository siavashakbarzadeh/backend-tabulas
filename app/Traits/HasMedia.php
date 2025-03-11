<?php

namespace App\Traits;

use App\Models\Media;
use App\Services\Media\MediaHandler;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\UploadedFile;

trait HasMedia
{
    use HasRelationships;

    /**
     * @var string
     */
    private string $disk = 'public';

    /**
     * @var string|null
     */
    private ?string $directory = null;

    /**
     * @var string|null
     */
    private ?string $collection = null;

    /**
     * @param UploadedFile $file
     * @return Model
     */
    public function uploadMedia(UploadedFile $file): Model
    {
        $mediaHandler = resolve(MediaHandler::class);
        $files = $mediaHandler->upload($file, $this->getDisk(), $this->getDirectory());
        return $this->storeModel([
            'user_id' => auth()->id(),
            'original_name' => $file->getClientOriginalName(),
            'type' => $mediaHandler->getHandler($file)->getValue(),
            'disk' => $this->getDisk(),
            'files' => $files,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'collection' => $this->getCollection(),
        ]);
    }

    private function storeModel(array $attributes = []): Model
    {
        return $this->medias()->create($attributes);
    }

    /**
     * @return MorphMany
     */
    public function medias(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediaable');
    }

    /**
     * @return MorphOne
     */
    public function media(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediaable');
    }

    /**
     * @return string
     */
    public function getDisk(): string
    {
        return $this->disk;
    }

    /**
     * @return $this
     */
    public function privateDisk(): static
    {
        $this->setDisk('private');
        return $this;
    }

    /**
     * @return $this
     */
    public function privatePublic(): static
    {
        $this->setDisk('public');
        return $this;
    }

    /**
     * @param string $disk
     * @return $this
     */
    public function setDisk(string $disk): static
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    /**
     * @param string|null $directory
     * @return $this
     */
    public function setDirectory(?string $directory): static
    {
        $this->directory = $directory;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCollection(): ?string
    {
        return $this->collection;
    }

    /**
     * @param string|null $collection
     * @return $this
     */
    public function setCollection(?string $collection): static
    {
        $this->collection = $collection;
        return $this;
    }

}
