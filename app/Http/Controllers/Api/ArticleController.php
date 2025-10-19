<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * عرض مقال محدد حسب slug الخاص بالترجمة
     */
    public function show(Request $request, $slug)
    {
        $locale = $request->get('locale', app()->getLocale());
        app()->setLocale($locale);

        $article = Article::where('status', 'published')               // الحالة منشورة
            ->whereNotNull('published_at')                             // تاريخ النشر موجود
            ->whereHas('issue', fn ($q) => $q->where('is_published', true)) // العدد منشور
            ->whereHas('translations', fn ($q) => $q->where('slug', $slug)->where('locale', $locale))
            ->with([
                'translations' => fn ($q) => $q->where('locale', $locale),
                'articleAuthors.user',
                'issue.translations' => fn ($q) => $q->where('locale', $locale),
                'journal.translations' => fn ($q) => $q->where('locale', $locale),
                'revisions' => fn ($q) => $q->where('file_published', true),
            ])
            ->first();
        if (! $article) {
            return response()->json([
                'message' => 'article not found',
                'status' => 404,
            ], 404);
        }

        return new ArticleResource($article);
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        $locale = $request->input('locale', app()->getLocale());
        $filter = $request->input('filter', 'all');
        $perPage = $request->input('per_page', 10);

        $articlesQuery = Article::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->whereHas('issue', fn ($q) => $q->where('is_published', true))
            ->with([
                'translations' => fn ($q) => $q->where('locale', $locale),
                'articleAuthors.user',
                'issue.translations' => fn ($q) => $q->where('locale', $locale),
                'journal.translations' => fn ($q) => $q->where('locale', $locale),
            ]);

        if ($query) {
            $articlesQuery->where(function ($q) use ($query, $filter, $locale) {
                if ($filter === 'title' || $filter === 'all') {
                    $q->orWhereHas('translations', fn ($tq) => $tq->where('locale', $locale)->where('title', 'like', "%{$query}%"));
                }
                if ($filter === 'abstract' || $filter === 'all') {
                    $q->orWhereHas('translations', fn ($tq) => $tq->where('locale', $locale)->where('abstract', 'like', "%{$query}%"));
                }
                if ($filter === 'keywords' || $filter === 'all') {
                    $q->orWhereHas('translations', fn ($tq) => $tq->where('locale', $locale)->where('keywords', 'like', "%{$query}%"));
                }
                if ($filter === 'author' || $filter === 'all') {
                    $q->orWhereHas('articleAuthors', fn ($aq) => $aq->where('external_name', 'like', "%{$query}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$query}%"))
                    );
                }
            });
        }

        $articles = $articlesQuery->latest()->paginate($perPage);

        if ($articles->isEmpty()) {
            return response()->json([
                'message' => 'No articles found',
            ], 404);
        }

        // تعديل النتائج لتكون بالترجمة الصحيحة ولعرض issue و journal
        $articles->getCollection()->transform(function ($article) use ($locale) {
            $translation = $article->translations->first();
            $mainAuthor = $article->articleAuthors->firstWhere('is_main_author', true)
                ?? $article->articleAuthors->first();

            $issueTranslation = $article->issue?->translations->firstWhere('locale', $locale)
                ?? $article->issue?->translations->first();

            $journalTranslation = $article->journal?->translations->firstWhere('locale', $locale)
                ?? $article->journal?->translations->first();

            return [
                'id' => $article->id,
                'slug' => $translation?->slug ?? null,
                'title' => $translation?->title ?? 'بدون عنوان',
                'abstract' => $translation?->abstract ?? '',
                'main_author' => $mainAuthor?->user?->name
                                ?? ($locale === 'en' ? $mainAuthor?->external_name_en : $mainAuthor?->external_name)
                                ?? 'بدون مؤلف',
                'issue' => $article->issue ? [
                    'id' => $article->issue->id,
                    'slug' => $issueTranslation?->slug ?? null,
                    'title' => $issueTranslation?->title ?? null,
                ] : null,
                'journal' => $article->journal ? [
                    'id' => $article->journal->id,
                    'slug' => $journalTranslation?->slug ?? null,
                    'title' => $journalTranslation?->title ?? null,
                ] : null,
            ];
        });

        return response()->json($articles);
    }
}
