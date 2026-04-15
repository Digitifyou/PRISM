<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = [
        'content_plan_id',
        'topic',
        'platform',
        'caption',
        'poster_copy',
        'image_url',
        'image_prompt',
        'status',
        'research_data',
        'strategy_notes',
        'scheduled_at',
        'published_at',
        'platform_post_id',
        'failure_reason',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    const STATUS_DRAFT     = 'draft';
    const STATUS_APPROVED  = 'approved';
    const STATUS_PUBLISHED = 'published';
    const STATUS_FAILED    = 'failed';

    public function contentPlan(): BelongsTo
    {
        return $this->belongsTo(ContentPlan::class);
    }

    public function insights(): HasMany
    {
        return $this->hasMany(Insight::class);
    }

    public function getEngagementRateAttribute(): float
    {
        $reach = $this->insights()->sum('reach');
        if ($reach <= 0) return 0;

        $engagement = $this->insights()->sum('likes') + 
                     $this->insights()->sum('comments') + 
                     $this->insights()->sum('shares');
        
        return round(($engagement / $reach) * 100, 2);
    }

    public function isDraft(): bool     { return $this->status === self::STATUS_DRAFT; }
    public function isApproved(): bool  { return $this->status === self::STATUS_APPROVED; }
    public function isPublished(): bool { return $this->status === self::STATUS_PUBLISHED; }
    public function isFailed(): bool    { return $this->status === self::STATUS_FAILED; }
}
