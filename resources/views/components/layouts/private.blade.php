@props(['title' => config('app.name')])

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title }} - {{ config('app.name') }}</title>
    <style>
        :root {
            color-scheme: light dark;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.5;
            --bg: #f6f7f9;
            --surface: #ffffff;
            --surface-soft: #eef2f6;
            --border: #d9e2ec;
            --text: #1f2933;
            --muted: #64748b;
            --accent: #0f766e;
            --accent-dark: #115e59;
            --danger: #b42318;
            --info: #2563eb;
            --success: #15803d;
            --warning: #d97706;
            --card-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: var(--bg);
            color: var(--text);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input {
            font: inherit;
        }

        .app-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
        }

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 20px;
            border-right: 1px solid var(--border);
            background: var(--surface);
            overflow-y: auto;
        }

        .brand {
            margin: 0 0 20px;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .nav {
            display: grid;
            gap: 4px;
        }

        .nav a {
            padding: 10px 12px;
            border-radius: 6px;
            color: var(--muted);
            font-weight: 650;
        }

        .nav a:hover,
        .nav a.active {
            background: var(--surface-soft);
            color: var(--text);
        }

        .main {
            min-width: 0;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 70px;
            padding: 18px 28px;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
        }

        .topbar-title {
            margin: 0;
            font-size: 1.25rem;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .logout {
            margin: 0;
        }

        .button,
        button {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 14px;
            border: 0;
            border-radius: 6px;
            background: var(--text);
            color: #ffffff;
            cursor: pointer;
            font-weight: 750;
        }

        .button.secondary,
        button.secondary {
            background: var(--surface-soft);
            color: var(--text);
        }

        .button.primary,
        button.primary {
            background: var(--accent);
        }

        .button.primary:hover,
        button.primary:hover {
            background: var(--accent-dark);
        }

        button.danger {
            background: var(--danger);
            color: #ffffff;
        }

        .content {
            width: min(1180px, calc(100vw - 32px));
            margin: 0 auto;
            padding: 28px 0 48px;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .section-header h2 {
            margin: 0;
            font-size: 1.1rem;
        }

        .grid {
            display: grid;
            gap: 18px;
        }

        .grid.two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .grid.three {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .grid.four {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .panel {
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
            box-shadow: var(--card-shadow);
        }

        .card-accent {
            border-left: 4px solid var(--accent);
        }

        .metric-card {
            border-top: 4px solid var(--info);
        }

        .notice {
            padding: 12px 14px;
            border: 1px solid #99f6e4;
            border-radius: 8px;
            background: #ccfbf1;
            color: #134e4a;
            font-weight: 700;
        }

        .panel-inner {
            padding: 18px;
        }

        .panel-title {
            margin: 0 0 12px;
            font-size: 1rem;
        }

        .metric {
            margin: 0;
            font-size: 2rem;
            font-weight: 800;
        }

        .list {
            display: grid;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .list-item {
            padding: 10px 0;
            border-top: 1px solid var(--border);
        }

        .list-item:first-child {
            border-top: 0;
            padding-top: 0;
        }

        .item-title {
            margin: 0;
            font-weight: 750;
        }

        .item-meta,
        .empty {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .project-row,
        .row-actions,
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .row-actions,
        .form-actions {
            flex-wrap: wrap;
        }

        .row-actions form,
        .form-actions form {
            margin: 0;
        }

        .tight-actions {
            justify-content: flex-start;
            gap: 8px;
        }

        .inbox-item {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(340px, 0.9fr);
            gap: 16px;
            align-items: start;
        }

        .inbox-actions {
            display: grid;
            gap: 10px;
        }

        .inline-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 8px;
            align-items: end;
            margin: 0;
        }

        .item-excerpt {
            margin-top: 8px;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .compact-list .list-item {
            padding: 8px 0;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 2px 8px;
            border-radius: 999px;
            background: var(--surface-soft);
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 700;
        }

        .stack-form {
            margin: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .filters-form {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            align-items: end;
            gap: 16px;
            margin: 0;
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            align-items: end;
            gap: 16px;
            margin: 0;
        }

        .filter-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        textarea,
        select,
        input[type="text"],
        input[type="search"],
        input[type="date"],
        .file-input {
            width: 100%;
            min-height: 44px;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--bg);
            color: var(--text);
            font: inherit;
        }

        textarea {
            resize: vertical;
        }

        .form-actions {
            justify-content: flex-start;
            margin-top: 20px;
        }

        .description-block {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            white-space: pre-wrap;
        }


        .mini-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .quick-add-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 999px;
            background: var(--accent);
            color: #ffffff;
            font-size: 1.35rem;
            font-weight: 900;
        }

        .badge-info {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-warning {
            background: #ffedd5;
            color: #9a3412;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .progress {
            height: 12px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--surface-soft);
        }

        .progress span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--accent), var(--success));
        }

        .checkbox-line {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .compact-project-form {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .compact-project-form select {
            max-width: 220px;
        }




        .shortcut-help {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 50;
            width: min(320px, calc(100vw - 32px));
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
            box-shadow: var(--card-shadow);
        }

        .kanban-board {
            display: grid;
            grid-template-columns: repeat(4, minmax(220px, 1fr));
            gap: 14px;
            align-items: start;
            overflow-x: auto;
            padding-bottom: 8px;
        }

        .kanban-column {
            min-width: 220px;
            background: var(--surface-soft);
        }

        .kanban-card {
            display: grid;
            gap: 6px;
            padding: 12px;
            margin-top: 10px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
        }

        .context-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 18px;
            align-items: start;
        }

        .context-panel {
            position: sticky;
            top: 88px;
        }

        .context-panel h3 {
            margin: 18px 0 6px;
            font-size: 0.92rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .timeline-list {
            position: relative;
            display: grid;
            gap: 0;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .timeline-item {
            position: relative;
            display: grid;
            grid-template-columns: 120px minmax(0, 1fr);
            gap: 18px;
            padding: 0 0 18px;
        }

        .timeline-item::before {
            content: "";
            position: absolute;
            top: 26px;
            bottom: 0;
            left: 130px;
            width: 2px;
            background: var(--border);
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-item:last-child::before {
            display: none;
        }

        .timeline-date {
            color: var(--muted);
            font-size: 0.85rem;
            font-weight: 700;
        }

        .timeline-content {
            position: relative;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
        }

        .timeline-content::before {
            content: "";
            position: absolute;
            top: 18px;
            left: -25px;
            width: 12px;
            height: 12px;
            border: 3px solid var(--accent);
            border-radius: 999px;
            background: var(--surface);
        }

        .timeline-title {
            margin: 6px 0 0;
            font-weight: 800;
        }

        .timeline-description {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .timeline-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .timeline-filters .badge {
            border: 1px solid var(--border);
        }

        .timeline-filters .active {
            background: var(--accent);
            color: #ffffff;
        }

        .pagination {
            margin-top: 18px;
        }

        .meta-list {
            display: grid;
            gap: 8px;
            margin: 0;
            padding-left: 18px;
            color: var(--muted);
        }

        code {
            overflow-wrap: anywhere;
        }

        .archived-panel {
            opacity: 0.88;
        }

        .error {
            margin-top: 6px;
            color: var(--danger);
            font-size: 0.9rem;
        }

        .search-box {
            display: flex;
            gap: 10px;
            padding: 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
        }

        .search-box input {
            width: 100%;
            min-height: 44px;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--bg);
            color: var(--text);
        }

        .placeholder {
            padding: 32px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
        }

        .placeholder h2 {
            margin: 0 0 8px;
        }

        .placeholder p {
            margin: 0;
            color: var(--muted);
        }

        @media (max-width: 980px) {
            .kanban-board {
                grid-template-columns: 1fr;
                overflow-x: visible;
            }

            .context-layout {
                grid-template-columns: 1fr;
            }

            .context-panel {
                position: static;
            }

            .grid.four,
            .grid.three {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .inbox-item {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .app-shell {
                display: block;
            }

            .sidebar {
                position: static;
                height: auto;
                border-right: 0;
                border-bottom: 1px solid var(--border);
            }

            .nav {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .grid.two,
            .grid.three,
            .grid.four,
            .form-grid,
            .filters-form,
            .search-form {
                grid-template-columns: 1fr;
            }

            .timeline-item {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .timeline-item::before,
            .timeline-content::before {
                display: none;
            }

            .project-row {
                align-items: flex-start;
                flex-direction: column;
            }

            .row-actions,
            .row-actions .button,
            .row-actions button,
            .compact-project-form {
                width: 100%;
            }

            .compact-project-form {
                display: grid;
            }

            .compact-project-form select {
                max-width: none;
            }

            .search-box {
                display: grid;
            }

            .inline-form {
                grid-template-columns: 1fr;
            }
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #111827;
                --surface: #1f2937;
                --surface-soft: #334155;
                --border: #334155;
                --text: #f9fafb;
                --muted: #cbd5e1;
                --accent: #14b8a6;
                --accent-dark: #0f766e;
            }

            .button,
            button {
                background: #e5e7eb;
                color: #111827;
            }

            .button.primary,
            .button.primary:hover,
            button.primary,
            button.primary:hover {
                color: #042f2e;
            }

            .notice {
                border-color: #0f766e;
                background: #134e4a;
                color: #ccfbf1;
            }

            .badge-info, .badge-success, .badge-warning, .badge-danger {
                color: #0f172a;
            }
        }
    </style>
</head>
<body>
    @php
        $items = [
            ['label' => 'Tableau de bord', 'route' => 'dashboard', 'active' => 'dashboard'],
            ['label' => 'Projets', 'route' => 'projects.index', 'active' => 'projects.*'],
            ['label' => 'Notes', 'route' => 'notes.index', 'active' => 'notes.*'],
            ['label' => 'Ajouter rapidement', 'route' => 'quick-add', 'active' => 'quick-add'],
            ['label' => 'Recherche', 'route' => 'search', 'active' => 'search'],
            ['label' => 'Decisions', 'route' => 'decisions.index', 'active' => 'decisions.*'],
            ['label' => 'Actions', 'route' => 'actions.index', 'active' => 'actions.*'],
            ['label' => 'Fichiers', 'route' => 'files.index', 'active' => 'files.*'],
            ['label' => 'Inbox', 'route' => 'inbox', 'active' => 'inbox'],
            ['label' => 'Administration', 'route' => 'admin.index', 'active' => 'admin.*'],
        ];
    @endphp

    <div class="app-shell">
        <aside class="sidebar">
            <p class="brand">{{ config('app.name') }}</p>
            <nav class="nav" aria-label="Navigation principale">
                @foreach ($items as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        @class(['active' => request()->routeIs($item['active'])])
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="main">
            <header class="topbar">
                <h1 class="topbar-title">{{ $title }}</h1>
                <div class="topbar-user">
                    <span>{{ auth()->user()->name }}</span>
                    <form class="logout" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Deconnexion</button>
                    </form>
                </div>
            </header>

            <main class="content">
                {{ $slot }}
            </main>
        </div>
    </div>

    <div id="shortcut-help" class="shortcut-help" hidden>
        <div class="panel-inner">
            <h2>Raccourcis clavier</h2>
            <p><strong>N</strong> : nouvelle note rapide</p>
            <p><strong>P</strong> : nouveau projet</p>
            <p><strong>A</strong> : nouvelle action</p>
            <p><strong>R</strong> : recherche</p>
            <p><strong>?</strong> : afficher/masquer cette aide</p>
        </div>
    </div>
    <script>
        const shortcutRoutes = {
            n: @json(route('notes.create')),
            p: @json(route('projects.create')),
            a: @json(route('actions.create')),
            r: @json(route('search')),
        };

        document.addEventListener('keydown', (event) => {
            const target = event.target;
            const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(target?.tagName) || target?.isContentEditable;
            if (isTyping || event.ctrlKey || event.metaKey || event.altKey) {
                return;
            }

            if (event.key === '?') {
                const help = document.getElementById('shortcut-help');
                help.hidden = !help.hidden;
                return;
            }

            const route = shortcutRoutes[event.key.toLowerCase()];
            if (route) {
                window.location.href = route;
            }
        });
    </script>

</body>
</html>
