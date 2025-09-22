<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'cv',
        'pre_accept',
        'post_id'
    ];
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
