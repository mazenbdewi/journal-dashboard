<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Rules\AcademicEmail;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as BaseRegister;
use Monarobase\CountryList\CountryListFacade as Countries;
use Spatie\Permission\Models\Role;

class Register extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        TextInput::make('name_en')
                            ->label(__('user.form.name_en'))
                            ->required()
                            ->maxLength(255),
                        $this->getEmailFormComponent(),

                        Grid::make(2)
                            ->schema([
                                Select::make('country_code')
                                    ->label('رمز الدولة')
                                    ->options([
                                        '+963' => 'سوريا (+963)',
                                        '+966' => 'السعودية (+966)',
                                        '+971' => 'الإمارات (+971)',
                                        '+962' => 'الأردن (+962)',
                                        '+965' => 'الكويت (+965)',
                                        '+974' => 'قطر (+974)',
                                        '+968' => 'عمان (+968)',
                                        '+973' => 'البحرين (+973)',
                                        '+20' => 'مصر (+20)',
                                        '+961' => 'لبنان (+961)',
                                    ])
                                    ->default('+963')
                                    ->searchable()
                                    ->required(),

                                TextInput::make('phone')
                                    ->label('رقم الهاتف')
                                    ->required()
                                    ->tel()
                                    ->maxLength(15)
                                    ->unique(User::class, 'phone')
                                    ->rules([
                                        'regex:/^[0-9]{7,15}$/',
                                    ])
                                    ->placeholder('9XXXXXXXX')
                                    ->validationMessages([
                                        'regex' => 'يرجى إدخال رقم هاتف صحيح (7-15 رقم بدون مسافات أو رموز).',
                                    ]),
                            ]),

                        Select::make('country')
                            ->label(__('user.form.country'))
                            ->options(Countries::getList(app()->getLocale()))
                            ->searchable()
                            ->required(),

                        Select::make('nationality')
                            ->label(__('user.form.nationality'))
                            ->options(Countries::getList(app()->getLocale()))
                            ->searchable()
                            ->required(),

                        Select::make('user_type')
                            ->label('نوع المستخدم')
                            ->required()
                            ->options([
                                'master' => 'طالب ماجستير',
                                'researcher' => 'باحث',
                                'doctor' => 'دكتور',
                                'faculty' => 'عضو هيئة تدريسية',
                            ])
                            ->searchable(),

                        // FileUpload::make('student_form')
                        //     ->label('استمارة الطالب (PDF)')
                        //     ->required()
                        //     ->directory('student_forms')
                        //     ->acceptedFileTypes(['application/pdf'])
                        //     ->preserveFilenames()
                        //     ->maxSize(2048),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function createUser(array $data): User
    {
        // إنشاء المستخدم مع جميع الحقول المطلوبة
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'country' => $data['country'],
            'nationality' => $data['nationality'],
            'password' => bcrypt($data['password']),
            'student_form' => $data['student_form'],
            'user_type' => $data['user_type'],

        ]);

        // إسناد دور researcher للمستخدم الجديد
        $researcherRole = Role::where('name', 'researcher')->first();

        if ($researcherRole) {
            $user->assignRole($researcherRole);
        }

        return $user;
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('البريد الإلكتروني الأكاديمي')
            ->email()
            ->required()
            ->maxLength(255)
            ->unique(User::class, 'email')
            ->rules([new AcademicEmail])
            ->autocomplete('email')
            ->autofocus();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('كلمة المرور')
            ->password()
            ->required()
            ->rules(['min:8'])
            ->autocomplete('new-password');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('password_confirmation')
            ->label('تأكيد كلمة المرور')
            ->password()
            ->required()
            ->same('password')
            ->autocomplete('new-password');
    }
}
