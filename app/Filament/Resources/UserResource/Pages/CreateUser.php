<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function authorizeAccess(): void
    {
        // السماح بالوصول فقط للمشرف الأعلى
        if (! Auth::user()->hasRole('super_admin')) {
            throw new AuthorizationException('ليس لديك صلاحية الوصول إلى هذه الصفحة.');
        }
    }
}
