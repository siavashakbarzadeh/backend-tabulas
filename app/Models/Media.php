<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    /** @use HasFactory<\Database\Factories\MediaFactory> */
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'mediaable_type',
        'mediaable_id',
        'original_name',
        'type',
        'disk',
        'files',
        'mime_type',
        'size',
        'collection',
    ];

    /**
     * @return bool
     */
    public function isPublicDisk(): bool
    {
        return true;
        return $this->disk === 'public';
    }

    /**
     * @return array
     */
    public function getPublicFiles(): array
    {
        return collect($this->files)
            ->mapWithKeys(function ($path, $key) {

                return [
                    $key => Storage::url($path),
                ];
            })->toArray();
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo
     */
    public function mediaable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param $q
     * @param string $collection
     * @return mixed
     */
    public function scopeCollection($q, string $collection): mixed
    {
        return $q->where('collection', $collection);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'files' => 'array',
        ];
    }
}
