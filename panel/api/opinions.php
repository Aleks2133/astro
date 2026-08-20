<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
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
    if (isAdminLoggedIn()) {
        $stmt = $pdo->query('SELECT * FROM opinions ORDER BY created_at DESC, id DESC');
    } else {
        $stmt = $pdo->query('SELECT * FROM opinions WHERE is_published = 1 ORDER BY created_at DESC, id DESC');
    }
    echo json_encode($stmt->fetchAll());
}

function handlePost(PDO $pdo): void
{
    $data = readJsonBody();

    $authorName = trim((string) ($data['author_name'] ?? ''));
    if ($authorName === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Pole author_name jest wymagane']);
        return;
    }

    $content = trim((string) ($data['content'] ?? ''));
    if ($content === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Pole content jest wymagane']);
        return;
    }

    $rating = validateRating($data['rating'] ?? null);
    if ($rating === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Pole rating musi być liczbą całkowitą 1-5']);
        return;
    }

    $isPublished = !empty($data['is_published']) ? 1 : 0;

    $stmt = $pdo->prepare(
        'INSERT INTO opinions (author_name, content, rating, is_published, created_at)
         VALUES (:author_name, :content, :rating, :is_published, NOW())'
    );
    $stmt->execute([
        'author_name' => $authorName,
        'content' => $content,
        'rating' => $rating,
        'is_published' => $isPublished,
    ]);

    $id = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare('SELECT * FROM opinions WHERE id = :id');
    $stmt->execute(['id' => $id]);
    echo json_encode($stmt->fetch());
}

/**
 * Obsługuje zarówno pełną edycję opinii, jak i szybkie przełączenie
 * is_published — brakujące pola są uzupełniane z istniejącego wiersza,
 * więc do samej zmiany statusu wystarczy przesłać tylko is_published.
 */
function handlePut(PDO $pdo): void
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Nieprawidłowy parametr id']);
        return;
    }

    $stmt = $pdo->prepare('SELECT * FROM opinions WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        http_response_code(404);
        echo json_encode(['error' => 'Nie znaleziono opinii']);
        return;
    }

    $data = readJsonBody();

    $authorName = isset($data['author_name']) ? trim((string) $data['author_name']) : (string) $existing['author_name'];
    if ($authorName === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Pole author_name jest wymagane']);
        return;
    }

    $content = isset($data['content']) ? trim((string) $data['content']) : (string) $existing['content'];
    if ($content === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Pole content jest wymagane']);
        return;
    }

    if (array_key_exists('rating', $data)) {
        $rating = validateRating($data['rating']);
        if ($rating === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Pole rating musi być liczbą całkowitą 1-5']);
            return;
        }
    } else {
        $rating = (int) $existing['rating'];
    }

    $isPublished = array_key_exists('is_published', $data)
        ? (!empty($data['is_published']) ? 1 : 0)
        : (int) $existing['is_published'];

    $stmt = $pdo->prepare(
        'UPDATE opinions SET
            author_name = :author_name,
            content = :content,
            rating = :rating,
            is_published = :is_published
         WHERE id = :id'
    );
    $stmt->execute([
        'author_name' => $authorName,
        'content' => $content,
        'rating' => $rating,
        'is_published' => $isPublished,
        'id' => $id,
    ]);

    $stmt = $pdo->prepare('SELECT * FROM opinions WHERE id = :id');
    $stmt->execute(['id' => $id]);
    echo json_encode($stmt->fetch());
}

function handleDelete(PDO $pdo): void
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Nieprawidłowy parametr id']);
        return;
    }

    $stmt = $pdo->prepare('DELETE FROM opinions WHERE id = :id');
    $stmt->execute(['id' => $id]);

    echo json_encode(['success' => true]);
}

function validateRating($value): ?int
{
    if (!is_numeric($value)) {
        return null;
    }
    $floatValue = (float) $value;
    if ($floatValue != (int) $floatValue) {
        return null;
    }
    $rating = (int) $floatValue;
    if ($rating < 1 || $rating > 5) {
        return null;
    }
    return $rating;
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
