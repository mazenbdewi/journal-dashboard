<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PageController extends Controller
{
    // public function show($slug, Request $request)
    // {
    //     $locale = $request->get('locale', app()->getLocale());
    //     app()->setLocale($locale);

    //     // البحث في جدول الترجمة بدلاً من جدول الصفحة
    //     $translation = \App\Models\PageTranslation::where('slug', $slug)
    //         ->where('locale', $locale)
    //         ->whereHas('page', fn ($q) => $q->where('active', true))
    //         ->with('page')
    //         ->first();

    //     if (! $translation) {
    //         return response()->json(['message' => 'Page not found'], 404);
    //     }

    //     $page = $translation->page;

    //     return response()->json([
    //         'id' => $page->id,
    //         'slug' => $translation->slug,
    //         'title' => $translation->title,
    //         'content' => $translation->content,
    //         'file' => $translation->file,
    //         'keywords' => explode(',', $translation->keywords ?? ''),
    //     ]);
    // }

    public function show($slug, Request $request)
    {
        $locale = $request->get('locale', app()->getLocale());
        app()->setLocale($locale);

        $translation = \App\Models\PageTranslation::where('slug', $slug)
            ->where('locale', $locale)
            ->whereHas('page', fn ($q) => $q->where('active', true))
            ->with(['page.journal.translations']) // تحميل ترجمة المجلة
            ->first();

        if (! $translation) {
            return response()->json(['message' => 'Page not found'], 404);
        }

        $page = $translation->page;

        // جلب slug المجلة حسب اللغة الحالية
        $journalSlug = $page->journal?->translations
            ->firstWhere('locale', $locale)
            ?->slug;

        return response()->json([
            'id' => $page->id,
            'slug' => $translation->slug,
            'title' => $translation->title,
            'content' => $translation->content,
            'file' => $translation->file,
            'keywords' => explode(',', $translation->keywords ?? ''),
            'journal_id' => $page->journal_id,
            'journal_title' => $page->journalTitle, // هذا يستخدم getJournalTitleAttribute
            'journal_slug' => $page->journal?->currentTranslation?->slug,
        ]);

    }
}
