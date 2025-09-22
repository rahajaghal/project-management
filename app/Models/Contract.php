<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;
    protected $fillable=['contract','project_id','contract_manager_status','contract_manager_notes','project_manager_status','project_manager_notes','client_sign','status','client_edit_request','need_edit','admin_sign'];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
