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
    <title>Panel administracyjny</title>
</head>
<body>
    <h1>Panel administracyjny</h1>
    <p><a href="logout.php">Wyloguj</a></p>
</body>
</html>
