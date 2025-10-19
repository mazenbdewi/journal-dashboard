<x-filament::widget>
    <x-filament::card>
        <div class="space-y-6">

            {{-- الحالة: موثق أو غير موثق --}}
            @if ($hasStudentForm)
            <div class="mb-4">
                <span
                    class="inline-flex items-center px-3 py-1 text-sm font-medium text-green-700 bg-green-100 rounded-full">
                    ✅ الحساب موثق - تم رفع الاستمارة
                </span>
            </div>
            @else
            <div class="mb-4">
                <span
                    class="inline-flex items-center px-3 py-1 text-sm font-medium text-red-700 bg-red-100 rounded-full">
                    ⚠️ لم يتم رفع استمارة الطالب بعد
                </span>
            </div>
            @endif

            {{-- عرض الاستمارة إن وُجدت --}}
            @php
            $studentFormPath = auth()->user()->custom_fields['student_form'] ?? null;
            @endphp

            @if ($studentFormPath && Storage::disk('public')->exists($studentFormPath))
            <div class="mb-4 p-4 bg-gray-50 rounded-lg border">
                <p class="font-semibold text-gray-700 mb-2">الاستمارة الحالية:</p>
                <div class="flex flex-col sm:flex-row gap-2">
                    <a href="{{ Storage::disk('public')->url($studentFormPath) }}" target="_blank"
                        class="inline-flex items-center px-3 py-2 text-sm text-primary-600 bg-primary-50 hover:bg-primary-100 rounded">
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        عرض الاستمارة
                    </a>

                    <button wire:click="downloadForm"
                        class="inline-flex items-center px-3 py-2 text-sm text-green-600 bg-green-50 hover:bg-green-100 rounded">
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        تحميل الاستمارة
                    </button>

                    <button wire:click="deleteForm" wire:confirm="هل أنت متأكد من حذف الاستمارة؟"
                        class="inline-flex items-center px-3 py-2 text-sm text-red-600 bg-red-50 hover:bg-red-100 rounded">
                        🗑️ حذف الاستمارة
                    </button>
                </div>
            </div>
            @endif

            {{-- فورم الرفع --}}
            <div class="border-t pt-4">
                <form wire:submit.prevent="submit">
                    <div class="space-y-4">
                        <div>
                            <label for="studentForm" class="block text-sm font-medium text-gray-700 mb-2">
                                رفع استمارة الطالب (PDF فقط - الحد الأقصى 5MB)
                            </label>
                            <input type="file" wire:model="studentForm" id="studentForm" accept=".pdf" class="block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4 
                                          file:rounded-full file:border-0 
                                          file:text-sm file:font-semibold 
                                          file:bg-primary-50 file:text-primary-700 
                                          hover:file:bg-primary-100
                                          border border-gray-300 rounded-lg p-2">

                            @error('studentForm')
                            <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror

                            @if ($studentForm)
                            <p class="text-sm text-green-600 mt-1">
                                تم اختيار الملف: {{ $studentForm->getClientOriginalName() }}
                            </p>
                            @endif
                        </div>

                        <x-filament::button type="submit" class="w-full justify-center" wire:loading.attr="disabled"
                            wire:target="studentForm,submit">
                            <span wire:loading.remove wire:target="studentForm,submit">
                                {{ $hasStudentForm ? 'تحديث الاستمارة' : 'رفع الاستمارة' }}
                            </span>
                            <span wire:loading wire:target="studentForm,submit">
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                جاري الرفع...
                            </span>
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>