<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeHeroSectionTranslation extends Model
{
    protected $fillable = ['locale', 'title', 'description'];

    public function section()
    {
        return $this->belongsTo(HomeHeroSection::class, 'home_hero_section_id');
    }
}
