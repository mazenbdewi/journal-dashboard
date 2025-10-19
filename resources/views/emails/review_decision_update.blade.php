<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Review Decision - قرار المراجعة</title>
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

    .arabic-section,
    .english-section {
        padding: 40px;
    }

    .arabic-section {
        direction: rtl;
        text-align: right;
        background: #f8fafc;
        border-bottom: 3px solid #4f46e5;
    }

    .english-section {
        direction: ltr;
        text-align: left;
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

    .language-notice {
        text-align: center;
        font-size: 12px;
        color: #a0aec0;
        margin-top: 15px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Review Decision</h1>
            <div class="subtitle">قرار المراجعة</div>
        </div>

        <div class="content">

            <!-- Arabic Section -->
            <div class="arabic-section">
                <h2 class="section-title">قرار المراجعة</h2>
                <div class="greeting">مرحباً {{ $name }},</div>
                <div class="message">
                    تم {{ $actionTextAr }} للمقال التالي. فيما يلي تفاصيل القرار:
                </div>
                <div class="details">
                    <h3>تفاصيل القرار</h3>
                    <div class="detail-item">
                        <span class="detail-label">عنوان المقال:</span>
                        <span class="detail-value">{{ $articleTitle }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">المؤلف الرئيسي:</span>
                        <span class="detail-value">{{ $mainAuthor }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">القرار:</span>
                        <span class="detail-value">{{ $decisionTextAr }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">المحكم:</span>
                        <span class="detail-value">{{ $reviewerName }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">تاريخ المراجعة:</span>
                        <span class="detail-value">{{ $reviewDate }}</span>
                    </div>
                </div>

                <div class="message">
                    يمكنك الاطلاع على تفاصيل المراجعة من خلال الرابط أدناه:
                </div>

                <div class="button-container">
                    <a href="{{ $url }}" class="button">عرض المراجعة</a>
                </div>
            </div>

            <!-- English Section -->
            <div class="english-section">
                <h2 class="section-title">Review Decision</h2>
                <div class="greeting">Hello {{ $name }},</div>
                <div class="message">
                    A review decision has been {{ $actionTextEn }} for the following article. Details are below:
                </div>
                <div class="details english">
                    <h3>Decision Details</h3>
                    <div class="detail-item">
                        <span class="detail-label">Article Title:</span>
                        <span class="detail-value">{{ $articleTitle }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Main Author:</span>
                        <span class="detail-value">{{ $mainAuthor }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Decision:</span>
                        <span class="detail-value">{{ $decisionTextEn }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Reviewer:</span>
                        <span class="detail-value">{{ $reviewerName }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Review Date:</span>
                        <span class="detail-value">{{ $reviewDate }}</span>
                    </div>
                </div>

                <div class="message">
                    You can view the review using the link below:
                </div>

                <div class="button-container">
                    <a href="{{ $url }}" class="button">View Review</a>
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