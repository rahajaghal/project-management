<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
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
            'contract'=>$this->contract,
            'contract_manager_status'=>$this->contract_manager_status,
            'project_manager_status'=>$this->project_manager_status,
            'status'=>$this->status,
            'client_edit_request'=>$this->client_edit_request,
            'need_edit'=>$this->need_edit,
            'admin_sign'=>$this->admin_sign,
            'project'=>$this->project,
        ];
    }
}
