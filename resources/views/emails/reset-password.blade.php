<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reset Password - إعادة تعيين كلمة المرور</title>
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
        background: linear-gradient(135deg, #dc2626, #ef4444);
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
        background: #fef2f2;
        border-bottom: 3px solid #dc2626;
    }

    /* القسم الإنجليزي */
    .english-section {
        direction: ltr;
        text-align: left;
        padding: 40px;
        background: #ffffff;
        border-bottom: 3px solid #ef4444;
    }

    .section-title {
        color: #dc2626;
        margin-bottom: 25px;
        font-size: 24px;
        font-weight: 600;
        text-align: center;
    }

    .greeting {
        font-size: 18px;
        margin-bottom: 20px;
        color: #dc2626;
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
        border-right: 4px solid #dc2626;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .details.english {
        border-right: none;
        border-left: 4px solid #ef4444;
    }

    .details h3 {
        color: #dc2626;
        margin-bottom: 20px;
        text-align: center;
        font-size: 20px;
    }

    .button-container {
        text-align: center;
        margin: 30px 0;
    }

    .button {
        display: inline-block;
        padding: 14px 32px;
        background: #dc2626;
        color: white !important;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .button:hover {
        background: #b91c1c;
    }

    .contact {
        text-align: center;
        margin: 20px 0;
        color: #718096;
        font-size: 14px;
    }

    .warning {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 6px;
        padding: 15px;
        margin: 20px 0;
        text-align: center;
        color: #92400e;
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
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Reset Password</h1>
            <div class="subtitle">إعادة تعيين كلمة المرور</div>
        </div>

        <div class="content">
            <!-- القسم العربي بالكامل -->
            <div class="arabic-section">
                <h2 class="section-title">إعادة تعيين كلمة المرور</h2>

                <div class="greeting">مرحباً {{ $user->name }},</div>

                <div class="message">
                    تلقينا طلباً لإعادة تعيين كلمة المرور لحسابك. يرجى النقر على الزر أدناه لتعيين كلمة مرور جديدة.
                </div>

                <div class="details">
                    <h3>تفاصيل الطلب</h3>
                    <div style="text-align: center; margin: 20px 0;">
                        <strong>اسم المستخدم:</strong> {{ $user->name }}<br>
                        <strong>البريد الإلكتروني:</strong> {{ $user->email }}<br>
                        <strong>وقت الطلب:</strong> {{ now()->format('Y-m-d H:i') }}
                    </div>
                </div>

                <div class="warning">
                    <strong>ملاحظة:</strong> سينتهي صلاحية رابط إعادة التعيين خلال {{ $count }} دقيقة.
                </div>

                <div class="button-container">
                    <a href="{{ $resetUrl }}" class="button">إعادة تعيين كلمة المرور</a>
                </div>

                <div class="message">
                    إذا لم تطلب إعادة تعيين كلمة المرور، فلا داعي لاتخاذ أي إجراء. يمكنك تجاهل هذه الرسالة.
                </div>

                <div class="contact">
                    إذا واجهتك أي مشكلة، يرجى التواصل مع فريق الدعم.
                </div>
            </div>

            <!-- القسم الإنجليزي بالكامل -->
            <div class="english-section">
                <h2 class="section-title">Reset Password</h2>

                <div class="greeting">Hello {{ $user->name }},</div>

                <div class="message">
                    We received a request to reset your password. Please click the button below to set a new password.
                </div>

                <div class="details english">
                    <h3>Request Details</h3>
                    <div style="text-align: center; margin: 20px 0;">
                        <strong>Username:</strong> {{ $user->name }}<br>
                        <strong>Email:</strong> {{ $user->email }}<br>
                        <strong>Request Time:</strong> {{ now()->format('Y-m-d H:i') }}
                    </div>
                </div>

                <div class="warning">
                    <strong>Note:</strong> This reset link will expire in {{ $count }} minutes.
                </div>

                <div class="button-container">
                    <a href="{{ $resetUrl }}" class="button">Reset Password</a>
                </div>

                <div class="message">
                    If you did not request a password reset, no further action is required. You can safely ignore this
                    email.
                </div>

                <div class="contact">
                    If you encounter any issues, please contact our support team.
                </div>
            </div>

            <div class="footer">
                <p>شكراً لاستخدامك منصتنا</p>
                <p>Thank you for using our platform</p>
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