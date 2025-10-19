<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleRevision extends Model
{
    /** @use HasFactory<\Database\Factories\ArticleRevisionFactory> */
    use HasFactory;

    protected $fillable = [
        'article_id', 'file_path', 'note', 'file_published',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function getDownloadUrlAttribute(): string
    {
        return asset('storage/'.$this->file_path);
    }

    public function getFileTypeNameAttribute(): string
    {
        return match ($this->file_type) {
            'main' => 'الملف الرئيسي',
            'revision' => 'مراجعة',
            'supplementary' => 'ملف مكمل',
            default => 'ملف'
        };
    }
}
