<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IssueResource;
use App\Models\Issue;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    // public function show(Request $request, $slug)
    // {
    //     $locale = $request->get('locale', app()->getLocale());

    //     $issue = Issue::where('slug', $slug)
    //         ->with([
    //             'currentTranslation',
    //             'articles' => function ($q) {
    //                 $q->where('status', 'published')
    //                     ->with([
    //                         'translations',
    //                         'articleAuthors.user',
    //                         'revisions' => function ($q) {
    //                             $q->where('file_published', true);
    //                         },
    //                     ]);
    //             },
    //         ])
    //         ->firstOrFail();

    //     return new IssueResource($issue);
    // }

    public function show(Request $request, $slug)
    {
        $locale = $request->get('locale', app()->getLocale());
        app()->setLocale($locale); // ضبط اللغة

        $issue = Issue::where('is_published', true)
            ->whereHas('translations', function ($q) use ($slug, $locale) {
                $q->where('slug', $slug)
                    ->where('locale', $locale);
            })
            ->with([
                'translations' => fn ($q) => $q->where('locale', $locale),
                'journal.translations' => fn ($q) => $q->where('locale', $locale), // لتحميل ترجمة المجلة حسب اللغة
                'articles' => function ($q) {
                    $q->where('status', 'published')
                        ->with([
                            'translations',
                            'articleAuthors.user',
                            'revisions' => fn ($q) => $q->where('file_published', true),
                        ]);
                },
            ])
            ->first();

        if (! $issue) {
            return response()->json([
                'message' => 'issue not found',
                'status' => 404,
            ], 404);
        }

        return new IssueResource($issue);
    }
}
