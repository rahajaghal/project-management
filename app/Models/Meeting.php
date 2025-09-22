<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;
    protected $fillable=['date','meeting_type','project_id'];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
