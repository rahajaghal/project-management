<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'title'=>$this->title,
            'description'=>$this->description,
            'sprint_id'=>$this->sprint?->id,
            'sprint'=>$this->sprint?->label,
            'project_id'=>$this->project_id,
            'status'=>$this->status->name,
            'assigned_to'=>new UserResource($this->user),
            'priority'=>$this->priority,
            'completion'=>$this->completion,
            'start'=>$this->start,
            'end'=>$this->end

        ];
    }
}
