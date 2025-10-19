<?php

declare(strict_types=1);

return [
    'form' => [
        'title' => 'العنوان',
        'url' => 'الرابط (URL)',
        'linkable_type' => 'النوع',
        'linkable_id' => 'المعرف (ID)',
        'url_helper' => 'إذا كان الرابط داخل الصفحة الرئيسية للموقع العام فيجب أن يبدأ بـ (/) مثل: /about-us/. 
         وإذا كنت تريد ربطه بصفحة داخل مجلة، فيمكنك اختيار المجلة من الحقل المخصص، 
        وكتابة  الرابط (slug) مثل
        رابط المجلة ثم كلمة page ثم رابط الصفحة: journal-slug/page/about-site',
    ],
    'resource' => [
        'name' => [
            'label' => 'اسم القائمة',
        ],
        'locations' => [
            'label' => 'المواقع',
            'empty' => 'غير معين',
        ],
        'items' => [
            'label' => 'العناصر',
        ],
        'is_visible' => [
            'label' => 'الظهور',
            'visible' => 'مرئي',
            'hidden' => 'مخفي',
        ],
    ],
    'actions' => [
        'add' => [
            'label' => 'إضافة إلى القائمة',
        ],
        'indent' => 'إزاحة لليمين',
        'unindent' => 'إزاحة لليسار',
        'locations' => [
            'label' => 'المواقع',
            'heading' => 'إدارة المواقع',
            'description' => 'اختر أي قائمة تظهر في كل موقع.',
            'submit' => 'تحديث',
            'form' => [
                'location' => [
                    'label' => 'الموقع',
                ],
                'menu' => [
                    'label' => 'القائمة المعينة',
                ],
            ],
            'empty' => [
                'heading' => 'لا توجد مواقع مسجلة',
            ],
        ],
    ],
    'items' => [
        'expand' => 'توسيع',
        'collapse' => 'طي',
        'empty' => [
            'heading' => 'لا توجد عناصر في هذه القائمة.',
        ],
    ],
    'custom_link' => 'رابط مخصص',
    'custom_text' => 'نص مخصص',
    'open_in' => [
        'label' => 'الفتح في',
        'options' => [
            'self' => 'نفس التبويب',
            'blank' => 'تبويب جديد',
            'parent' => 'تبويب الأصل',
            'top' => 'أعلى تبويب',
        ],
    ],
    'notifications' => [
        'created' => [
            'title' => 'تم إنشاء الرابط',
        ],
        'locations' => [
            'title' => 'تم تحديث مواقع القوائم',
        ],
    ],
    'panel' => [
        'empty' => [
            'heading' => 'لا توجد عناصر',
            'description' => 'لا توجد عناصر في هذه القائمة.',
        ],
        'pagination' => [
            'previous' => 'السابق',
            'next' => 'التالي',
        ],
    ],
];
