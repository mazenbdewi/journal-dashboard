<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads; // أضف هذا

class UploadStudentForm extends Widget
{
    use WithFileUploads; // أضف هذا

    protected static string $view = 'filament.widgets.upload-student-form';

    public $studentForm;

    public bool $hasStudentForm = false;

    public $user;

    public $isUploading = false;

    public function mount(): void
    {
        $this->user = Auth::user();
        $this->hasStudentForm = ! empty($this->user->custom_fields['student_form'] ?? null);
    }

    public function submit()
    {
        $this->isUploading = true;

        $this->validate([
            'studentForm' => 'required|file|mimes:pdf|max:5120', // 5MB كحد أقصى
        ]);

        try {
            // حفظ الملف
            $fileName = 'student_form_'.$this->user->id.'_'.time().'.'.$this->studentForm->getClientOriginalExtension();
            $path = $this->studentForm->storeAs('student_forms', $fileName, 'public');

            $user = Auth::user();

            // حذف الملف القديم إذا كان موجوداً
            if ($user->custom_fields && isset($user->custom_fields['student_form'])) {
                Storage::disk('public')->delete($user->custom_fields['student_form']);
            }

            // تحديث البيانات
            $customFields = $user->custom_fields ?? [];
            $customFields['student_form'] = $path;

            $user->update([
                'custom_fields' => $customFields,
            ]);

            $this->hasStudentForm = true;
            $this->studentForm = null;

            \Filament\Notifications\Notification::make()
                ->title('تم تحميل الاستمارة بنجاح ✅')
                ->body('تم رفع استمارة الطالب بنجاح')
                ->success()
                ->send();

        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('خطأ في الرفع ❌')
                ->body('حدث خطأ أثناء رفع الملف: '.$e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->isUploading = false;
        }
    }

    public function deleteForm()
    {
        $user = Auth::user();

        if ($user->custom_fields && isset($user->custom_fields['student_form'])) {
            Storage::disk('public')->delete($user->custom_fields['student_form']);

            $customFields = $user->custom_fields;
            unset($customFields['student_form']);

            $user->update([
                'custom_fields' => $customFields,
            ]);
        }

        $this->hasStudentForm = false;

        \Filament\Notifications\Notification::make()
            ->title('تم حذف الاستمارة بنجاح ✅')
            ->success()
            ->send();
    }

    public function downloadForm()
    {
        $user = Auth::user();
        $filePath = $user->custom_fields['student_form'] ?? null;

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->download($filePath);
        }

        \Filament\Notifications\Notification::make()
            ->title('خطأ ❌')
            ->body('الملف غير موجود')
            ->danger()
            ->send();
    }

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->hasRole('researcher');
    }
}
