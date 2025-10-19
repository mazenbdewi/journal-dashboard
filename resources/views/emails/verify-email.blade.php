<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Email Verification - تأكيد البريد الإلكتروني</title>
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
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Email Verification</h1>
            <div class="subtitle">تأكيد البريد الإلكتروني</div>
        </div>

        <div class="content">
            <!-- القسم العربي بالكامل -->
            <div class="arabic-section">
                <h2 class="section-title">تأكيد البريد الإلكتروني</h2>

                <div class="greeting">مرحباً {{ $user->name }},</div>

                <div class="message">
                    شكراً لتسجيلك في منصتنا. يرجى النقر على الزر أدناه لتأكيد عنوان بريدك الإلكتروني.
                </div>

                <div class="details">
                    <h3>تفاصيل الحساب</h3>
                    <div style="text-align: center; margin: 20px 0;">
                        <strong>اسم المستخدم:</strong> {{ $user->name }}<br>
                        <strong>البريد الإلكتروني:</strong> {{ $user->email }}<br>
                        <strong>تاريخ التسجيل:</strong> {{ $user->created_at->format('Y-m-d') }}
                    </div>
                </div>

                <div class="message">
                    إذا لم تقم بإنشاء هذا الحساب، يمكنك تجاهل هذه الرسالة.
                </div>

                <div class="button-container">
                    <a href="{{ $verificationUrl }}" class="button">تأكيد البريد الإلكتروني</a>
                </div>

                <div class="contact">
                    إذا واجهتك أي مشكلة، يرجى التواصل مع فريق الدعم.
                </div>
            </div>

            <!-- القسم الإنجليزي بالكامل -->
            <div class="english-section">
                <h2 class="section-title">Email Verification</h2>

                <div class="greeting">Hello {{ $user->name }},</div>

                <div class="message">
                    Thank you for registering on our platform. Please click the button below to verify your email
                    address.
                </div>

                <div class="details english">
                    <h3>Account Details</h3>
                    <div style="text-align: center; margin: 20px 0;">
                        <strong>Username:</strong> {{ $user->name }}<br>
                        <strong>Email:</strong> {{ $user->email }}<br>
                        <strong>Registration Date:</strong> {{ $user->created_at->format('Y-m-d') }}
                    </div>
                </div>

                <div class="message">
                    If you did not create this account, you can safely ignore this email.
                </div>

                <div class="button-container">
                    <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
                </div>

                <div class="contact">
                    If you encounter any issues, please contact our support team.
                </div>
            </div>

            <div class="footer">
                <p>شكراً لانضمامك إلى منصتنا</p>
                <p>Thank you for joining our platform</p>
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