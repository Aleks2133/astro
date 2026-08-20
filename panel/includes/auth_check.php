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

/**
 * Zwalnia blokadę pliku sesji, jeśli sesja jest otwarta.
 *
 * PHP trzyma wyłączną blokadę pliku sesji od session_start() aż do końca
 * requestu, przez co równoległe żądania tego samego klienta ustawiają się
 * w kolejce. Publiczne odczyty w API tylko sprawdzają flagę admina i nic do
 * sesji nie zapisują, więc mogą oddać blokadę od razu po tym sprawdzeniu.
 *
 * Po wywołaniu $_SESSION pozostaje czytelne, ale dalsze zapisy nie będą
 * utrwalone — stąd wyłącznie dla ścieżek tylko do odczytu.
 *
 * Zamyka sesję tylko gdy jest aktywna: session_write_close() na nieaktywnej
 * sesji emituje warning, który zepsułby odpowiedź JSON.
 */
function releaseSessionLock(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
}
