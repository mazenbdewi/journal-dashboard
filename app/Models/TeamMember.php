<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamMember extends Model
{
    protected $fillable = [
        'image', 'twitter', 'facebook', 'instagram', 'linkedin',
        'active', 'order',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(TeamMemberTranslation::class);
    }
}
