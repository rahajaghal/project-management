<?php

namespace App\Http\Resources;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SprintTaskResource extends JsonResource
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
            'label'=>$this->label,
            'description'=>$this->description,
            'goal'=>$this->goal,
            'start'=>$this->start,
            'end'=>$this->end,
            'status'=>$this->status,
            'project_id'=>$this->project_id,
            'incomplete_tasks_count' => Task::where('sprint_id', $this->id)
                ->where('completion', '<', 100)
                ->count(),
            'tasks'=>TaskResource::collection($this->tasks),
        ];
    }
}
