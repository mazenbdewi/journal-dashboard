<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\CustomVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\Permission\Traits\HasRoles;
use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticatable;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasPasskeys, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'name_en',
        'email',
        'password',
        'avatar_url',
        'status',
        'student_form',
        'custom_fields',
        'phone',
        'country',
        'nationality',
        'address',
        'orcid ',
        'affiliation',
        'bio',
        'user_type',
        'email_verified_at',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'custom_fields' => 'array',
        ];
    }

    public function sendEmailVerificationNotification()
    {
        // $this->notify(new CustomVerifyEmail);
        $this->notify(new \App\Notifications\CustomVerifyEmail);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\CustomResetPassword($token, $this));
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // يمكنك تعديل الشرط حسب احتياجاتك
        // مثال: السماح فقط للمستخدمين الذين لديهم أدوار محددة
        return $this->hasRole(['super_admin', 'researcher', 'reviewer']) || $this->hasAnyRole(['super_admin', 'researcher', 'reviewer']);
    }

    public function authoredArticles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_authors')
            ->withPivot('is_main_author');
    }

    public function mainAuthoredArticles(): BelongsToMany
    {
        return $this->authoredArticles()->wherePivot('is_main_author', true);
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_authors')
            ->withPivot('is_main_author');
    }

    public function createdJournals(): HasMany
    {
        return $this->hasMany(Journal::class, 'created_by');
    }

    public function createdArticles(): HasMany
    {
        return $this->hasMany(Article::class, 'created_by');
    }

    public function reviewAssignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class, 'reviewer_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $avatarColumn = config('filament-edit-profile.avatar_column', 'avatar_url');

        return $this->avatar_url ? Storage::url("$this->avatar_url") : null;
    }

    // public function roles()
    // {
    //     return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id');
    // }

    // العلاقة مع المقالات التي أنشأها (One-to-Many)
    // use Illuminate\Validation\ValidationException;

    protected static function booted()
    {
        static::created(function ($user) {
            if (Schema::hasTable('roles') && Role::where('name', 'researcher')->exists()) {
                if (! $user->hasRole('researcher')) {
                    $user->assignRole('researcher');
                }
            }
        });

        static::deleting(function ($user) {
            // التحقق من المقالات المرتبطة كمؤلف
            if ($user->articles()->exists()) {
                \Filament\Notifications\Notification::make()
                    ->title('لا يمكن حذف المستخدم')
                    ->body('لديه مقالات مرتبطة كمؤلف ولا يمكن حذفه.')
                    ->danger()
                    ->send();

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'delete' => ['لا يمكن حذف المستخدم لأنه مرتبط بمقالات كمؤلف.'],
                ]);
            }

            // التحقق من المقالات المرتبطة كـ created_by
            if (\App\Models\Article::where('created_by', $user->id)->exists()) {
                \Filament\Notifications\Notification::make()
                    ->title('لا يمكن حذف المستخدم')
                    ->body('قام بإنشاء مقالات ولا يمكن حذفه.')
                    ->danger()
                    ->send();

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'delete' => ['لا يمكن حذف المستخدم لأنه قام بإنشاء مقالات.'],
                ]);
            }

            // منع حذف آخر مدير - التحقق قبل حذف العلاقات
            if ($user->hasRole('super_admin')) {
                // عد جميع المستخدمين الذين لديهم دور super_admin بما فيهم المستخدم الحالي
                $superAdminsCount = \App\Models\User::whereHas('roles', function ($query) {
                    $query->where('name', 'super_admin');
                })->count();

                if ($superAdminsCount <= 1) {
                    \Filament\Notifications\Notification::make()
                        ->title('لا يمكن حذف المدير الوحيد')
                        ->body('يجب أن يبقى على الأقل مدير واحد في النظام.')
                        ->danger()
                        ->send();

                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'delete' => ['لا يمكن حذف المدير الوحيد في النظام.'],
                    ]);
                }
            }
        });
    }

    public function notifications()
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->latest();
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }
}
