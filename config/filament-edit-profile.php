<?php

return [
    'avatar_column' => 'avatar_url',
    'disk' => env('FILESYSTEM_DISK', 'public'),
    'visibility' => 'public', // or replace by filesystem disk visibility with fallback value
    'show_custom_fields' => false,

    'custom_fields' => [

        'phone' => [
            'type' => 'text',
            'label' => 'رقم الهاتف',
            'placeholder' => 'مثال: 0999999999',
            'required' => true,
            'rules' => ['string', 'max:255'],
            'column_span' => 'full',
        ],

        'address' => [
            'type' => 'text',
            'label' => 'العنوان',
            'required' => true,
            'rules' => ['string', 'max:255'],
            'column_span' => 'full',
        ],

        'country' => [
            'type' => 'text', // لاحقًا يمكن تغييره إلى select بقائمة الدول
            'label' => 'البلد',
            'required' => false,
            'rules' => ['string', 'max:255'],
            'column_span' => 'full',
        ],

        'nationality' => [
            'type' => 'text', // أو select
            'label' => 'الجنسية',
            'required' => false,
            'rules' => ['string', 'max:255'],
            'column_span' => 'full',
        ],

        'orcid' => [
            'type' => 'text',
            'label' => 'ORCID',
            'placeholder' => 'https://orcid.org/...',
            'required' => false,
            'rules' => ['nullable', 'string', 'max:255'],
            'column_span' => 'full',
        ],

        'affiliation' => [
            'type' => 'text',
            'label' => 'جهة الانتساب',
            'required' => false,
            'rules' => ['nullable', 'string', 'max:255'],
            'column_span' => 'full',
        ],

        'bio' => [
            'type' => 'textarea',
            'label' => 'نبذة تعريفية',
            'rows' => 4,
            'required' => false,
            'rules' => ['nullable', 'string'],
            'column_span' => 'full',
        ],
        'user_type' => [
            'type' => 'select',
            'label' => 'نوع المستخدم',
            'required' => true,
            'options' => [
                'master' => 'طالب ماجستير',
                'researcher' => 'باحث',
                'doctor' => 'دكتور',
                'faculty' => 'عضو هيئة تدريسية',
            ],
            'searchable' => true,
            'column_span' => 'full',
        ],
    ],

];
