<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ $actionText }}</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, 'Noto Sans Arabic', Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: #333;
        background-color: #f5f5f5;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 700px;
        margin: 0 auto;
        background-color: #ffffff;
    }

    .header {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: white;
        padding: 30px 20px;
        text-align: center;
    }

    .header h1 {
        font-size: 28px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .content {
        padding: 0;
    }

    .arabic-section,
    .english-section {
        padding: 40px;
    }

    .arabic-section {
        direction: rtl;
        text-align: right;
        background: #f0f9ff;
        border-bottom: 3px solid #0ea5e9;
    }

    .english-section {
        direction: ltr;
        text-align: left;
        background: #ffffff;
        border-bottom: 3px solid #0284c7;
    }

    .section-title {
        color: #0ea5e9;
        margin-bottom: 25px;
        font-size: 24px;
        font-weight: 600;
        text-align: center;
    }

    .info-box {
        background: white;
        padding: 25px;
        border-radius: 8px;
        margin: 25px 0;
        border-right: 4px solid #0ea5e9;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .english .info-box {
        border-right: none;
        border-left: 4px solid #0284c7;
    }

    .info-box p {
        margin: 10px 0;
        font-size: 16px;
        color: #444;
    }

    .info-box strong {
        display: inline-block;
        min-width: 120px;
        color: #1e293b;
    }

    .button-container {
        text-align: center;
        margin: 30px 0;
    }

    .button {
        display: inline-block;
        padding: 14px 32px;
        background: #0ea5e9;
        color: white !important;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 16px;
        transition: background 0.3s ease;
    }

    .button:hover {
        background: #0284c7;
    }

    .footer {
        text-align: center;
        padding: 30px;
        background: #1f2937;
        color: white;
        font-size: 14px;
    }

    .language-notice {
        text-align: center;
        font-size: 12px;
        color: #64748b;
        margin-top: 15px;
        padding: 10px;
        background: #f1f5f9;
    }

    @media (max-width: 600px) {

        .arabic-section,
        .english-section {
            padding: 25px;
        }

        .info-box p {
            display: block;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>{{ $actionText }}</h1>
            <div>{{ $actionText == 'Review Updated' ? 'تم تحديث المراجعة' : 'تم تغيير حالة المراجعة' }}</div>
        </div>

        <div class="content">
            <!-- القسم العربي -->
            <div class="arabic-section">
                <h2 class="section-title">تحديث حالة المراجعة</h2>
                <p>مرحباً {{ $name }},</p>
                <p>تم {{ $actionAr }} لمقالك أو مراجعتك. التفاصيل كما يلي:</p>

                <div class="info-box">
                    <p><strong>عنوان المقالة:</strong> {{ $articleTitle }}</p>
                    <p><strong>الحالة:</strong> {{ $statusText }}</p>
                    <p><strong>التاريخ:</strong> {{ $timestamp }}</p>
                </div>

                <p>يمكنك عرض المراجعة من خلال الرابط التالي:</p>

                <div class="button-container">
                    <a href="{{ url('/admin/reviews/' . $reviewId . '/edit') }}" class="button">عرض المراجعة</a>
                </div>
            </div>

            <!-- English section -->
            <div class="english-section english">
                <h2 class="section-title">Review Status Update</h2>
                <p>Hello {{ $name }},</p>
                <p>The review status has been {{ $actionEn }}. Here are the details:</p>

                <div class="info-box">
                    <p><strong>Article Title:</strong> {{ $articleTitle }}</p>
                    <p><strong>Status:</strong> {{ $statusTextEn }}</p>
                    <p><strong>Date:</strong> {{ $timestamp }}</p>
                </div>

                <p>You can view the review by clicking the button below:</p>

                <div class="button-container">
                    <a href="{{ url('/admin/reviews/' . $reviewId . '/edit') }}" class="button">View Review</a>
                </div>
            </div>

            <div class="footer">
                <p>شكراً لمساهمتك في تحسين جودة المحتوى العلمي</p>
                <p>Thank you for contributing to the quality of scientific content</p>
                <p>{{ config('app.name') }}</p>
            </div>

            <div class="language-notice">
                <div style="direction: rtl;">هذه الرسالة معروضة بلغتين للراحة. يمكنك قراءة القسم المفضل لديك.</div>
                <div style="direction: ltr;">This message is presented in two languages for your convenience.</div>
            </div>
        </div>
    </div>
</body>

</html>