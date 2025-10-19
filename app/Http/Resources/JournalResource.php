<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $request->get('locale', app()->getLocale());

        return [
            'id' => $this->id,
            'code' => $this->code,
            'issn' => $this->issn,
            'e_issn' => $this->e_issn,
            'name' => $this->name,
            'created_by' => $this->created_by,
            'cover_url' => $this->cover_url,

            'current_translation' => new JournalTranslationResource(
                $this->translations->where('locale', $locale)->first() ?? $this->translations->first()
            ),
            'issues' => IssueResource::collection($this->whenLoaded('issues')),

            'latest_issue' => new IssueResource(
                $this->whenLoaded('issues')->first()
            ),

            'issues_count' => $this->issues_count,
            'published_issues_count' => $this->published_issues_count,
            'articles_count' => $this->articles_count,
            'published_articles_count' => $this->published_articles_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
