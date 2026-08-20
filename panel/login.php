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
    <title>Logowanie — Panel administracyjny</title>
</head>
<body>
    <h1>Logowanie</h1>

    <?php if ($error !== ''): ?>
        <p style="color: red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label>
            Login:
            <input type="text" name="username" required>
        </label>
        <br>
        <label>
            Hasło:
            <input type="password" name="password" required>
        </label>
        <br>
        <button type="submit">Zaloguj</button>
    </form>
</body>
</html>
