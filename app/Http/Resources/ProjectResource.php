<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'project_type'=>$this->project_type,
            'project_description'=>$this->project_description,
            'requirements'=>$this->requirements,
            'document'=>$this->document,
            'cooperation_type'=>$this->cooperation_type,
            'contact_time'=>$this->contact_time,
            'private'=>$this->private,

            'contract'=>$this->contract,
            'status'=>$this->status,
            'start'=>$this->start,
            'end'=>$this->end,
            'review'=>$this->review,
             'team' => $this->whenLoaded('team', function () {
                return [
                    'id' => $this->team->id,
                    'name' => $this->team->name,
                    'members' => $this->team->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'phone'=>$user->phone,
                            'image'=>$user->image,
                            'role'=>$user->role->role,
                            'is_manager' => (bool) $user->pivot->is_manager,
                        ];
                    }),
                ];
            }),
        ];
    }
}
