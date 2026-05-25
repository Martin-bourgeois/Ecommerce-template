<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nouveau message de contact</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 15px; border-bottom: 1px solid #dee2e6; }
        .content { padding: 20px; }
        .info { background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .info-item { margin: 8px 0; }
        .info-label { font-weight: bold; color: #495057; }
        .message-box { background-color: #ffffff; border: 1px solid #dee2e6; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .newsletter-notice { background-color: #f0f9ff; border-left: 4px solid #3b82f6; padding: 12px; margin-top: 20px; }
        .footer { text-align: center; font-size: 12px; color: #6c757d; margin-top: 20px; padding-top: 15px; border-top: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>

        <div class="content">
            <h2>Nouveau message de contact</h2>
            <p>Vous avez reçu un nouveau message de contact via le formulaire de votre site.</p>

            <div class="info">
                <div class="info-item">
                    <span class="info-label">Nom:</span> {{ $name }}
                </div>
                <div class="info-item">
                    <span class="info-label">Email:</span> <a href="mailto:{{ $email }}">{{ $email }}</a>
                </div>
                <div class="info-item">
                    <span class="info-label">Sujet:</span> {{ $subject }}
                </div>
            </div>

            <h3>Message:</h3>
            <div class="message-box">
                {!! nl2br(e($contactMessage)) !!}
            </div>

            @if($wantsNewsletter)
            <div class="newsletter-notice">
                <p style="margin: 0; color: #1f2937; font-size: 14px;">
                    ✓ <strong>Newsletter:</strong> Cette personne souhaite s'abonner à votre newsletter.
                </p>
            </div>
            @endif
        </div>

        <div class="footer">
            <p>Ce message a été envoyé depuis votre formulaire de contact.</p>
        </div>
    </div>
</body>
</html>
