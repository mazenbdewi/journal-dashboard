<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JournalResource;
use App\Models\Journal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    // في JournalController.php
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 6);
        $page = $request->get('page', 1);
        $locale = $request->get('locale', app()->getLocale());

        $journals = Journal::with([
            'translations',
            'issues' => fn ($query) => $query->orderBy('published_at', 'desc')->take(1),
        ])
            ->where('code', '!=', 'general')
            ->withCount([
                'issues',
                'articles',
                'issues as published_issues_count' => fn ($query) => $query->where('is_published', true),
                'articles as published_articles_count' => fn ($query) => $query->where('status', 'published'),
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return JournalResource::collection($journals);
    }

    // JournalController.php
    public function getTranslatedSlug(Request $request)
    {
        $slug = $request->query('slug');
        $locale = $request->query('locale', app()->getLocale());

        $translation = \App\Models\JournalTranslation::where('slug', $slug)->first();

        if (! $translation) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $journal = $translation->journal;

        $translated = $journal->translations()->where('locale', $locale)->first();

        if (! $translated) {
            return response()->json(['slug' => $slug]); // fallback
        }

        return response()->json(['slug' => $translated->slug]);
    }

    /**
     * عرض مجلة محددة مع تفاصيلها
     */
    // public function show(Request $request, $slug)
    // {
    //     // تعيين اللغة المطلوبة
    //     $locale = $request->get('locale', app()->getLocale());
    //     app()->setLocale($locale);

    //     $journal = Journal::where('slug', $slug)
    //         ->with([
    //             'translations', // ترجمة المجلة
    //             'issues' => function ($q) {
    //                 $q->orderByDesc('published_at')
    //                     ->withCount('articles')
    //                     ->with('currentTranslation'); // تحميل الترجمة الحالية للعدد
    //             },
    //         ])
    //         ->withCount([
    //             'issues',
    //             'articles',
    //             'issues as published_issues_count' => fn ($q) => $q->where('is_published', true),
    //             'articles as published_articles_count' => fn ($q) => $q->where('status', 'published'),
    //         ])
    //         ->firstOrFail();

    //     return new JournalResource($journal);
    // }

    // public function getName(string $slug, Request $request)
    // {
    //     $locale = $request->get('locale', app()->getLocale());
    //     app()->setLocale($locale);

    //     $journal = Journal::where('slug', $slug)
    //         ->with(['translations' => fn ($q) => $q->where('locale', $locale)])
    //         ->firstOrFail();

    //     $translation = $journal->translations->first();

    //     return response()->json([
    //         'name' => $translation?->title ?? $journal->name ?? 'Unknown Journal',
    //     ]);
    // }

    public function show(Request $request, $slug)
    {
        $locale = $request->get('locale', app()->getLocale());
        app()->setLocale($locale);

        // ابحث عن المجلة من خلال الـ slug في جدول journal_translations
        $journal = Journal::whereHas('translations', function ($query) use ($slug, $locale) {
            $query->where('locale', $locale)
                ->where('slug', $slug);
        })
            ->with([
                'translations',
                'issues' => function ($q) {
                    $q->orderByDesc('published_at')
                        ->withCount('articles')
                        ->with('currentTranslation');
                },
            ])
            ->withCount([
                'issues',
                'articles',
                'issues as published_issues_count' => fn ($q) => $q->where('is_published', true),
                'articles as published_articles_count' => fn ($q) => $q->where('status', 'published'),
            ])
            ->first();

        if (! $journal) {
            return response()->json([
                'message' => 'journal not found',
                'status' => 404,
            ], 404);
        }

        return new JournalResource($journal);
    }
}
