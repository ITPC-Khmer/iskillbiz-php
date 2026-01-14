<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookMessage extends Model
{
    protected $fillable = [
        'facebook_id',
        'conversation_id',
        'sender_id',
        'sender_name',
        'message',
        'attachments',
        'created_time',
        'sticker',
    ];

    protected $casts = [
        'attachments' => 'array',
        'created_time' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(FacebookConversation::class, 'conversation_id');
    }
}
