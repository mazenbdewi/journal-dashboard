<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeAboutSectionTranslation extends Model
{
    protected $fillable = ['locale', 'title', 'description', 'vision', 'mission', 'goals'];

    public function section()
    {
        return $this->belongsTo(HomeAboutSection::class, 'home_about_section_id');
    }
}
