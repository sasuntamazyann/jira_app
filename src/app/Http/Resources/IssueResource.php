<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IssueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'summary' => $this->summary,
            'description' => $this->description,
            'project_id' => $this->project_id,
            'type' => new TypeResource($this->type),
            'created_at' => $this->created_at,
        ];
    }
}
