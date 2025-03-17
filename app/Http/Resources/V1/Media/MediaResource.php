<?php

namespace App\Http\Resources\V1\Media;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->whenHas('id'),
            'mediaable_type' => $this->whenHas('mediaable_type'),
            'mediaable_id' => $this->whenHas('mediaable_id'),
            'original_name' => $this->whenHas('original_name'),
            'type' => $this->whenHas('type'),
            'files' => $this->when($this->resource->isPublicDisk(), function () {
                return $this->resource->getPublicFiles();
            }, function () {
                return $this->resource->getTemporaryUrlFiles();
            })
        ];
    }

}
