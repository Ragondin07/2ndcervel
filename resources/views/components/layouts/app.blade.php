<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title ?? config('app.name', 'Memoire Projet Privee') }}</title>
    <style>
        :root {
            color-scheme: light dark;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.5;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: #f6f7f9;
            color: #1f2933;
        }

        a {
            color: inherit;
        }

        .page {
            min-height: 100vh;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 24px;
            border-bottom: 1px solid #d9e2ec;
            background: #ffffff;
        }

        .brand {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .content {
            width: min(1040px, calc(100vw - 32px));
            margin: 0 auto;
            padding: 40px 0;
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .panel {
            width: min(420px, 100%);
            padding: 28px;
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
        }

        h1 {
            margin: 0 0 8px;
            font-size: 1.75rem;
            line-height: 1.2;
        }

        p {
            margin: 0;
            color: #52606d;
        }

        form {
            margin: 24px 0 0;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.9rem;
            font-weight: 650;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            min-height: 44px;
            padding: 10px 12px;
            border: 1px solid #bcccdc;
            border-radius: 6px;
            background: #ffffff;
            color: #1f2933;
            font: inherit;
        }

        .field {
            margin-bottom: 16px;
        }

        .check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 4px 0 18px;
            color: #52606d;
            font-size: 0.95rem;
        }

        .error {
            margin-top: 6px;
            color: #b42318;
            font-size: 0.9rem;
        }

        button {
            min-height: 42px;
            padding: 10px 16px;
            border: 0;
            border-radius: 6px;
            background: #1f2933;
            color: #ffffff;
            cursor: pointer;
            font: inherit;
            font-weight: 700;
        }

        .logout {
            margin: 0;
        }

        .empty-state {
            padding: 36px;
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            background: #ffffff;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background: #111827;
                color: #f9fafb;
            }

            .topbar,
            .panel,
            .empty-state {
                border-color: #334155;
                background: #1f2937;
            }

            p,
            .check {
                color: #cbd5e1;
            }

            input[type="email"],
            input[type="password"] {
                border-color: #475569;
                background: #111827;
                color: #f9fafb;
            }

            button {
                background: #e5e7eb;
                color: #111827;
            }
        }
    </style>
</head>
<body>
    {{ $slot }}
</body>
</html>
