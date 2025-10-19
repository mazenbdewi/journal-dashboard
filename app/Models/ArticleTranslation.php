<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleTranslation extends Model
{
    /** @use HasFactory<\Database\Factories\ArticleTranslationFactory> */
    use HasFactory;

    protected $fillable = [
        'article_id', 'locale', 'title', 'slug', 'abstract', 'content', 'keywords',
    ];

    protected $attributes = [
        'title' => 'Untitled',
        'abstract' => '',
        'content' => '',
        'keywords' => '',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $exists = self::where('article_id', $model->article_id)
                ->where('locale', $model->locale)
                ->exists();

            if ($exists) {
                throw new \Exception('هذه اللغة مضافه مسبقاً للمقالة');
            }
        });
    }
}
