<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomePartner extends Model
{
    protected $fillable = ['active', 'link', 'image', 'order'];

    public function translations(): HasMany
    {
        return $this->hasMany(HomePartnerTranslation::class);
    }
}
