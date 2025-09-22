<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory;
    protected $fillable=['project_type','project_description','cost','duration','requirements','document','cooperation_type','contact_time','client_id','team_id','team_approved','status','private','start','end','review'];
    protected $casts = [
        'requirements' => 'array',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function editRequest()
    {
        return $this->hasMany(EditRequest::class);
    }
    public function latesteditRequest(): HasOne
    {
        return $this->hasOne(EditRequest::class)->latestOfMany();
    }
    public function contract()
    {
        return $this->hasOne(Contract::class);
    }
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }
    public function sprints()
    {
        return $this->hasMany(Sprint::class);
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    public function emails()
    {
        return $this->belongsToMany(User::class);
    }
    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }
}
