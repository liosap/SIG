<?php
// Código numérico (por defecto 500)
$code = isset($code) ? (int)$code : 500;

// Mensaje y descripción con valores por defecto
$message = isset($message) && $message !== '' ? $message : 'Error del servidor';
$description = $description ?? 'Por favor, inténtalo de nuevo más tarde.';

// Saneado para evitar HTML no deseado
$message     = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');

// Asegurar que el código HTTP coincide
http_response_code($code);
?>

<h1>Error <?= $code ?></h1>
<h2><?= $message ?></h2>
<p><?= $description ?></p>

<p><a href="<?= url('/') ?>">Volver al inicio</a></p>