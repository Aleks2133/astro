<?php
declare(strict_types=1);

/**
 * Jednorazowy skrypt do utworzenia pierwszego konta admina.
 * UWAGA: usuń ten plik z serwera zaraz po jednorazowym użyciu!
 */

require_once __DIR__ . '/config.php';

// --- Dane podawane przy uruchomieniu, nigdy nie zapisuj ich w kodzie ---
// Uruchom np.: ADMIN_USERNAME=admin ADMIN_PASSWORD='silne_haslo' php create_admin.php
$username = getenv('ADMIN_USERNAME') ?: 'admin';
$password = getenv('ADMIN_PASSWORD');

if (!$password) {
    fwrite(STDERR, "Ustaw zmienną środowiskową ADMIN_PASSWORD przed uruchomieniem skryptu.\n");
    exit(1);
}
// -------------------------------------------------------------------

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

    echo 'Admin "' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '" został utworzony.';
    echo '<br>Pamiętaj, aby usunąć ten plik (create_admin.php) z serwera!';
} catch (PDOException $e) {
    echo 'Błąd podczas tworzenia admina: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
