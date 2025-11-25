<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Register;
use App\Filament\Resources\MenuResource;
use App\Filament\Widgets\ArticlesStatusChart;
use App\Filament\Widgets\ArticleStatsWidget;
use App\Filament\Widgets\JournalStatsWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Datlechin\FilamentMenuBuilder\FilamentMenuBuilderPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;
use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticationPlugin;

class AdminpanelPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('adminpanel')
            ->path('adminpanel')
            ->login()
            ->topNavigation(true)
            ->registration(Register::class)
            ->passwordReset()
            ->emailVerification()
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn () => auth()->user()->name)
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle'),
            ])
            ->colors([
                'primary' => '#0070b7', // Change primary color to yellow
                'gray' => Color::Gray,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                JournalStatsWidget::class,
                ArticleStatsWidget::class,
                ArticlesStatusChart::class,
                \App\Filament\Widgets\UploadStudentForm::class,

            ])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                FilamentEditProfilePlugin::make()
                    ->shouldRegisterNavigation(false)

                    ->shouldShowEmailForm(false)
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars', // image will be stored in 'storage/app/public/avatars
                        rules: 'mimes:jpeg,png|max:1024' // only accept jpeg and png files with a maximum size of 1MB

                    ),

                FilamentMenuBuilderPlugin::make()
                    ->usingResource(MenuResource::class)
                    ->navigationLabel(fn () => __('menu.navigation_label'))
                    ->navigationGroup(fn () => __('menu.group'))
                    ->navigationIcon('heroicon-o-bars-3')
                    ->navigationSort(7)                              // ← ترتيب الظهور
                    ->navigationCountBadge(false),
                TwoFactorAuthenticationPlugin::make()
                    ->enableTwoFactorAuthentication() // Enable Google 2FA
                    // ->enablePasskeyAuthentication() // Enable Passkey
                    ->addTwoFactorMenuItem() // Add 2FA menu item
                    ->forceTwoFactorSetup()
                    ->disable(),

            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
