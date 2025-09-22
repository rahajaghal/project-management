<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditRequest extends Model
{
    use HasFactory;
    protected $fillable=['message','project_id','from_user_id','to_user_id'];
    public function projects()
    {
        return $this->belongsTo(Project::class);
    }
}
