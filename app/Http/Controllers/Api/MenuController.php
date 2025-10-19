<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalTranslation;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function show($journalSlug, Request $request)
    {
        $locale = $request->get('locale', app()->getLocale());
        $menu = null;
        $journalData = null;

        // 🔍 حاول إيجاد ترجمة المجلة حسب slug
        $translation = JournalTranslation::where('slug', $journalSlug)
            ->where('locale', $locale)
            ->first();

        if ($translation) {
            // ✅ جلب المجلة المرتبطة
            $journal = $translation->journal;

            // ✅ الترجمة الحالية للمجلة
            $currentTranslation = $journal->translations()
                ->where('locale', $locale)
                ->first();

            // ✅ القائمة الخاصة بالمجلة
            $menu = Menu::where('is_visible', true)
                ->where('journal_id', $journal->id)
                ->where('language', $locale)
                ->with('menuItems')
                ->first();

            // ✅ بيانات المجلة
            $journalData = [
                'name' => $currentTranslation?->title ?? $journal->name,
                'slug' => $currentTranslation?->slug ?? $journal->code,
                'issn' => $journal->issn,
                'e_issn' => $journal->e_issn,
                'logo_url' => $currentTranslation && $currentTranslation->image
                    ? Storage::disk('journal')->url($currentTranslation->image)
                    : ($journal->currentTranslation && $journal->currentTranslation->image
                        ? Storage::disk('journal')->url($journal->currentTranslation->image)
                        : asset('images/default-journal.png')),
            ];
        } else {
            // 🧭 fallback إلى المجلة العامة
            $menu = Menu::whereNull('journal_id')
                ->where('language', $locale)
                ->with('menuItems')
                ->first();

            $journalData = [
                'name' => $locale === 'ar' ? 'المجلة العامة' : 'General Journal',
                'slug' => $locale === 'ar' ? 'المجلة-العامة' : 'general-journal',
                'issn' => '0000-0000',
                'e_issn' => '0000-0000',
                'logo_url' => asset('images/default-journal.png'),
            ];
        }

        return response()->json([
            'menu_items' => $menu?->menuItems ?? [],
            'journal' => $journalData,
        ]);
    }
}
