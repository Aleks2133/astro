<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($username !== '' && $password !== '') {
        $pdo = getPDO();

        $stmt = $pdo->prepare('SELECT id, password_hash FROM admin_users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            header('Location: index.php');
            exit;
        }
    }

    $error = 'Nieprawidłowy login lub hasło';
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logowanie — Panel administracyjny</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500..600&family=Manrope:wght@400..700&display=swap" rel="stylesheet">
    <style>
        :root {
            --matcha-100: #e6f0d8;
            --matcha-500: #7ba64c;
            --matcha-600: #5f8639;
            --matcha-700: #48682e;
            --matcha-800: #354d26;
            --matcha-900: #24361c;
            --cream-50: #fffdf8;
            --cream-100: #fbf7ee;
            --clay-600: #8a7a63;
            --red-500: #dc2626;
            --red-50: #fef2f2;
            --font-display: 'Fraunces', Georgia, serif;
            --font-sans: 'Manrope', system-ui, -apple-system, 'Segoe UI', sans-serif;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: radial-gradient(circle at 15% 15%, var(--matcha-100), transparent 55%), var(--cream-100);
            font-family: var(--font-sans);
            color: var(--matcha-900);
            -webkit-font-smoothing: antialiased;
        }

        .login-card {
            width: 100%;
            max-width: 380px;
            background: var(--cream-50);
            border: 1px solid rgba(53, 77, 38, 0.08);
            border-radius: 18px;
            box-shadow: 0 24px 60px -24px rgba(36, 54, 28, 0.35);
            padding: 36px 32px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 26px;
        }

        .brand-mark {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            border-radius: 999px;
            background: var(--matcha-500);
            color: var(--cream-50);
            flex-shrink: 0;
        }

        .brand-text strong {
            display: block;
            font-family: var(--font-display);
            font-size: 1.05rem;
            line-height: 1.2;
        }

        .brand-text span {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--clay-600);
        }

        h1 {
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 1.5rem;
            margin: 0 0 6px;
        }

        .subtitle {
            margin: 0 0 24px;
            font-size: 14px;
            color: var(--clay-600);
        }

        .error-msg {
            background: var(--red-50);
            color: var(--red-500);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13.5px;
            font-weight: 600;
            margin: 0 0 20px;
        }

        form { display: flex; flex-direction: column; gap: 16px; }

        label {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--matcha-800);
        }

        input {
            padding: 11px 13px;
            border: 1px solid rgba(53, 77, 38, 0.18);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            background: var(--cream-50);
            color: var(--matcha-900);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        input:hover { border-color: rgba(53, 77, 38, 0.32); }

        input:focus {
            outline: none;
            border-color: var(--matcha-500);
            box-shadow: 0 0 0 3px rgba(123, 166, 76, 0.18);
        }

        button {
            margin-top: 4px;
            padding: 12px 18px;
            border: none;
            border-radius: 999px;
            background: var(--matcha-700);
            color: var(--cream-50);
            cursor: pointer;
            font-size: 14.5px;
            font-weight: 700;
            font-family: inherit;
            transition: background-color 0.15s ease, transform 0.15s ease;
        }

        button:hover { background: var(--matcha-600); transform: translateY(-1px); }
        button:active { transform: translateY(0); }

        :focus-visible {
            outline: 2px solid var(--matcha-600);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M4 9h13a3 3 0 0 1 0 6h-1" stroke-linecap="round" />
                    <path d="M4 9v3a5 5 0 0 0 5 5h3a5 5 0 0 0 5-5V9" stroke-linecap="round" />
                </svg>
            </span>
            <span class="brand-text">
                <strong>Pani Matcha</strong>
                <span>Panel administracyjny</span>
            </span>
        </div>

        <h1>Logowanie</h1>
        <p class="subtitle">Zaloguj się, aby zarządzać treścią strony.</p>

        <?php if ($error !== ''): ?>
            <p class="error-msg"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label>
                Login
                <input type="text" name="username" required autocomplete="username">
            </label>
            <label>
                Hasło
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button type="submit">Zaloguj</button>
        </form>
    </div>
</body>
</html>
