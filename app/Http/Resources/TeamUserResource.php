<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamUserResource extends JsonResource
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
            'members'=>UserResource::collection($this->users)
//            'email'=>$this->email,
//            'role_id'=>$this->role_id,
//            'image'=>$this->image,
//            'cv'=>$this->cv,
//            'pivot'=>$this->pivot,
//            'team name'=>$this->teams->name,
        ];
    }
}
