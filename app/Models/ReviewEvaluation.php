<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'page_count_appropriate',
        'page_count_comment',
        'titles_match_languages',
        'titles_match_comment',
        'objective_clearly_defined',
        'objective_achieved',
        'objective_comment',
        'all_relevant_references',
        'references_are_recent',
        'references_comment',
        'scientific_value',
        'published_before',
        'published_before_comment',
        'results_verifiable',
        'results_well_documented',
        'results_scientifically_acceptable',
        'results_comment',
        'research_methodology',
        'methodology_suitable',
        'methodology_comment',
        'research_significance',
        'if_weak_comment',
        'comments_for_author',
        'research_type',
        'file_path',
        'note',
    ];

    protected $casts = [
        'research_significance' => 'array',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
