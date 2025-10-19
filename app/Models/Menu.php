<?php

namespace App\Models;

use Datlechin\FilamentMenuBuilder\Models\Menu as BaseMenu;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu extends BaseMenu
{
    protected $table = 'menus';

    protected $fillable = [
        'name',
        'is_visible',
        'journal_id',
        'language',

    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
