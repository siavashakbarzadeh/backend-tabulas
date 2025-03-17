<?php

namespace App\Http\Resources\V1\Media;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    private bool $withUrls = false;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

    /**
     * @return bool
     */
    public function hasWithUrls(): bool
    {
        return $this->withUrls;
    }
    public function withUrls(): static
    {
        $this->withUrls = true;
        return $this;
    }
}
