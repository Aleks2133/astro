<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/uploads.php';
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') {
    requireAuth();
}

header('Content-Type: application/json; charset=utf-8');

$pdo = getPDO();

switch ($method) {
    case 'GET':
        handleGet($pdo);
        break;
    case 'POST':
        handlePost($pdo);
        break;
    case 'PUT':
        handlePut($pdo);
        break;
    case 'DELETE':
        handleDelete($pdo);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
}

function handleGet(PDO $pdo): void
{
    $stmt = $pdo->query('SELECT * FROM gallery ORDER BY sort_order ASC, id ASC');
    echo json_encode($stmt->fetchAll());
}

function handlePost(PDO $pdo): void
{
    $data = readJsonBody();

    $photoUrl = trim((string) ($data['photo_url'] ?? ''));
    if ($photoUrl === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Pole photo_url jest wymagane']);
        return;
    }

    $caption = isset($data['caption']) ? trim((string) $data['caption']) : '';
    $sortOrder = isset($data['sort_order']) ? (int) $data['sort_order'] : 0;

    $stmt = $pdo->prepare('INSERT INTO gallery (photo_url, caption, sort_order) VALUES (:photo_url, :caption, :sort_order)');
    $stmt->execute([
        'photo_url' => $photoUrl,
        'caption' => $caption,
        'sort_order' => $sortOrder,
    ]);

    $id = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare('SELECT * FROM gallery WHERE id = :id');
    $stmt->execute(['id' => $id]);
    echo json_encode($stmt->fetch());
}

function handlePut(PDO $pdo): void
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Nieprawidłowy parametr id']);
        return;
    }

    $data = readJsonBody();

    $photoUrl = trim((string) ($data['photo_url'] ?? ''));
    if ($photoUrl === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Pole photo_url jest wymagane']);
        return;
    }

    $caption = isset($data['caption']) ? trim((string) $data['caption']) : '';
    $sortOrder = isset($data['sort_order']) ? (int) $data['sort_order'] : 0;

    $stmt = $pdo->prepare('UPDATE gallery SET photo_url = :photo_url, caption = :caption, sort_order = :sort_order WHERE id = :id');
    $stmt->execute([
        'photo_url' => $photoUrl,
        'caption' => $caption,
        'sort_order' => $sortOrder,
        'id' => $id,
    ]);

    $stmt = $pdo->prepare('SELECT * FROM gallery WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Nie znaleziono wpisu galerii']);
        return;
    }

    echo json_encode($row);
}

function handleDelete(PDO $pdo): void
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Nieprawidłowy parametr id']);
        return;
    }

    $stmt = $pdo->prepare('SELECT photo_url FROM gallery WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    $stmt = $pdo->prepare('DELETE FROM gallery WHERE id = :id');
    $stmt->execute(['id' => $id]);

    if ($row && !empty($row['photo_url'])) {
        deleteUploadedFile($row['photo_url']);
    }

    echo json_encode(['success' => true]);
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
