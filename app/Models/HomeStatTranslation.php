<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeStatTranslation extends Model
{
    protected $fillable = ['locale', 'label'];

    public function stat(): BelongsTo
    {
        return $this->belongsTo(HomeStat::class, 'home_stat_id');
    }
}
