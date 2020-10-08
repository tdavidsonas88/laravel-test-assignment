<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'status',
        'owner',
    ];

    public function users()
    {
        return $this->belongsToMany(
            User::class, 'task_user', 'task_id', 'user_id'
        );
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'task_id', 'id')->select();
    }

}
