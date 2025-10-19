<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $request->get('locale', app()->getLocale());

        // ترجمة المقال حسب اللغة المطلوبة مع fallback
        $translation = $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->first();

        $title = $translation?->title ?? 'بدون عنوان';
        $abstract = $translation?->abstract ?? null;
        $slug = $translation?->slug ?? $this->slug;

        // ترتيب المؤلفين بحيث يكون المؤلف الرئيسي أولاً
        $authors = collect($this->articleAuthors)
            ->sortByDesc(fn ($a) => $a->is_main_author)
            ->map(function ($author) use ($locale) {
                if ($author->is_registered && $author->user) {
                    return [
                        'name' => $locale === 'en'
                            ? ($author->user->name_en ?? $author->user->name)
                            : $author->user->name,
                        'email' => $author->user->email,
                        'affiliation' => $author->user->affiliation,
                        'is_main_author' => $author->is_main_author,
                    ];
                }

                // المؤلف غير مسجل
                return [
                    'name' => $locale === 'en'
                        ? ($author->external_name_en ?? $author->external_name)
                        : $author->external_name,
                    'email' => $author->external_email,
                    'affiliation' => $author->external_affiliation,
                    'is_main_author' => $author->is_main_author,
                ];
            })
            ->values();

        // ملف PDF المنشور
        $pdfFile = $this->revisions
            ->where('file_published', true)
            ->sortByDesc('created_at')
            ->first();

        // ترجمة العدد
        $issueTranslation = $this->issue?->translations
            ->firstWhere('locale', $locale)
            ?? $this->issue?->translations->first();

        // ترجمة المجلة
        $journalTranslation = $this->journal?->translations
            ->firstWhere('locale', $locale)
            ?? $this->journal?->translations->first();

        return [
            'id' => $this->id,
            'slug' => $slug,
            'doi' => $this->doi ?: null,
            'published_at' => $this->published_at,
            'title' => $title,
            'abstract' => $abstract,
            'authors' => $authors,
            'pdf_url' => $pdfFile ? asset('storage/'.$pdfFile->file_path) : null,
            'issue' => $this->issue ? [
                'id' => $this->issue->id,
                'volume' => $this->issue->volume,
                'slug' => $issueTranslation?->slug ?? $this->issue->slug,
                'title' => $issueTranslation?->title ?? null,
                'is_published' => $this->issue->is_published,
                'published_at' => $this->issue->published_at,
            ] : null,
            'journal' => $this->journal ? [
                'id' => $this->journal->id,
                'slug' => $journalTranslation?->slug ?? $this->journal->slug,
                'title' => $journalTranslation?->title ?? null,
            ] : null,
        ];
    }
}
