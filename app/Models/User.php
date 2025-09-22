<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'image',
        'cv',
        'approved',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function providers()
    {
        return $this->hasMany(Provider::class);
    }
    public function role()
    {
        return$this->belongsTo(Role::class);
    }
    public function contactUs()
    {
        return $this->hasMany(ContactUs::class);
    }
    public function teams()
    {
        return $this->belongsToMany(Team::class);
    }
    public function projects()
    {
        return $this->hasMany(Project::class,'client_id');
    }
    public function drafts()
    {
        return $this->hasMany(Draft::class);
    }
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    public function emails()
    {
        return $this->belongsToMany(Project::class);
    }
}
