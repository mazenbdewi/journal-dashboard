<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $decision === 'accepted' ? 'قبول طلب المراجعة' : 'رفض طلب المراجعة' }}</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: #333;
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
    }

    .header {
        background: #3b82f6;
        color: white;
        padding: 20px;
        text-align: center;
        border-radius: 10px 10px 0 0;
    }

    .content {
        background: #f8fafc;
        padding: 20px;
        border-radius: 0 0 10px 10px;
    }

    .arabic-section {
        direction: rtl;
        text-align: right;
        margin-bottom: 30px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 20px;
    }

    .english-section {
        direction: ltr;
        text-align: left;
    }

    .info-box {
        background: white;
        padding: 15px;
        border-radius: 5px;
        margin: 15px 0;
        border-right: 4px solid #3b82f6;
    }

    .button {
        display: inline-block;
        padding: 12px 24px;
        background: #3b82f6;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        margin: 10px 0;
    }

    .footer {
        text-align: center;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 14px;
    }
    </style>
</head>

<body>
    <div class="header">
        <h1>
            {{ $decision === 'accepted' ? '✅ قبول طلب المراجعة' : '❌ رفض طلب المراجعة' }}
        </h1>
    </div>

    <div class="content">
        <!-- القسم العربي -->
        <div class="arabic-section">
            <h2>الإشعار باللغة العربية</h2>

            <div class="info-box">
                <h3>تفاصيل القرار:</h3>
                <p>
                    @if($decision === 'accepted')
                    🎉 <strong>تم قبول طلب المراجعة</strong>
                    @else
                    ⚠️ <strong>تم رفض طلب المراجعة</strong>
                    @endif
                </p>

                <p><strong>اسم المحكم:</strong> {{ $reviewerName }}</p>
                <p><strong>عنوان المقالة:</strong> {{ $articleTitle }}</p>
                <p><strong>تاريخ القرار:</strong> {{ now()->format('Y-m-d H:i') }}</p>
            </div>

            <p>
                @if($decision === 'accepted')
                قام المحكم بقبول طلب مراجعة المقالة وسيبدأ عملية المراجعة خلال الفترة المحددة.
                @else
                قام المحكم برفض طلب مراجعة المقالة. يرجى تعيين محكم آخر للمقالة.
                @endif
            </p>

            <a href="{{ url('/adminpanel/review-assignments/' . $reviewAssignment->id) }}" class="button">
                عرض تفاصيل المهمة
            </a>
        </div>

        <!-- القسم الإنجليزي -->
        <div class="english-section">
            <h2>Notification in English</h2>

            <div class="info-box">
                <h3>Decision Details:</h3>
                <p>
                    @if($decision === 'accepted')
                    🎉 <strong>Review Assignment Accepted</strong>
                    @else
                    ⚠️ <strong>Review Assignment Declined</strong>
                    @endif
                </p>

                <p><strong>Reviewer Name:</strong> {{ $reviewerName }}</p>
                <p><strong>Article Title:</strong> {{ $articleTitle }}</p>
                <p><strong>Decision Date:</strong> {{ now()->format('Y-m-d H:i') }}</p>
            </div>

            <p>
                @if($decision === 'accepted')
                The reviewer has accepted the review assignment and will begin the review process within the specified
                timeframe.
                @else
                The reviewer has declined the review assignment. Please assign another reviewer for the article.
                @endif
            </p>

            <a href="{{ url('/adminpanel/review-assignments/' . $reviewAssignment->id) }}" class="button">
                View Assignment Details
            </a>
        </div>
    </div>

    <div class="footer">
        <p>هذا إشعار تلقائي - This is an automated notification</p>
        <p>© {{ date('Y') }} نظام إدارة المجلات العلمية - Journal Management System</p>
    </div>
</body>

</html>