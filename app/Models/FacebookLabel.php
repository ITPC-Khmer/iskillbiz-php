<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, mixed $id)
 */
class FacebookLabel extends Model
{
    protected $fillable = [
        'user_id',
        'page_id',
        'name',
        'color',
    ];

    public function conversations()
    {
        return $this->belongsToMany(FacebookConversation::class, 'facebook_conversation_labels', 'label_id', 'conversation_id');
    }
}
