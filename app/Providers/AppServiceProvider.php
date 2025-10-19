<?php

namespace App\Providers;

use App\Models\Menu as CustomMenu;
use App\Notifications\CustomResetPassword;
use App\Notifications\CustomVerifyEmail;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Datlechin\FilamentMenuBuilder\Models\Menu as BaseMenu;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        app()->bind(BaseMenu::class, CustomMenu::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->locales(['ar', 'en']);
        });

        // 🔒 تحقق من وجود جدول roles قبل إنشاء الأدوار
        // if (Schema::hasTable('roles')) {
        //     $roles = ['reviewer', 'user', 'researcher'];

        //     foreach ($roles as $role) {
        //         Role::firstOrCreate([
        //             'name' => $role,
        //             'guard_name' => 'web',
        //         ]);
        //     }
        // }

        // تخصيص رسائل البريد الإلكتروني
        VerifyEmail::toMailUsing(function ($notifiable, $verificationUrl) {
            return (new CustomVerifyEmail)->toMail($notifiable);
        });

        ResetPassword::toMailUsing(function ($notifiable, $token) {
            return (new CustomResetPassword($token, $notifiable))->toMail($notifiable);
        });

    }
}
