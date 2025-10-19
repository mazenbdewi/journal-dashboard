<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AcademicEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = substr(strrchr($value, '@'), 1);
        $keywords = ['edu', 'ac', 'univ', 'college', 'school', 'institute'];

        $isAcademic = collect($keywords)->contains(function ($keyword) use ($domain) {
            return str_contains($domain, $keyword);
        });

        if (! $isAcademic) {
            $fail('يجب أن يكون البريد الإلكتروني يحتوي على نطاق أكاديمي مثل: edu أو univ أو ac');
        }
    }
}
