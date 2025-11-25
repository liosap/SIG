<?php
$code = 404;

// Si viene un mensaje desde Response::error, úsalo; si no, uno por defecto.
$message = (isset($message) && $message !== '')
    ? $message
    : 'Página no encontrada';

$description = $description ?? 'La página que buscas no existe o fue movida.';

include __DIR__ . '/error-template.php';