<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/uploads.php';
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
    $stmt = $pdo->query('SELECT * FROM events ORDER BY event_date DESC, id DESC');
    echo json_encode($stmt->fetchAll());
}

function handlePost(PDO $pdo): void
{
    $data = readJsonBody();
    $validated = validateEventData($data);

    if (isset($validated['error'])) {
        http_response_code(400);
        echo json_encode(['error' => $validated['error']]);
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO events (title, description, event_date, photo_url)
         VALUES (:title, :description, :event_date, :photo_url)'
    );
    $stmt->execute($validated);

    $id = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare('SELECT * FROM events WHERE id = :id');
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
    $validated = validateEventData($data);

    if (isset($validated['error'])) {
        http_response_code(400);
        echo json_encode(['error' => $validated['error']]);
        return;
    }

    $validated['id'] = $id;

    $stmt = $pdo->prepare(
        'UPDATE events SET
            title = :title,
            description = :description,
            event_date = :event_date,
            photo_url = :photo_url
         WHERE id = :id'
    );
    $stmt->execute($validated);

    $stmt = $pdo->prepare('SELECT * FROM events WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Nie znaleziono wydarzenia']);
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

    $stmt = $pdo->prepare('SELECT photo_url FROM events WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    $stmt = $pdo->prepare('DELETE FROM events WHERE id = :id');
    $stmt->execute(['id' => $id]);

    if ($row && !empty($row['photo_url'])) {
        deleteUploadedFile($row['photo_url']);
    }

    echo json_encode(['success' => true]);
}

/**
 * Waliduje i normalizuje dane wydarzenia z requestu.
 * Zwraca tablicę parametrów gotowych do bindowania albo ['error' => ...].
 */
function validateEventData(array $data): array
{
    $title = trim((string) ($data['title'] ?? ''));
    if ($title === '') {
        return ['error' => 'Pole title jest wymagane'];
    }

    $eventDate = trim((string) ($data['event_date'] ?? ''));
    if ($eventDate === '' || !isValidDate($eventDate)) {
        return ['error' => 'Pole event_date jest wymagane i musi być prawidłową datą (YYYY-MM-DD)'];
    }

    $description = isset($data['description']) ? trim((string) $data['description']) : '';
    $photoUrl = isset($data['photo_url']) ? trim((string) $data['photo_url']) : '';

    return [
        'title' => $title,
        'description' => $description,
        'event_date' => $eventDate,
        'photo_url' => $photoUrl,
    ];
}

function isValidDate(string $date): bool
{
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
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
