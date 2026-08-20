<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';

requireAuth();

header('Content-Type: application/json; charset=utf-8');

const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

const ALLOWED_MIME_TYPES = [
    'image/jpeg' => ['jpg', 'jpeg'],
    'image/png' => ['png'],
    'image/webp' => ['webp'],
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

if (!isset($_FILES['photo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Brak pliku photo']);
    exit;
}

$file = $_FILES['photo'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Błąd przesyłania pliku']);
    exit;
}

if ($file['size'] > MAX_FILE_SIZE) {
    http_response_code(400);
    echo json_encode(['error' => 'Plik jest zbyt duży (limit 5MB)']);
    exit;
}

$originalName = (string) $file['name'];
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($extension, ALLOWED_EXTENSIONS, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Niedozwolone rozszerzenie pliku']);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

if ($mimeType === false || !isset(ALLOWED_MIME_TYPES[$mimeType])) {
    http_response_code(400);
    echo json_encode(['error' => 'Plik nie jest prawidłowym obrazem']);
    exit;
}

if (!in_array($extension, ALLOWED_MIME_TYPES[$mimeType], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Rozszerzenie pliku nie zgadza się z jego rzeczywistym typem']);
    exit;
}

$uploadsDir = __DIR__ . '/../uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

$fileName = bin2hex(random_bytes(16)) . '.' . $extension;
$destination = $uploadsDir . '/' . $fileName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    http_response_code(500);
    echo json_encode(['error' => 'Nie udało się zapisać pliku']);
    exit;
}

echo json_encode(['url' => '/panel/uploads/' . $fileName]);
