<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomePartnerTranslation extends Model
{
    protected $fillable = ['locale', 'title'];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(HomePartner::class, 'home_partner_id');
    }
}
