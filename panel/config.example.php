<?php
declare(strict_types=1);

// Dane połączenia z bazą danych MySQL
define('DB_HOST', 'aleksw1711.mysql.dhosting.pl');
define('DB_NAME', 'auy9mu_panimatc');
define('DB_USER', 'uom3oe_panimatc');
define('DB_PASS', 'TU_WSTAW_HASLO'); // TODO: uzupełnić ręcznie
define('DB_PORT', '3306');

/**
 * Zwraca połączenie PDO do bazy danych.
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $pdo;
}
