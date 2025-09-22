<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable=['title',
        'description',
        'project_id',
        'sprint_id',
        'status_id',
        'user_id',
        'priority',
        'completion',
        'start',
        'end'
    ];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
