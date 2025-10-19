<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeAboutSection;
use App\Models\HomeHeroSection;
use App\Models\HomePartner;
use App\Models\HomeStat;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class HomePageController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->get('locale', app()->getLocale());

        return response()->json([
            'hero' => tap(
                HomeHeroSection::where('active', true)
                    ->with(['translations' => fn ($q) => $q->where('locale', $locale)])
                    ->latest()
                    ->first(),
                fn ($item) => $item?->setRelation('translation', $item->translations->first())
            )?->only(['id', 'image', 'active', 'translation']),

            'about' => tap(
                HomeAboutSection::where('active', true)
                    ->with(['translations' => fn ($q) => $q->where('locale', $locale)])
                    ->latest()
                    ->first(),
                fn ($item) => $item?->setRelation('translation', $item->translations->first())
            )?->only(['id', 'image', 'active', 'order', 'translation']),

            'stats' => HomeStat::where('active', true)
                ->orderBy('order')
                ->with(['translations' => fn ($q) => $q->where('locale', $locale)])
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'icon' => $item->icon,
                    'number' => $item->number,
                    'translation' => $item->translations->first(),
                ]),

            'partners' => HomePartner::where('active', true)
                ->orderBy('order')
                ->with(['translations' => fn ($q) => $q->where('locale', $locale)])
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'image' => $item->image,
                    'link' => $item->link,
                    'translation' => $item->translations->first(),
                ]),

            'team' => TeamMember::where('active', true)
                ->orderBy('order')
                ->with(['translations' => fn ($q) => $q->where('locale', $locale)])
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'image' => $item->image,
                    'social' => [
                        'twitter' => $item->twitter,
                        'facebook' => $item->facebook,
                        'instagram' => $item->instagram,
                        'linkedin' => $item->linkedin,
                    ],
                    'translation' => $item->translations->first(),
                ]),
        ]);
    }
}
