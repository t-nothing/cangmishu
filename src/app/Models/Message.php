<?php

namespace App\Models;

class Message extends Model
{
    protected $fillable = [
        'content', 'user_id'
    ];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}