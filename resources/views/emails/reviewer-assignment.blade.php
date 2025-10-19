<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>New Review Assignment - تعيين مراجعة جديدة</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, 'Noto Sans Arabic', Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: #333;
        background-color: #f5f5f5;
    }

    .container {
        max-width: 700px;
        margin: 0 auto;
        background-color: #ffffff;
    }

    .header {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: white;
        padding: 30px 20px;
        text-align: center;
    }

    .header h1 {
        font-size: 28px;
        margin-bottom: 10px;
        font-weight: 600;
        direction: ltr;
    }

    .header .subtitle {
        font-size: 18px;
        opacity: 0.9;
    }

    .content {
        padding: 0;
    }

    /* القسم العربي */
    .arabic-section {
        direction: rtl;
        text-align: right;
        padding: 40px;
        background: #f8fafc;
        border-bottom: 3px solid #4f46e5;
    }

    /* القسم الإنجليزي */
    .english-section {
        direction: ltr;
        text-align: left;
        padding: 40px;
        background: #ffffff;
        border-bottom: 3px solid #7c3aed;
    }

    .section-title {
        color: #4f46e5;
        margin-bottom: 25px;
        font-size: 24px;
        font-weight: 600;
        text-align: center;
    }

    .greeting {
        font-size: 18px;
        margin-bottom: 20px;
        color: #4f46e5;
        font-weight: 600;
    }

    .message {
        margin-bottom: 25px;
        font-size: 16px;
        color: #555;
        line-height: 1.8;
    }

    .details {
        background: white;
        padding: 25px;
        border-radius: 8px;
        margin: 25px 0;
        border-right: 4px solid #4f46e5;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .details.english {
        border-right: none;
        border-left: 4px solid #7c3aed;
    }

    .details h3 {
        color: #4f46e5;
        margin-bottom: 20px;
        text-align: center;
        font-size: 20px;
    }

    .detail-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e2e8f0;
    }

    .detail-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .detail-label {
        font-weight: 600;
        color: #4a5568;
        min-width: 140px;
    }

    .detail-value {
        color: #2d3748;
        flex: 1;
    }

    .arabic-section .detail-label {
        text-align: right;
        margin-left: 20px;
    }

    .english-section .detail-label {
        text-align: left;
        margin-right: 20px;
    }

    .button-container {
        text-align: center;
        margin: 30px 0;
    }

    .button {
        display: inline-block;
        padding: 14px 32px;
        background: #4f46e5;
        color: white !important;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .button:hover {
        background: #4338ca;
    }

    .contact {
        text-align: center;
        margin: 20px 0;
        color: #718096;
        font-size: 14px;
    }

    .footer {
        text-align: center;
        padding: 30px;
        background: #1f2937;
        color: white;
    }

    .footer p {
        margin-bottom: 10px;
    }

    .language-notice {
        text-align: center;
        font-size: 12px;
        color: #a0aec0;
        margin-top: 15px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }

    @media (max-width: 600px) {

        .arabic-section,
        .english-section {
            padding: 25px;
        }

        .detail-item {
            flex-direction: column;
        }

        .detail-label {
            min-width: auto;
            margin-bottom: 5px;
        }

        .arabic-section .detail-label,
        .english-section .detail-label {
            margin-left: 0;
            margin-right: 0;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>New Review Assignment</h1>
            <div class="subtitle">تعيين مراجعة جديدة</div>
        </div>

        <div class="content">
            <!-- القسم العربي بالكامل -->
            <div class="arabic-section">
                <h2 class="section-title">تعيين مراجعة جديدة</h2>

                <div class="greeting">مرحباً {{ $reviewer->name }},</div>

                <div class="message">
                    تم تعيينك لمراجعة المقال التالي. يرجى الاطلاع على التفاصيل أدناه والمتابعة وفقاً للإرشادات.
                </div>

                <div class="details">
                    <h3>تفاصيل التعيين</h3>

                    <div class="detail-item">
                        <span class="detail-label">عنوان المقال:</span>
                        <span class="detail-value">{{ $articleTitleAr }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">ملخص المقال:</span>
                        <span class="detail-value">{{ $articleAbstractAr }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">رقم المقال:</span>
                        <span class="detail-value">#{{ $article->id }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">تاريخ التعيين:</span>
                        <span class="detail-value">{{ $formattedAssignedAt }}</span>

                    </div>

                    <div class="detail-item">
                        <span class="detail-label">موعد التسليم:</span>
                        <span class="detail-value">
                            @if($reviewAssignment->deadline)
                            {{ \Carbon\Carbon::parse($reviewAssignment->deadline)->format('Y-m-d') }}
                            @else
                            غير محدد
                            @endif
                        </span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">حالة المراجعة:</span>
                        <span class="detail-value">
                            @php
                            $statusLabels = [
                            'pending' => 'معلق',
                            'completed' => 'مكتمل',
                            'declined' => 'مرفوض',
                            ];
                            @endphp
                            {{ $statusLabels[$reviewAssignment->status] ?? $reviewAssignment->status }}
                        </span>
                    </div>
                </div>

                <div class="message">
                    يرجى تسجيل الدخول إلى نظام المجلة لبدء عملية المراجعة. يجب إكمال المراجعة قبل الموعد النهائي المحدد.
                </div>

                <div class="button-container">
                    <a href="{{ url('/') }}" class="button">الدخول إلى نظام المجلة</a>
                </div>

                <div class="contact">
                    إذا كانت لديك أي استفسارات أو تحتاج إلى مساعدة، فلا تتردد في التواصل معنا.
                </div>
            </div>

            <!-- القسم الإنجليزي بالكامل -->
            <div class="english-section">
                <h2 class="section-title">New Review Assignment</h2>

                <div class="greeting">Hello {{ $reviewer->name }},</div>

                <div class="message">
                    You have been assigned to review the following article. Please review the details below and proceed
                    according to the guidelines.
                </div>

                <div class="details english">
                    <h3>Assignment Details</h3>

                    <div class="detail-item">
                        <span class="detail-label">Article Title:</span>
                        <span class="detail-value">{{ $articleTitleEn }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Article Abstract:</span>
                        <span class="detail-value">{{ $articleAbstractEn }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Article ID:</span>
                        <span class="detail-value">#{{ $article->id }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Assignment Date:</span>
                        <span class="detail-value">{{ $formattedDeadline }}</span>

                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Submission Deadline:</span>
                        <span class="detail-value">
                            @if($reviewAssignment->deadline)
                            {{ \Carbon\Carbon::parse($reviewAssignment->deadline)->format('Y-m-d') }}
                            @else
                            Not specified
                            @endif
                        </span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Review Status:</span>
                        <span class="detail-value">
                            @php
                            $statusLabels = [
                            'pending' => 'Pending',
                            'completed' => 'Completed',
                            'declined' => 'Declined',
                            ];
                            @endphp
                            {{ $statusLabels[$reviewAssignment->status] ?? $reviewAssignment->status }}
                        </span>
                    </div>
                </div>

                <div class="message">
                    Please log in to the journal system to start the review process. The review must be completed before
                    the specified deadline.
                </div>

                <div class="button-container">
                    <a href="{{ url('/') }}" class="button">Access Journal System</a>
                </div>

                <div class="contact">
                    If you have any questions or need assistance, please do not hesitate to contact us.
                </div>
            </div>

            <div class="footer">
                <p>شكراً لمساهمتك في تحسين جودة المحتوى العلمي</p>
                <p>Thank you for contributing to improving the quality of scientific content</p>
                <p>{{ config('app.name') }}</p>
            </div>

            <div class="language-notice">
                <div style="direction: rtl; margin-bottom: 5px;">
                    هذه الرسالة معروضة بلغتين للراحة. يمكنك الاطلاع على القسم الذي تفضلونه.
                </div>
                <div style="direction: ltr;">
                    This message is displayed in two languages for convenience. You can refer to your preferred section.
                </div>
            </div>
        </div>
    </div>
</body>

</html>