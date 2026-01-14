<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookConversation extends Model
{
    protected $fillable = [
        'facebook_id',
        'page_id',
        'user_id',
        'participant_name',
        'participant_id',
        'snippet',
        'updated_time',
        'unread_count',
        'can_reply',
    ];

    protected $casts = [
        'updated_time' => 'datetime',
        'can_reply' => 'boolean',
    ];

    public function messages()
    {
        return $this->hasMany(FacebookMessage::class, 'conversation_id');
    }

    public function labels()
    {
        return $this->belongsToMany(FacebookLabel::class, 'facebook_conversation_labels', 'conversation_id', 'label_id');
    }
}
