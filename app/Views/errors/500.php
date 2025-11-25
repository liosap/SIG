<?php
$code = 500;

$message = (isset($message) && $message !== '')
    ? $message
    : 'Error interno del servidor';

$description = $description ?? 'El servidor encontró un error inesperado.';

include __DIR__ . '/error-template.php';