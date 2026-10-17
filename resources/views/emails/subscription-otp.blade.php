<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Votre code de validation CIOMAG Premium</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
        }
        .container {
            max-width: 560px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #020617;
            padding: 28px 32px;
            text-align: center;
            border-bottom: 3px solid #f59e0b;
        }
        .logo {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: -1px;
            color: #ffffff;
            text-transform: uppercase;
        }
        .logo span {
            color: #b91c1c;
        }
        .badge {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 10px;
            background: #f59e0b;
            color: #020617;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 4px;
        }
        .body {
            padding: 32px 32px 24px 32px;
        }
        h1 {
            font-size: 20px;
            font-weight: 800;
            margin-top: 0;
            margin-bottom: 12px;
            color: #020617;
        }
        p {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            margin-top: 0;
            margin-bottom: 16px;
        }
        .otp-box {
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 24px 16px;
            text-align: center;
            margin: 24px 0;
        }
        .otp-code {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 34px;
            font-weight: 900;
            letter-spacing: 10px;
            color: #b91c1c;
            display: inline-block;
            padding-left: 10px;
        }
        .otp-sub {
            margin-top: 10px;
            font-size: 12px;
            color: #64748b;
        }
        .footer {
            background: #f1f5f9;
            padding: 20px 32px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">CIO<span>MAG</span></div>
            <div class="badge">Club CIOMAG Premium</div>
        </div>
        <div class="body">
            <h1>Bonjour {{ $displayName }},</h1>
            <p>
                Vous avez initié la souscription à l'offre <strong>{{ $planName ?? 'CIOMAG Premium' }}</strong>.
                Pour valider votre adresse email et finaliser la création de votre compte abonné, veuillez utiliser le code à 6 chiffres ci-dessous :
            </p>

            <div class="otp-box">
                <div class="otp-code">{{ $code }}</div>
                <div class="otp-sub">Code de sécurité valable pendant 15 minutes</div>
            </div>

            <p style="font-size: 13px; color: #64748b;">
                Ce code est strictement confidentiel. Ne le communiquez à personne. Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email en toute sécurité.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} CIOMAG Media — Le Magazine des Dirigeants IT & de la Transformation Digitale.<br>
            Édition Panafricaine & Internationale.
        </div>
    </div>
</body>
</html>
