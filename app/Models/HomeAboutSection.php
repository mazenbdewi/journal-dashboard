<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeAboutSection extends Model
{
    protected $fillable = ['active'];

    public function translations(): HasMany
    {
        return $this->hasMany(HomeAboutSectionTranslation::class);
    }
}
