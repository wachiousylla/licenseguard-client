<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Service temporairement indisponible</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #0b1220; color: #e5e9f0; padding: 24px;
        }
        .card {
            width: 100%; max-width: 460px; text-align: center;
            background: #131c2e; border: 1px solid rgba(255,255,255,.08);
            border-radius: 24px; padding: 40px 32px; box-shadow: 0 30px 60px rgba(0,0,0,.45);
        }
        .badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(245,184,31,.14); color: #f5b81f;
            font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
            padding: 6px 14px; border-radius: 999px;
        }
        h1 { margin: 22px 0 10px; font-size: 22px; }
        p { margin: 0; color: #9aa6bd; font-size: 14px; line-height: 1.6; }
        .btn {
            display: inline-block; margin-top: 24px; padding: 12px 26px; border-radius: 999px;
            background: #f5b81f; color: #0b1220; font-weight: 700; font-size: 14px; text-decoration: none;
        }
        .btn:hover { background: #ffc846; }
        .status { margin-top: 18px; font-size: 12px; color: #5d6b85; }
    </style>
</head>
<body>
    <div class="card">
        <span class="badge">Maintenance</span>
        <h1>Service temporairement indisponible</h1>
        <p>{{ $message ?? "Ce site est momentanément inaccessible. Merci de réessayer plus tard." }}</p>

        @if (! empty($renewalUrl))
            <a class="btn" href="{{ $renewalUrl }}">Renouveler l'abonnement</a>
        @endif

        @if (! empty($status))
            <p class="status">Réf. : {{ $status }}</p>
        @endif
    </div>
</body>
</html>
