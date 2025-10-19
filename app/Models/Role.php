<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->model_type = 'App\Models\User'; // تعيين قيمة افتراضية
        });
    }
}
