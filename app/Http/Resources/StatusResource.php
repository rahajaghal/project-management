<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'order'=>$this->order,
//            'tasks'=>TaskResource::collection($this->whenLoaded('tasks')),
            'tasks'=>TaskResource::collection($this->tasks),

        ];
    }
}
