<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IssueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = $request->get('locale', app()->getLocale());
        $journalTranslation = $this->journal?->translations
            ->where('locale', $locale)
            ->first()
                  ?? $this->journal?->translations->first();

        return [
            'id' => $this->id,
            'journal_id' => $this->journal_id,
            'journal' => [
                'slug' => $journalTranslation?->slug ?? $this->journal->slug,
                'title' => $journalTranslation?->title ?? $this->journal->name,
            ],
            'volume' => $this->volume,
            'number' => $this->number,
            'year' => $this->year,
            'published_at' => $this->published_at,
            'is_published' => $this->is_published,
            'articles_count' => $this->articles_count,
            'current_translation' => new IssueTranslationResource(
                $this->translations->where('locale', $locale)->first()
            ),
            'articles' => ArticleResource::collection($this->whenLoaded('articles')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
