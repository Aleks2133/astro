<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config.php';

requireAuth();

header('Content-Type: application/json; charset=utf-8');

const SETTING_KEYS = ['hero_text', 'contact_phone', 'contact_email', 'opening_hours', 'instagram_url'];

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet($pdo);
        break;
    case 'POST':
        handlePost($pdo);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
}

function handleGet(PDO $pdo): void
{
    $stmt = $pdo->query('SELECT setting_key, setting_value FROM site_settings');
    $rows = $stmt->fetchAll();

    $result = array_fill_keys(SETTING_KEYS, '');
    foreach ($rows as $row) {
        if (in_array($row['setting_key'], SETTING_KEYS, true)) {
            $result[$row['setting_key']] = (string) $row['setting_value'];
        }
    }

    echo json_encode($result);
}

function handlePost(PDO $pdo): void
{
    $data = readJsonBody();

    $stmt = $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value) VALUES (:setting_key, :setting_value)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );

    foreach (SETTING_KEYS as $key) {
        $value = isset($data[$key]) ? trim((string) $data[$key]) : '';
        $stmt->execute([
            'setting_key' => $key,
            'setting_value' => $value,
        ]);
    }

    handleGet($pdo);
}

function readJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return $_POST;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $_POST;
}
