<?php
declare(strict_types=1);

/**
 * Jednorazowy skrypt do utworzenia pierwszego konta admina.
 * UWAGA: usuń ten plik z serwera zaraz po jednorazowym użyciu!
 */

require_once __DIR__ . '/config.php';

$message = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $message = 'Podaj nazwę użytkownika i hasło.';
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $pdo = getPDO();

            $stmt = $pdo->prepare(
                'INSERT INTO admin_users (username, password_hash) VALUES (:username, :password_hash)'
            );
            $stmt->execute([
                'username' => $username,
                'password_hash' => $passwordHash,
            ]);

            $success = true;
            $message = 'Admin "' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '" został utworzony.'
                . '<br>Pamiętaj, aby usunąć ten plik (create_admin.php) z serwera!';
        } catch (PDOException $e) {
            $message = 'Błąd podczas tworzenia admina: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Utwórz konto admina</title>
</head>
<body>
    <h1>Utwórz konto admina</h1>

    <?php if ($message !== null): ?>
        <p style="color: <?= $success ? 'green' : 'red' ?>;"><?= $message ?></p>
    <?php endif; ?>

    <?php if (!$success): ?>
        <form method="post">
            <label>
                Nazwa użytkownika:
                <input type="text" name="username" required>
            </label>
            <br>
            <label>
                Hasło:
                <input type="password" name="password" required>
            </label>
            <br>
            <button type="submit">Utwórz admina</button>
        </form>
    <?php endif; ?>
</body>
</html>
