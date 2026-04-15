<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'client_pillar_id',
        'niche', // Can now technically be null, but keeping it fillable just in case.
        'topics',
        'frequency',
        'platforms',
        'ai_provider'
    ];

    protected $casts = [
        'topics' => 'array',
        'platforms' => 'array',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class, 'content_plan_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function pillar()
    {
        return $this->belongsTo(ClientPillar::class, 'client_pillar_id');
    }
}
