<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;
    protected $fillable=['name'];
    public function users()
    {
        return $this->belongsToMany(User::class,'team_user','team_id','user_id')
            ->withPivot('is_manager')->using(TeamUser::class);
    }
    public function user($userId)
    {
        return $this->belongsToMany(User::class,'team_user','team_id','user_id')
            ->withPivot('is_manager')
            ->wherePivot('user_id',$userId)->using(TeamUser::class);

    }
    public function projects()
    {
        return $this->hasMany(Project::class,'team_id');
    }
}
