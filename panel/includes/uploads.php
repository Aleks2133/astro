<?php
declare(strict_types=1);

/**
 * Usuwa fizyczny plik z panel/uploads/ na podstawie zapisanego photo_url.
 * Akceptuje tylko pliki faktycznie leżące w uploads/, żeby uniknąć path traversal.
 */
function deleteUploadedFile(string $photoUrl): void
{
    $fileName = basename($photoUrl);
    $uploadsDir = realpath(__DIR__ . '/../uploads');
    if ($uploadsDir === false) {
        return;
    }

    $path = $uploadsDir . '/' . $fileName;
    $realPath = realpath($path);

    if ($realPath !== false && strpos($realPath, $uploadsDir) === 0 && is_file($realPath)) {
        unlink($realPath);
    }
}
