<?php

namespace App\Http\Resources\V1\Application;

use App\Http\Resources\V1\Media\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
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
            'user_id' => $this->whenHas('user_id'),
            'name' => $this->whenHas('name'),
            'act_type' => $this->whenHas('act_type'),
            'recipient_office' => $this->whenHas('recipient_office'),
            'submission_date' => $this->whenHas('submission_date', fn($date) => $date->toDateString()),
            'document' => $this->whenLoaded('document', function () {
                return new MediaResource($this->resource->document);
            }),
        ];
    }
}
