<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config.php';

requireAuth();

header('Content-Type: application/json; charset=utf-8');

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];

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
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY sort_order ASC, id ASC');
    echo json_encode($stmt->fetchAll());
}

function handlePost(PDO $pdo): void
{
    $data = readJsonBody();

    $name = trim((string) ($data['name'] ?? ''));
    if ($name === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Pole name jest wymagane']);
        return;
    }

    $sortOrder = isset($data['sort_order']) ? (int) $data['sort_order'] : 0;

    $stmt = $pdo->prepare('INSERT INTO categories (name, sort_order) VALUES (:name, :sort_order)');
    $stmt->execute([
        'name' => $name,
        'sort_order' => $sortOrder,
    ]);

    $id = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = :id');
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

    $name = trim((string) ($data['name'] ?? ''));
    if ($name === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Pole name jest wymagane']);
        return;
    }

    $sortOrder = isset($data['sort_order']) ? (int) $data['sort_order'] : 0;

    $stmt = $pdo->prepare('UPDATE categories SET name = :name, sort_order = :sort_order WHERE id = :id');
    $stmt->execute([
        'name' => $name,
        'sort_order' => $sortOrder,
        'id' => $id,
    ]);

    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Nie znaleziono kategorii']);
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

    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
    $stmt->execute(['id' => $id]);

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
