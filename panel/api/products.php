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
    $categoryId = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);

    $sql = 'SELECT p.*, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id';
    $params = [];

    if ($categoryId) {
        $sql .= ' WHERE p.category_id = :category_id';
        $params['category_id'] = $categoryId;
    }

    $sql .= ' ORDER BY p.sort_order ASC, p.id ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll());
}

function handlePost(PDO $pdo): void
{
    $data = readJsonBody();
    $validated = validateProductData($data);

    if (isset($validated['error'])) {
        http_response_code(400);
        echo json_encode(['error' => $validated['error']]);
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO products (category_id, name, description, price, photo_url, is_available, sort_order)
         VALUES (:category_id, :name, :description, :price, :photo_url, :is_available, :sort_order)'
    );
    $stmt->execute($validated);

    $id = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = :id'
    );
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
    $validated = validateProductData($data);

    if (isset($validated['error'])) {
        http_response_code(400);
        echo json_encode(['error' => $validated['error']]);
        return;
    }

    $validated['id'] = $id;

    $stmt = $pdo->prepare(
        'UPDATE products SET
            category_id = :category_id,
            name = :name,
            description = :description,
            price = :price,
            photo_url = :photo_url,
            is_available = :is_available,
            sort_order = :sort_order
         WHERE id = :id'
    );
    $stmt->execute($validated);

    $stmt = $pdo->prepare(
        'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Nie znaleziono produktu']);
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

    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute(['id' => $id]);

    echo json_encode(['success' => true]);
}

/**
 * Waliduje i normalizuje dane produktu z requestu.
 * Zwraca tablicę parametrów gotowych do bindowania albo ['error' => ...].
 */
function validateProductData(array $data): array
{
    $name = trim((string) ($data['name'] ?? ''));
    if ($name === '') {
        return ['error' => 'Pole name jest wymagane'];
    }

    if (!isset($data['price']) || !is_numeric($data['price'])) {
        return ['error' => 'Pole price musi być liczbą'];
    }
    $price = (float) $data['price'];
    if ($price < 0) {
        return ['error' => 'Pole price nie może być ujemne'];
    }

    $categoryId = isset($data['category_id']) && $data['category_id'] !== ''
        ? (int) $data['category_id']
        : null;

    $description = isset($data['description']) ? trim((string) $data['description']) : '';
    $photoUrl = isset($data['photo_url']) ? trim((string) $data['photo_url']) : '';
    $isAvailable = !empty($data['is_available']) ? 1 : 0;
    $sortOrder = isset($data['sort_order']) ? (int) $data['sort_order'] : 0;

    return [
        'category_id' => $categoryId,
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'photo_url' => $photoUrl,
        'is_available' => $isAvailable,
        'sort_order' => $sortOrder,
    ];
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
