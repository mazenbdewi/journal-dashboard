<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeHeroSection extends Model
{
    protected $fillable = ['image', 'active'];

    public function translations(): HasMany
    {
        return $this->hasMany(HomeHeroSectionTranslation::class);
    }
}
