<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel administracyjny — Pani Matcha</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500..600&family=Manrope:wght@400..800&display=swap" rel="stylesheet">
    <style>
        :root {
            --matcha-50: #f4f8ee;
            --matcha-100: #e6f0d8;
            --matcha-200: #cfe2b4;
            --matcha-300: #b4d18c;
            --matcha-500: #7ba64c;
            --matcha-600: #5f8639;
            --matcha-700: #48682e;
            --matcha-800: #354d26;
            --matcha-900: #24361c;

            --cream-50: #fffdf8;
            --cream-100: #fbf7ee;
            --cream-200: #f4ecdc;

            --clay-400: #c8b8a0;
            --clay-600: #8a7a63;
            --clay-800: #4b4234;

            --gold-400: #d9b86a;
            --gold-500: #c19a45;

            --red-500: #dc2626;
            --red-50: #fef2f2;
            --green-600: #16a34a;
            --green-50: #f0fdf4;

            --radius: 12px;
            --shadow-sm: 0 1px 2px rgba(36, 54, 28, 0.06);
            --shadow-md: 0 8px 24px -8px rgba(36, 54, 28, 0.16);
            --font-display: 'Fraunces', Georgia, serif;
            --font-sans: 'Manrope', system-ui, -apple-system, 'Segoe UI', sans-serif;
        }

        * { box-sizing: border-box; }

        html { -webkit-text-size-adjust: 100%; }

        body {
            font-family: var(--font-sans);
            margin: 0;
            background: var(--cream-100);
            color: var(--matcha-900);
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3 { font-family: var(--font-display); font-weight: 600; }
        h1 { margin: 0; font-size: 1.5rem; }
        h2 { margin: 0; font-size: 1.15rem; }
        h3 { margin: 0 0 4px; font-size: 1.05rem; color: var(--matcha-800); }

        a { color: var(--matcha-700); }

        ::selection { background: var(--matcha-200); color: var(--matcha-900); }

        :focus-visible {
            outline: 2px solid var(--matcha-600);
            outline-offset: 2px;
            border-radius: 4px;
        }

        /* ---------- App shell: sidebar + content ---------- */

        .app-shell {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            flex: 0 0 240px;
            display: flex;
            flex-direction: column;
            background: var(--matcha-900);
            color: var(--cream-100);
            padding: 24px 18px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 8px 20px;
            margin-bottom: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .brand-mark {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            background: var(--matcha-500);
            color: var(--cream-50);
            flex-shrink: 0;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .brand-text strong {
            font-family: var(--font-display);
            font-size: 1rem;
            color: var(--cream-50);
        }

        .brand-text span {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--matcha-300);
        }

        .section-nav {
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin: 12px 0;
            padding: 0;
            flex: 1;
        }

        .section-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-radius: 8px;
            color: var(--matcha-100);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .section-nav a:hover,
        .section-nav a:focus-visible {
            background: rgba(255, 255, 255, 0.08);
            color: var(--cream-50);
        }

        .section-nav svg { flex-shrink: 0; opacity: 0.85; }

        .sidebar-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 14px;
            margin-top: 8px;
        }

        .logout-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 10px;
            border-radius: 8px;
            color: var(--matcha-200);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .logout-link:hover { background: rgba(255, 255, 255, 0.08); color: var(--cream-50); }

        .main { flex: 1; min-width: 0; }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 32px;
            background: var(--cream-50);
            border-bottom: 1px solid rgba(53, 77, 38, 0.08);
            position: sticky;
            top: 0;
            z-index: 5;
        }

        .topbar p {
            margin: 3px 0 0;
            font-size: 13px;
            color: var(--clay-600);
        }

        .content {
            padding: 28px 32px 64px;
            max-width: 1080px;
        }

        /* ---------- Panels / cards ---------- */

        .panel {
            background: var(--cream-50);
            border: 1px solid rgba(53, 77, 38, 0.08);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            padding: 24px;
            margin-bottom: 22px;
            scroll-margin-top: 90px;
        }

        .panel-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
        }

        .panel-icon {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--matcha-100);
            color: var(--matcha-700);
            flex-shrink: 0;
        }

        .panel-hint {
            margin: 4px 0 18px;
            font-size: 13px;
            color: var(--clay-600);
        }

        /* ---------- Forms ---------- */

        form.inline-form {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: flex-end;
        }

        form.inline-form label {
            display: flex;
            flex-direction: column;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--matcha-800);
            gap: 6px;
            flex: 1 1 170px;
        }

        form.inline-form label:has(input[type="checkbox"]) {
            flex-direction: row;
            align-items: center;
            flex: 0 0 auto;
            font-weight: 500;
            padding-bottom: 9px;
        }

        input, select, textarea {
            padding: 9px 11px;
            border: 1px solid rgba(53, 77, 38, 0.18);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: var(--cream-50);
            color: var(--matcha-900);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        input:hover, select:hover, textarea:hover { border-color: rgba(53, 77, 38, 0.32); }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--matcha-500);
            box-shadow: 0 0 0 3px rgba(123, 166, 76, 0.18);
        }

        input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--matcha-600);
            padding: 0;
        }

        input[type="file"] { padding: 7px; font-size: 13px; }

        textarea { min-height: 68px; resize: vertical; }

        /* ---------- Buttons ---------- */

        button {
            padding: 9px 18px;
            border: none;
            border-radius: 999px;
            background: var(--matcha-700);
            color: var(--cream-50);
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 600;
            font-family: inherit;
            transition: background-color 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
        }

        button:hover { background: var(--matcha-600); transform: translateY(-1px); box-shadow: var(--shadow-sm); }
        button:active { transform: translateY(0); }

        button.secondary {
            background: transparent;
            color: var(--clay-800);
            border: 1px solid rgba(53, 77, 38, 0.2);
        }
        button.secondary:hover { background: var(--matcha-50); box-shadow: none; }

        button.danger { background: var(--red-500); }
        button.danger:hover { background: #b91c1c; }

        /* ---------- Status messages ---------- */

        .status-msg {
            font-size: 13px;
            margin-top: 10px;
            min-height: 1.2em;
            font-weight: 600;
        }
        .status-msg.error { color: var(--red-500); }
        .status-msg.success { color: var(--green-600); }

        /* ---------- Tables ---------- */

        .table-wrap { overflow-x: auto; margin-top: 14px; border-radius: 10px; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

        th, td {
            text-align: left;
            padding: 11px 10px;
            border-bottom: 1px solid rgba(53, 77, 38, 0.08);
            vertical-align: middle;
        }

        th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--clay-600);
            font-weight: 700;
            background: var(--matcha-50);
        }

        th:first-child { border-top-left-radius: 10px; }
        th:last-child { border-top-right-radius: 10px; }

        tbody tr:hover { background: var(--matcha-50); }
        tbody tr:last-child td { border-bottom: none; }

        td.actions { display: flex; flex-wrap: wrap; gap: 6px; }
        td.actions button { padding: 6px 13px; font-size: 12.5px; }

        img.thumb {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 8px;
            background: var(--matcha-100);
            display: block;
        }
        div.thumb {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            background: var(--matcha-50);
            border: 1px dashed rgba(53, 77, 38, 0.25);
        }

        .category-group { margin-top: 26px; }
        .category-group:first-child { margin-top: 8px; }
        .category-group h3 {
            display: inline-block;
            border-bottom: 2px solid var(--gold-500);
            padding-bottom: 4px;
        }

        .unavailable { opacity: 0.55; }

        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 999px;
            background: var(--matcha-50);
            color: var(--clay-600);
        }
        .badge.available, .badge.published { background: var(--green-50); color: var(--green-600); }
        .badge.unavailable, .badge.hidden { background: var(--red-50); color: var(--red-500); }

        .rating-stars { color: var(--gold-500); letter-spacing: 1px; font-size: 15px; }

        /* ---------- Gallery ---------- */

        .gallery-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 16px;
        }
        .gallery-item {
            width: 168px;
            border: 1px solid rgba(53, 77, 38, 0.1);
            border-radius: 12px;
            padding: 10px;
            text-align: center;
            background: var(--cream-50);
            box-shadow: var(--shadow-sm);
        }
        .gallery-item img.thumb-lg {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            background: var(--matcha-100);
            display: block;
        }
        div.thumb-lg {
            width: 100%;
            height: 120px;
            border-radius: 8px;
            background: var(--matcha-50);
            border: 1px dashed rgba(53, 77, 38, 0.25);
        }
        .gallery-item .caption {
            font-size: 13px;
            margin: 8px 0;
            word-break: break-word;
            min-height: 1.2em;
        }
        .gallery-item .actions {
            display: flex;
            justify-content: center;
            gap: 6px;
        }
        .gallery-item .actions button { padding: 6px 12px; font-size: 12px; }

        /* ---------- Responsive ---------- */

        @media (max-width: 880px) {
            .app-shell { flex-direction: column; }
            .sidebar {
                position: static;
                height: auto;
                flex-direction: row;
                align-items: center;
                flex-wrap: wrap;
                gap: 8px 16px;
                padding: 14px 18px;
            }
            .brand { border-bottom: none; padding: 0; margin: 0; }
            .section-nav { flex-direction: row; flex-wrap: wrap; margin: 0; flex: 1; }
            .sidebar-footer { border-top: none; padding-top: 0; margin-top: 0; }
            .topbar, .content { padding-left: 18px; padding-right: 18px; }
        }

        @media (max-width: 560px) {
            form.inline-form label { flex-basis: 100%; }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 9h13a3 3 0 0 1 0 6h-1" stroke-linecap="round" />
                        <path d="M4 9v3a5 5 0 0 0 5 5h3a5 5 0 0 0 5-5V9" stroke-linecap="round" />
                    </svg>
                </span>
                <span class="brand-text">
                    <strong>Pani Matcha</strong>
                    <span>Panel administracyjny</span>
                </span>
            </div>

            <nav class="section-nav">
                <a href="#section-categories">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h9" stroke-linecap="round" /></svg>
                    Kategorie
                </a>
                <a href="#section-products">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7 12 3 4 7v10l8 4 8-4V7Z" stroke-linejoin="round" /><path d="M4 7l8 4 8-4M12 11v10" stroke-linejoin="round" /></svg>
                    Produkty
                </a>
                <a href="#section-gallery">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2" /><circle cx="8.5" cy="9.5" r="1.5" /><path d="m4 17 5-5 4 4 3-3 4 4" stroke-linejoin="round" /></svg>
                    Galeria
                </a>
                <a href="#section-events">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M8 3v4M16 3v4M3 10h18" stroke-linecap="round" /></svg>
                    Wydarzenia
                </a>
                <a href="#section-opinions">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 1.6l2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.2-4.1 5.8-.8L12 1.6z" stroke-linejoin="round" /></svg>
                    Opinie
                </a>
                <a href="#section-settings">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3.2" /><path d="M19.4 13.5a7.6 7.6 0 0 0 0-3l2-1.5-2-3.4-2.3.9a7.7 7.7 0 0 0-2.6-1.5L14 2.5h-4l-.5 2.5a7.7 7.7 0 0 0-2.6 1.5l-2.3-.9-2 3.4 2 1.5a7.6 7.6 0 0 0 0 3l-2 1.5 2 3.4 2.3-.9c.75.66 1.63 1.17 2.6 1.5l.5 2.5h4l.5-2.5a7.7 7.7 0 0 0 2.6-1.5l2.3.9 2-3.4-2-1.5Z" stroke-linejoin="round" /></svg>
                    Ustawienia
                </a>
            </nav>

            <div class="sidebar-footer">
                <a href="logout.php" class="logout-link">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 17l5-5-5-5M20 12H9M12 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    Wyloguj
                </a>
            </div>
        </aside>

        <div class="main">
            <header class="topbar">
                <div>
                    <h1>Panel administracyjny</h1>
                    <p>Zarządzaj treścią strony panimatcha.pl</p>
                </div>
            </header>

            <div class="content">
                <div class="panel" id="section-categories">
                    <div class="panel-head">
                        <span class="panel-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h9" stroke-linecap="round" /></svg>
                        </span>
                        <h2 id="category-form-title">Dodaj kategorię</h2>
                    </div>
                    <p class="panel-hint">Kategorie porządkują menu na stronie — kolejność decyduje, w jakiej sekcje pojawiają się dla gości.</p>
                    <form id="category-form" class="inline-form">
                        <input type="hidden" id="category-id" value="">
                        <label>
                            Nazwa
                            <input type="text" id="category-name" required>
                        </label>
                        <label>
                            Kolejność
                            <input type="number" id="category-sort-order" value="0">
                        </label>
                        <button type="submit">Zapisz</button>
                        <button type="button" class="secondary" id="category-form-cancel" style="display:none;">Anuluj edycję</button>
                    </form>
                    <div id="category-status" class="status-msg"></div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nazwa</th>
                                    <th>Kolejność</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody id="categories-table-body"></tbody>
                        </table>
                    </div>
                </div>

                <div class="panel" id="section-products">
                    <div class="panel-head">
                        <span class="panel-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7 12 3 4 7v10l8 4 8-4V7Z" stroke-linejoin="round" /><path d="M4 7l8 4 8-4M12 11v10" stroke-linejoin="round" /></svg>
                        </span>
                        <h2 id="product-form-title">Dodaj produkt</h2>
                    </div>
                    <p class="panel-hint">Zdjęcie jest opcjonalne — bez niego produkt pokaże się na stronie z ikoną zamiast fotografii.</p>
                    <form id="product-form" class="inline-form" enctype="multipart/form-data">
                        <input type="hidden" id="product-id" value="">
                        <label>
                            Nazwa
                            <input type="text" id="product-name" required>
                        </label>
                        <label>
                            Kategoria
                            <select id="product-category-id" required></select>
                        </label>
                        <label>
                            Cena
                            <input type="number" id="product-price" step="0.01" min="0" required>
                        </label>
                        <label>
                            Kolejność
                            <input type="number" id="product-sort-order" value="0">
                        </label>
                        <label>
                            Zdjęcie
                            <input type="file" id="product-photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        </label>
                        <label>
                            <input type="checkbox" id="product-is-available" checked>
                            Dostępny
                        </label>
                        <label style="flex-basis: 100%;">
                            Opis
                            <textarea id="product-description"></textarea>
                        </label>
                        <button type="submit">Zapisz</button>
                        <button type="button" class="secondary" id="product-form-cancel" style="display:none;">Anuluj edycję</button>
                    </form>
                    <div id="product-status" class="status-msg"></div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <span class="panel-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7 12 3 4 7v10l8 4 8-4V7Z" stroke-linejoin="round" /></svg>
                        </span>
                        <h2>Produkty</h2>
                    </div>
                    <p class="panel-hint">Produkty pogrupowane wg kategorii, tak jak pojawią się w menu na stronie.</p>
                    <div id="products-container"></div>
                </div>

                <div class="panel" id="section-gallery">
                    <div class="panel-head">
                        <span class="panel-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2" /><circle cx="8.5" cy="9.5" r="1.5" /><path d="m4 17 5-5 4 4 3-3 4 4" stroke-linejoin="round" /></svg>
                        </span>
                        <h2 id="gallery-form-title">Dodaj zdjęcie do galerii</h2>
                    </div>
                    <p class="panel-hint">Zdjęcia pojawiają się na stronie w kolejności rosnącej wg pola „Kolejność”.</p>
                    <form id="gallery-form" class="inline-form" enctype="multipart/form-data">
                        <input type="hidden" id="gallery-id" value="">
                        <label>
                            Zdjęcie
                            <input type="file" id="gallery-photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        </label>
                        <label>
                            Podpis
                            <input type="text" id="gallery-caption">
                        </label>
                        <label>
                            Kolejność
                            <input type="number" id="gallery-sort-order" value="0">
                        </label>
                        <button type="submit">Zapisz</button>
                        <button type="button" class="secondary" id="gallery-form-cancel" style="display:none;">Anuluj edycję</button>
                    </form>
                    <div id="gallery-status" class="status-msg"></div>

                    <div class="gallery-grid" id="gallery-container"></div>
                </div>

                <div class="panel" id="section-events">
                    <div class="panel-head">
                        <span class="panel-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M8 3v4M16 3v4M3 10h18" stroke-linecap="round" /></svg>
                        </span>
                        <h2 id="event-form-title">Dodaj wydarzenie</h2>
                    </div>
                    <p class="panel-hint">Wydarzenia wyświetlają się na stronie posortowane po dacie.</p>
                    <form id="event-form" class="inline-form" enctype="multipart/form-data">
                        <input type="hidden" id="event-id" value="">
                        <label>
                            Tytuł
                            <input type="text" id="event-title" required>
                        </label>
                        <label>
                            Data
                            <input type="date" id="event-date" required>
                        </label>
                        <label>
                            Zdjęcie
                            <input type="file" id="event-photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        </label>
                        <label style="flex-basis: 100%;">
                            Opis
                            <textarea id="event-description"></textarea>
                        </label>
                        <button type="submit">Zapisz</button>
                        <button type="button" class="secondary" id="event-form-cancel" style="display:none;">Anuluj edycję</button>
                    </form>
                    <div id="event-status" class="status-msg"></div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Zdjęcie</th>
                                    <th>Tytuł</th>
                                    <th>Data</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody id="events-table-body"></tbody>
                        </table>
                    </div>
                </div>

                <div class="panel" id="section-opinions">
                    <div class="panel-head">
                        <span class="panel-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 1.6l2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.2-4.1 5.8-.8L12 1.6z" stroke-linejoin="round" /></svg>
                        </span>
                        <h2 id="opinion-form-title">Dodaj opinię</h2>
                    </div>
                    <p class="panel-hint">Tylko opublikowane opinie są widoczne dla gości na stronie.</p>
                    <form id="opinion-form" class="inline-form">
                        <input type="hidden" id="opinion-id" value="">
                        <label>
                            Autor
                            <input type="text" id="opinion-author" required>
                        </label>
                        <label>
                            Ocena
                            <select id="opinion-rating" required>
                                <option value="5">5</option>
                                <option value="4">4</option>
                                <option value="3">3</option>
                                <option value="2">2</option>
                                <option value="1">1</option>
                            </select>
                        </label>
                        <label>
                            <input type="checkbox" id="opinion-is-published">
                            Opublikowana
                        </label>
                        <label style="flex-basis: 100%;">
                            Treść
                            <textarea id="opinion-content" required></textarea>
                        </label>
                        <button type="submit">Zapisz</button>
                        <button type="button" class="secondary" id="opinion-form-cancel" style="display:none;">Anuluj edycję</button>
                    </form>
                    <div id="opinion-status" class="status-msg"></div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Autor</th>
                                    <th>Treść</th>
                                    <th>Ocena</th>
                                    <th>Status</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody id="opinions-table-body"></tbody>
                        </table>
                    </div>
                </div>

                <div class="panel" id="section-settings">
                    <div class="panel-head">
                        <span class="panel-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3.2" /><path d="M19.4 13.5a7.6 7.6 0 0 0 0-3l2-1.5-2-3.4-2.3.9a7.7 7.7 0 0 0-2.6-1.5L14 2.5h-4l-.5 2.5a7.7 7.7 0 0 0-2.6 1.5l-2.3-.9-2 3.4 2 1.5a7.6 7.6 0 0 0 0 3l-2 1.5 2 3.4 2.3-.9c.75.66 1.63 1.17 2.6 1.5l.5 2.5h4l.5-2.5a7.7 7.7 0 0 0 2.6-1.5l2.3.9 2-3.4-2-1.5Z" stroke-linejoin="round" /></svg>
                        </span>
                        <h2>Ustawienia strony</h2>
                    </div>
                    <p class="panel-hint">Te dane trafiają bezpośrednio na stronę główną — hero, sekcję kontaktową i stopkę.</p>
                    <form id="settings-form" class="inline-form">
                        <label style="flex-basis: 100%;">
                            Tekst hero
                            <textarea id="setting-hero-text"></textarea>
                        </label>
                        <label>
                            Telefon kontaktowy
                            <input type="text" id="setting-contact-phone">
                        </label>
                        <label>
                            E-mail kontaktowy
                            <input type="text" id="setting-contact-email">
                        </label>
                        <label>
                            Godziny otwarcia
                            <input type="text" id="setting-opening-hours">
                        </label>
                        <label>
                            Link do Instagrama
                            <input type="text" id="setting-instagram-url">
                        </label>
                        <button type="submit">Zapisz wszystkie ustawienia</button>
                    </form>
                    <div id="settings-status" class="status-msg"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        'use strict';

        var categoriesCache = [];
        var productsCache = [];

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = str == null ? '' : String(str);
            return div.innerHTML;
        }

        function setStatus(elId, message, isError) {
            var el = document.getElementById(elId);
            el.textContent = message;
            el.className = 'status-msg ' + (isError ? 'error' : 'success');
            if (message) {
                setTimeout(function () {
                    if (el.textContent === message) {
                        el.textContent = '';
                        el.className = 'status-msg';
                    }
                }, 4000);
            }
        }

        function apiRequest(url, options) {
            options = options || {};
            var opts = Object.assign({ headers: {} }, options);
            if (opts.body && !(opts.body instanceof FormData)) {
                opts.headers['Content-Type'] = 'application/json';
            }
            return fetch(url, opts).then(function (res) {
                return res.json().then(function (data) {
                    if (!res.ok) {
                        throw new Error(data.error || 'Wystąpił błąd (' + res.status + ')');
                    }
                    return data;
                });
            });
        }

        // ---------- Categories ----------

        function loadCategories() {
            return apiRequest('api/categories.php').then(function (data) {
                categoriesCache = data;
                renderCategoriesTable();
                renderCategoryDropdown();
            }).catch(function (err) {
                setStatus('category-status', err.message, true);
            });
        }

        function renderCategoriesTable() {
            var tbody = document.getElementById('categories-table-body');
            tbody.innerHTML = '';
            categoriesCache.forEach(function (cat) {
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td>' + escapeHtml(cat.name) + '</td>' +
                    '<td>' + escapeHtml(cat.sort_order) + '</td>' +
                    '<td class="actions">' +
                        '<button type="button" data-action="edit-category" data-id="' + cat.id + '">Edytuj</button>' +
                        '<button type="button" class="danger" data-action="delete-category" data-id="' + cat.id + '">Usuń</button>' +
                    '</td>';
                tbody.appendChild(tr);
            });
        }

        function renderCategoryDropdown() {
            var select = document.getElementById('product-category-id');
            var current = select.value;
            select.innerHTML = '';
            categoriesCache.forEach(function (cat) {
                var opt = document.createElement('option');
                opt.value = cat.id;
                opt.textContent = cat.name;
                select.appendChild(opt);
            });
            if (current) select.value = current;
        }

        document.getElementById('category-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var id = document.getElementById('category-id').value;
            var payload = {
                name: document.getElementById('category-name').value.trim(),
                sort_order: parseInt(document.getElementById('category-sort-order').value, 10) || 0
            };

            var url = 'api/categories.php' + (id ? '?id=' + encodeURIComponent(id) : '');
            var method = id ? 'PUT' : 'POST';

            apiRequest(url, { method: method, body: JSON.stringify(payload) }).then(function () {
                setStatus('category-status', 'Zapisano kategorię.', false);
                resetCategoryForm();
                loadCategories().then(loadProducts);
            }).catch(function (err) {
                setStatus('category-status', err.message, true);
            });
        });

        document.getElementById('category-form-cancel').addEventListener('click', resetCategoryForm);

        function resetCategoryForm() {
            document.getElementById('category-id').value = '';
            document.getElementById('category-name').value = '';
            document.getElementById('category-sort-order').value = '0';
            document.getElementById('category-form-title').textContent = 'Dodaj kategorię';
            document.getElementById('category-form-cancel').style.display = 'none';
        }

        document.getElementById('categories-table-body').addEventListener('click', function (e) {
            var btn = e.target.closest('button');
            if (!btn) return;
            var id = btn.getAttribute('data-id');
            var action = btn.getAttribute('data-action');

            if (action === 'edit-category') {
                var cat = categoriesCache.find(function (c) { return String(c.id) === String(id); });
                if (!cat) return;
                document.getElementById('category-id').value = cat.id;
                document.getElementById('category-name').value = cat.name;
                document.getElementById('category-sort-order').value = cat.sort_order;
                document.getElementById('category-form-title').textContent = 'Edytuj kategorię';
                document.getElementById('category-form-cancel').style.display = 'inline-block';
            } else if (action === 'delete-category') {
                if (!confirm('Na pewno usunąć tę kategorię? Produkty w niej pozostaną bez kategorii.')) return;
                apiRequest('api/categories.php?id=' + encodeURIComponent(id), { method: 'DELETE' }).then(function () {
                    setStatus('category-status', 'Usunięto kategorię.', false);
                    loadCategories().then(loadProducts);
                }).catch(function (err) {
                    setStatus('category-status', err.message, true);
                });
            }
        });

        // ---------- Products ----------

        function loadProducts() {
            return apiRequest('api/products.php').then(function (data) {
                productsCache = data;
                renderProducts();
            }).catch(function (err) {
                setStatus('product-status', err.message, true);
            });
        }

        function renderProducts() {
            var container = document.getElementById('products-container');
            container.innerHTML = '';

            var byCategory = {};
            productsCache.forEach(function (p) {
                var key = p.category_id == null ? '' : String(p.category_id);
                if (!byCategory[key]) byCategory[key] = [];
                byCategory[key].push(p);
            });

            var groups = categoriesCache.map(function (c) { return { id: c.id, name: c.name }; });
            groups.push({ id: '', name: 'Bez kategorii' });

            groups.forEach(function (group) {
                var items = byCategory[String(group.id)];
                if (!items || items.length === 0) return;

                var section = document.createElement('div');
                section.className = 'category-group';

                var table = document.createElement('table');
                table.innerHTML =
                    '<thead><tr>' +
                        '<th>Zdjęcie</th>' +
                        '<th>Nazwa</th>' +
                        '<th>Cena</th>' +
                        '<th>Status</th>' +
                        '<th>Akcje</th>' +
                    '</tr></thead>';

                var tbody = document.createElement('tbody');
                items.forEach(function (p) {
                    var tr = document.createElement('tr');
                    if (!Number(p.is_available)) tr.classList.add('unavailable');

                    var photoHtml = p.photo_url
                        ? '<img class="thumb" src="' + escapeHtml(p.photo_url) + '" alt="">'
                        : '<div class="thumb"></div>';

                    tr.innerHTML =
                        '<td>' + photoHtml + '</td>' +
                        '<td>' + escapeHtml(p.name) + '<br><small>' + escapeHtml(p.description || '') + '</small></td>' +
                        '<td>' + Number(p.price).toFixed(2) + ' zł</td>' +
                        '<td><span class="badge ' + (Number(p.is_available) ? 'available' : 'unavailable') + '">' +
                            (Number(p.is_available) ? 'Dostępny' : 'Niedostępny') + '</span></td>' +
                        '<td class="actions">' +
                            '<button type="button" data-action="edit-product" data-id="' + p.id + '">Edytuj</button>' +
                            '<button type="button" class="danger" data-action="delete-product" data-id="' + p.id + '">Usuń</button>' +
                        '</td>';
                    tbody.appendChild(tr);
                });
                table.appendChild(tbody);

                var heading = document.createElement('h3');
                heading.textContent = group.name;

                section.appendChild(heading);
                section.appendChild(table);
                container.appendChild(section);
            });

            if (container.innerHTML === '') {
                container.innerHTML = '<p>Brak produktów.</p>';
            }
        }

        document.getElementById('product-form').addEventListener('submit', function (e) {
            e.preventDefault();

            var id = document.getElementById('product-id').value;
            var fileInput = document.getElementById('product-photo');
            var file = fileInput.files[0];

            function savePayload(photoUrl) {
                var payload = {
                    name: document.getElementById('product-name').value.trim(),
                    category_id: document.getElementById('product-category-id').value,
                    price: parseFloat(document.getElementById('product-price').value),
                    sort_order: parseInt(document.getElementById('product-sort-order').value, 10) || 0,
                    description: document.getElementById('product-description').value.trim(),
                    is_available: document.getElementById('product-is-available').checked ? 1 : 0,
                    photo_url: photoUrl
                };

                var url = 'api/products.php' + (id ? '?id=' + encodeURIComponent(id) : '');
                var method = id ? 'PUT' : 'POST';

                return apiRequest(url, { method: method, body: JSON.stringify(payload) });
            }

            var uploadPromise;
            if (file) {
                var formData = new FormData();
                formData.append('photo', file);
                uploadPromise = apiRequest('api/upload.php', { method: 'POST', body: formData }).then(function (res) {
                    return res.url;
                });
            } else {
                var existing = id ? productsCache.find(function (p) { return String(p.id) === String(id); }) : null;
                uploadPromise = Promise.resolve(existing ? existing.photo_url : '');
            }

            uploadPromise.then(savePayload).then(function () {
                setStatus('product-status', 'Zapisano produkt.', false);
                resetProductForm();
                loadProducts();
            }).catch(function (err) {
                setStatus('product-status', err.message, true);
            });
        });

        document.getElementById('product-form-cancel').addEventListener('click', resetProductForm);

        function resetProductForm() {
            document.getElementById('product-id').value = '';
            document.getElementById('product-name').value = '';
            document.getElementById('product-price').value = '';
            document.getElementById('product-sort-order').value = '0';
            document.getElementById('product-description').value = '';
            document.getElementById('product-is-available').checked = true;
            document.getElementById('product-photo').value = '';
            document.getElementById('product-form-title').textContent = 'Dodaj produkt';
            document.getElementById('product-form-cancel').style.display = 'none';
        }

        document.getElementById('products-container').addEventListener('click', function (e) {
            var btn = e.target.closest('button');
            if (!btn) return;
            var id = btn.getAttribute('data-id');
            var action = btn.getAttribute('data-action');

            if (action === 'edit-product') {
                var p = productsCache.find(function (item) { return String(item.id) === String(id); });
                if (!p) return;
                document.getElementById('product-id').value = p.id;
                document.getElementById('product-name').value = p.name;
                document.getElementById('product-category-id').value = p.category_id || '';
                document.getElementById('product-price').value = p.price;
                document.getElementById('product-sort-order').value = p.sort_order;
                document.getElementById('product-description').value = p.description || '';
                document.getElementById('product-is-available').checked = !!Number(p.is_available);
                document.getElementById('product-photo').value = '';
                document.getElementById('product-form-title').textContent = 'Edytuj produkt';
                document.getElementById('product-form-cancel').style.display = 'inline-block';
                window.scrollTo({ top: document.getElementById('product-form').offsetTop - 20, behavior: 'smooth' });
            } else if (action === 'delete-product') {
                if (!confirm('Na pewno usunąć ten produkt?')) return;
                apiRequest('api/products.php?id=' + encodeURIComponent(id), { method: 'DELETE' }).then(function () {
                    setStatus('product-status', 'Usunięto produkt.', false);
                    loadProducts();
                }).catch(function (err) {
                    setStatus('product-status', err.message, true);
                });
            }
        });

        // ---------- Gallery ----------

        var galleryCache = [];

        function loadGallery() {
            return apiRequest('api/gallery.php').then(function (data) {
                galleryCache = data;
                renderGallery();
            }).catch(function (err) {
                setStatus('gallery-status', err.message, true);
            });
        }

        function renderGallery() {
            var container = document.getElementById('gallery-container');
            container.innerHTML = '';

            if (galleryCache.length === 0) {
                container.innerHTML = '<p>Brak zdjęć w galerii.</p>';
                return;
            }

            galleryCache.forEach(function (item) {
                var div = document.createElement('div');
                div.className = 'gallery-item';

                var photoHtml = item.photo_url
                    ? '<img class="thumb-lg" src="' + escapeHtml(item.photo_url) + '" alt="">'
                    : '<div class="thumb-lg"></div>';

                div.innerHTML =
                    photoHtml +
                    '<div class="caption">' + escapeHtml(item.caption || '') + '</div>' +
                    '<div class="actions">' +
                        '<button type="button" data-action="edit-gallery" data-id="' + item.id + '">Edytuj</button>' +
                        '<button type="button" class="danger" data-action="delete-gallery" data-id="' + item.id + '">Usuń</button>' +
                    '</div>';

                container.appendChild(div);
            });
        }

        document.getElementById('gallery-form').addEventListener('submit', function (e) {
            e.preventDefault();

            var id = document.getElementById('gallery-id').value;
            var fileInput = document.getElementById('gallery-photo');
            var file = fileInput.files[0];

            function savePayload(photoUrl) {
                if (!photoUrl) {
                    setStatus('gallery-status', 'Zdjęcie jest wymagane.', true);
                    return Promise.reject(new Error('Zdjęcie jest wymagane.'));
                }

                var payload = {
                    photo_url: photoUrl,
                    caption: document.getElementById('gallery-caption').value.trim(),
                    sort_order: parseInt(document.getElementById('gallery-sort-order').value, 10) || 0
                };

                var url = 'api/gallery.php' + (id ? '?id=' + encodeURIComponent(id) : '');
                var method = id ? 'PUT' : 'POST';

                return apiRequest(url, { method: method, body: JSON.stringify(payload) });
            }

            var uploadPromise;
            if (file) {
                var formData = new FormData();
                formData.append('photo', file);
                uploadPromise = apiRequest('api/upload.php', { method: 'POST', body: formData }).then(function (res) {
                    return res.url;
                });
            } else {
                var existing = id ? galleryCache.find(function (g) { return String(g.id) === String(id); }) : null;
                uploadPromise = Promise.resolve(existing ? existing.photo_url : '');
            }

            uploadPromise.then(savePayload).then(function () {
                setStatus('gallery-status', 'Zapisano zdjęcie.', false);
                resetGalleryForm();
                loadGallery();
            }).catch(function (err) {
                if (err) setStatus('gallery-status', err.message, true);
            });
        });

        document.getElementById('gallery-form-cancel').addEventListener('click', resetGalleryForm);

        function resetGalleryForm() {
            document.getElementById('gallery-id').value = '';
            document.getElementById('gallery-caption').value = '';
            document.getElementById('gallery-sort-order').value = '0';
            document.getElementById('gallery-photo').value = '';
            document.getElementById('gallery-form-title').textContent = 'Dodaj zdjęcie do galerii';
            document.getElementById('gallery-form-cancel').style.display = 'none';
        }

        document.getElementById('gallery-container').addEventListener('click', function (e) {
            var btn = e.target.closest('button');
            if (!btn) return;
            var id = btn.getAttribute('data-id');
            var action = btn.getAttribute('data-action');

            if (action === 'edit-gallery') {
                var item = galleryCache.find(function (g) { return String(g.id) === String(id); });
                if (!item) return;
                document.getElementById('gallery-id').value = item.id;
                document.getElementById('gallery-caption').value = item.caption || '';
                document.getElementById('gallery-sort-order').value = item.sort_order;
                document.getElementById('gallery-photo').value = '';
                document.getElementById('gallery-form-title').textContent = 'Edytuj zdjęcie';
                document.getElementById('gallery-form-cancel').style.display = 'inline-block';
                window.scrollTo({ top: document.getElementById('gallery-form').offsetTop - 20, behavior: 'smooth' });
            } else if (action === 'delete-gallery') {
                if (!confirm('Na pewno usunąć to zdjęcie z galerii?')) return;
                apiRequest('api/gallery.php?id=' + encodeURIComponent(id), { method: 'DELETE' }).then(function () {
                    setStatus('gallery-status', 'Usunięto zdjęcie.', false);
                    loadGallery();
                }).catch(function (err) {
                    setStatus('gallery-status', err.message, true);
                });
            }
        });

        // ---------- Events ----------

        var eventsCache = [];

        function loadEvents() {
            return apiRequest('api/events.php').then(function (data) {
                eventsCache = data;
                renderEvents();
            }).catch(function (err) {
                setStatus('event-status', err.message, true);
            });
        }

        function renderEvents() {
            var tbody = document.getElementById('events-table-body');
            tbody.innerHTML = '';

            eventsCache.forEach(function (ev) {
                var tr = document.createElement('tr');

                var photoHtml = ev.photo_url
                    ? '<img class="thumb" src="' + escapeHtml(ev.photo_url) + '" alt="">'
                    : '<div class="thumb"></div>';

                tr.innerHTML =
                    '<td>' + photoHtml + '</td>' +
                    '<td>' + escapeHtml(ev.title) + '<br><small>' + escapeHtml(ev.description || '') + '</small></td>' +
                    '<td>' + escapeHtml(ev.event_date) + '</td>' +
                    '<td class="actions">' +
                        '<button type="button" data-action="edit-event" data-id="' + ev.id + '">Edytuj</button>' +
                        '<button type="button" class="danger" data-action="delete-event" data-id="' + ev.id + '">Usuń</button>' +
                    '</td>';
                tbody.appendChild(tr);
            });

            if (eventsCache.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4">Brak wydarzeń.</td></tr>';
            }
        }

        document.getElementById('event-form').addEventListener('submit', function (e) {
            e.preventDefault();

            var id = document.getElementById('event-id').value;
            var fileInput = document.getElementById('event-photo');
            var file = fileInput.files[0];

            function savePayload(photoUrl) {
                var payload = {
                    title: document.getElementById('event-title').value.trim(),
                    event_date: document.getElementById('event-date').value,
                    description: document.getElementById('event-description').value.trim(),
                    photo_url: photoUrl
                };

                var url = 'api/events.php' + (id ? '?id=' + encodeURIComponent(id) : '');
                var method = id ? 'PUT' : 'POST';

                return apiRequest(url, { method: method, body: JSON.stringify(payload) });
            }

            var uploadPromise;
            if (file) {
                var formData = new FormData();
                formData.append('photo', file);
                uploadPromise = apiRequest('api/upload.php', { method: 'POST', body: formData }).then(function (res) {
                    return res.url;
                });
            } else {
                var existing = id ? eventsCache.find(function (ev) { return String(ev.id) === String(id); }) : null;
                uploadPromise = Promise.resolve(existing ? existing.photo_url : '');
            }

            uploadPromise.then(savePayload).then(function () {
                setStatus('event-status', 'Zapisano wydarzenie.', false);
                resetEventForm();
                loadEvents();
            }).catch(function (err) {
                setStatus('event-status', err.message, true);
            });
        });

        document.getElementById('event-form-cancel').addEventListener('click', resetEventForm);

        function resetEventForm() {
            document.getElementById('event-id').value = '';
            document.getElementById('event-title').value = '';
            document.getElementById('event-date').value = '';
            document.getElementById('event-description').value = '';
            document.getElementById('event-photo').value = '';
            document.getElementById('event-form-title').textContent = 'Dodaj wydarzenie';
            document.getElementById('event-form-cancel').style.display = 'none';
        }

        document.getElementById('events-table-body').addEventListener('click', function (e) {
            var btn = e.target.closest('button');
            if (!btn) return;
            var id = btn.getAttribute('data-id');
            var action = btn.getAttribute('data-action');

            if (action === 'edit-event') {
                var ev = eventsCache.find(function (item) { return String(item.id) === String(id); });
                if (!ev) return;
                document.getElementById('event-id').value = ev.id;
                document.getElementById('event-title').value = ev.title;
                document.getElementById('event-date').value = ev.event_date;
                document.getElementById('event-description').value = ev.description || '';
                document.getElementById('event-photo').value = '';
                document.getElementById('event-form-title').textContent = 'Edytuj wydarzenie';
                document.getElementById('event-form-cancel').style.display = 'inline-block';
                window.scrollTo({ top: document.getElementById('event-form').offsetTop - 20, behavior: 'smooth' });
            } else if (action === 'delete-event') {
                if (!confirm('Na pewno usunąć to wydarzenie?')) return;
                apiRequest('api/events.php?id=' + encodeURIComponent(id), { method: 'DELETE' }).then(function () {
                    setStatus('event-status', 'Usunięto wydarzenie.', false);
                    loadEvents();
                }).catch(function (err) {
                    setStatus('event-status', err.message, true);
                });
            }
        });

        // ---------- Opinions ----------

        var opinionsCache = [];

        function loadOpinions() {
            return apiRequest('api/opinions.php').then(function (data) {
                opinionsCache = data;
                renderOpinions();
            }).catch(function (err) {
                setStatus('opinion-status', err.message, true);
            });
        }

        function renderStars(rating) {
            var n = Number(rating) || 0;
            return '★★★★★☆☆☆☆☆'.slice(5 - n, 10 - n);
        }

        function renderOpinions() {
            var tbody = document.getElementById('opinions-table-body');
            tbody.innerHTML = '';

            opinionsCache.forEach(function (op) {
                var tr = document.createElement('tr');
                var isPublished = !!Number(op.is_published);

                tr.innerHTML =
                    '<td>' + escapeHtml(op.author_name) + '</td>' +
                    '<td>' + escapeHtml(op.content) + '</td>' +
                    '<td><span class="rating-stars">' + renderStars(op.rating) + '</span></td>' +
                    '<td><span class="badge ' + (isPublished ? 'published' : 'hidden') + '">' +
                        (isPublished ? 'Opublikowana' : 'Ukryta') + '</span></td>' +
                    '<td class="actions">' +
                        '<button type="button" data-action="toggle-opinion" data-id="' + op.id + '" data-published="' + (isPublished ? 1 : 0) + '">' +
                            (isPublished ? 'Ukryj' : 'Opublikuj') +
                        '</button>' +
                        '<button type="button" data-action="edit-opinion" data-id="' + op.id + '">Edytuj</button>' +
                        '<button type="button" class="danger" data-action="delete-opinion" data-id="' + op.id + '">Usuń</button>' +
                    '</td>';
                tbody.appendChild(tr);
            });

            if (opinionsCache.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5">Brak opinii.</td></tr>';
            }
        }

        document.getElementById('opinion-form').addEventListener('submit', function (e) {
            e.preventDefault();

            var id = document.getElementById('opinion-id').value;
            var payload = {
                author_name: document.getElementById('opinion-author').value.trim(),
                content: document.getElementById('opinion-content').value.trim(),
                rating: parseInt(document.getElementById('opinion-rating').value, 10),
                is_published: document.getElementById('opinion-is-published').checked ? 1 : 0
            };

            var url = 'api/opinions.php' + (id ? '?id=' + encodeURIComponent(id) : '');
            var method = id ? 'PUT' : 'POST';

            apiRequest(url, { method: method, body: JSON.stringify(payload) }).then(function () {
                setStatus('opinion-status', 'Zapisano opinię.', false);
                resetOpinionForm();
                loadOpinions();
            }).catch(function (err) {
                setStatus('opinion-status', err.message, true);
            });
        });

        document.getElementById('opinion-form-cancel').addEventListener('click', resetOpinionForm);

        function resetOpinionForm() {
            document.getElementById('opinion-id').value = '';
            document.getElementById('opinion-author').value = '';
            document.getElementById('opinion-content').value = '';
            document.getElementById('opinion-rating').value = '5';
            document.getElementById('opinion-is-published').checked = false;
            document.getElementById('opinion-form-title').textContent = 'Dodaj opinię';
            document.getElementById('opinion-form-cancel').style.display = 'none';
        }

        document.getElementById('opinions-table-body').addEventListener('click', function (e) {
            var btn = e.target.closest('button');
            if (!btn) return;
            var id = btn.getAttribute('data-id');
            var action = btn.getAttribute('data-action');

            if (action === 'edit-opinion') {
                var op = opinionsCache.find(function (item) { return String(item.id) === String(id); });
                if (!op) return;
                document.getElementById('opinion-id').value = op.id;
                document.getElementById('opinion-author').value = op.author_name;
                document.getElementById('opinion-content').value = op.content;
                document.getElementById('opinion-rating').value = op.rating;
                document.getElementById('opinion-is-published').checked = !!Number(op.is_published);
                document.getElementById('opinion-form-title').textContent = 'Edytuj opinię';
                document.getElementById('opinion-form-cancel').style.display = 'inline-block';
                window.scrollTo({ top: document.getElementById('opinion-form').offsetTop - 20, behavior: 'smooth' });
            } else if (action === 'toggle-opinion') {
                var currentlyPublished = btn.getAttribute('data-published') === '1';
                apiRequest('api/opinions.php?id=' + encodeURIComponent(id), {
                    method: 'PUT',
                    body: JSON.stringify({ is_published: currentlyPublished ? 0 : 1 })
                }).then(function () {
                    setStatus('opinion-status', 'Zaktualizowano status opinii.', false);
                    loadOpinions();
                }).catch(function (err) {
                    setStatus('opinion-status', err.message, true);
                });
            } else if (action === 'delete-opinion') {
                if (!confirm('Na pewno usunąć tę opinię?')) return;
                apiRequest('api/opinions.php?id=' + encodeURIComponent(id), { method: 'DELETE' }).then(function () {
                    setStatus('opinion-status', 'Usunięto opinię.', false);
                    loadOpinions();
                }).catch(function (err) {
                    setStatus('opinion-status', err.message, true);
                });
            }
        });

        // ---------- Settings ----------

        function loadSettings() {
            return apiRequest('api/settings.php').then(function (data) {
                document.getElementById('setting-hero-text').value = data.hero_text || '';
                document.getElementById('setting-contact-phone').value = data.contact_phone || '';
                document.getElementById('setting-contact-email').value = data.contact_email || '';
                document.getElementById('setting-opening-hours').value = data.opening_hours || '';
                document.getElementById('setting-instagram-url').value = data.instagram_url || '';
            }).catch(function (err) {
                setStatus('settings-status', err.message, true);
            });
        }

        document.getElementById('settings-form').addEventListener('submit', function (e) {
            e.preventDefault();

            var payload = {
                hero_text: document.getElementById('setting-hero-text').value.trim(),
                contact_phone: document.getElementById('setting-contact-phone').value.trim(),
                contact_email: document.getElementById('setting-contact-email').value.trim(),
                opening_hours: document.getElementById('setting-opening-hours').value.trim(),
                instagram_url: document.getElementById('setting-instagram-url').value.trim()
            };

            apiRequest('api/settings.php', { method: 'POST', body: JSON.stringify(payload) }).then(function () {
                setStatus('settings-status', 'Zapisano ustawienia.', false);
            }).catch(function (err) {
                setStatus('settings-status', err.message, true);
            });
        });

        // ---------- Init ----------

        loadCategories().then(loadProducts);
        loadGallery();
        loadEvents();
        loadOpinions();
        loadSettings();
    })();
    </script>
</body>
</html>
