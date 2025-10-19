<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id', 'reviewer_id', 'assigned_at', 'deadline', 'status',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'deadline' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
