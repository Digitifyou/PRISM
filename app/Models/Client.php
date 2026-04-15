<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'website_url',
        'industry',
        'brand_voice',
        'target_audience',
        'goals',
        'competitors',
        'target_audience_demographics',
        'pain_points',
        'social_links',
    ];

    protected $casts = [
        'social_links' => 'array',
    ];

    public function pillars()
    {
        return $this->hasMany(ClientPillar::class);
    }
}
