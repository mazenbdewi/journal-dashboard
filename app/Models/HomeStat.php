<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeStat extends Model
{
    protected $fillable = ['number', 'icon', 'active', 'order'];

    public function translations(): HasMany
    {
        return $this->hasMany(HomeStatTranslation::class);
    }
}
