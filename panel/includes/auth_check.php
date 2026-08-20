<?php
declare(strict_types=1);

/**
 * Wymusza autoryzację dla endpointów API. Jeśli brak zalogowanego admina,
 * przerywa wykonanie odpowiedzią 401 JSON (zamiast przekierowania,
 * bo to endpoint API a nie strona HTML).
 */
function requireAuth(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}
