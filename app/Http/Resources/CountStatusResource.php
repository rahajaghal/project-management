<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $incompleteBySprint = $this->tasks
            ->groupBy('sprint_id')
            ->map(function ($tasks) {
                return $tasks->where('completion', '<', 100)->count();
            });
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'order'=>$this->order,
            'incomplete_tasks_by_sprint' => $incompleteBySprint, //  added
            'tasks'=>TaskResource::collection($this->tasks),

        ];
    }
}
