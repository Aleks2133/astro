<?php
declare(strict_types=1);

/**
 * Wymusza autoryzację dla endpointów API. Jeśli brak zalogowanego admina,
 * przerywa wykonanie odpowiedzią 401 JSON (zamiast przekierowania,
 * bo to endpoint API a nie strona HTML).
 */
function requireAuth(): void
{
    if (!isAdminLoggedIn()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

/**
 * Sprawdza, czy istnieje aktywna sesja zalogowanego admina panelu,
 * bez przerywania wykonania (w przeciwieństwie do requireAuth()).
 */
function isAdminLoggedIn(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return isset($_SESSION['admin_id']);
}
