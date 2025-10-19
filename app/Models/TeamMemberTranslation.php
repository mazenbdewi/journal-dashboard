<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMemberTranslation extends Model
{
    protected $fillable = [
        'locale', 'name', 'position', 'bio',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'team_member_id');
    }
}
