<?php
// Determinar si hay usuario autenticado
$isAuthenticated = !empty($_SESSION['ID_Usuario'] ?? null);
$currentUsername = $_SESSION['Username'] ?? null;

// Mensajes flash (se consumen una sola vez)
$flashSuccess = flash('success');
$flashError   = flash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= e($title ?? 'SIG - Sistema Integral de Gestión') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Estilos mínimos (placeholder) -->
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; }

        header { background: #222; color: #fff; padding: 0.75rem 1.5rem; }
        nav { display: flex; align-items: center; gap: 1rem; }
        nav a { color: #fff; text-decoration: none; font-size: .95rem; }
        nav a:hover { text-decoration: underline; }
        nav .spacer { flex: 1; }
        nav .username { font-weight: bold; opacity: .8; }

        .container { padding: 2rem; }

        .flash {
            padding: .75rem 1rem;
            border-radius: 4px;
            margin: 0 0 1rem 0;
            font-size: .9rem;
        }
        .flash-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .flash-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .error { color: #b00; margin-bottom: 1rem; }
        .success { color: #080; margin-bottom: 1rem; }

        input, button { padding: .5rem; margin: .25rem 0; }
        table { border-collapse: collapse; width: 100%; max-width: 800px; }
        table th, table td { padding: .5rem; border-bottom: 1px solid #ddd; text-align: left; }
    </style>
</head>

    <body>
        <header>
            <nav>
                <?php if ($isAuthenticated): ?>
                    <a href="<?= url('dashboard') ?>">Escritorio</a>
                    <a href="<?= url('usuarios') ?>">Usuarios</a>

                    <span class="spacer"></span>
                    <?php if ($currentUsername): ?>
                        <span class="username">Hola, <?= e($currentUsername) ?></span>
                    <?php endif; ?>
                    <a href="<?= url('logout') ?>">Salir</a>
                <?php else: ?>
                    <a href="<?= url('/') ?>">Inicio</a>

                    <span class="spacer"></span>
                    <a href="<?= url('login') ?>">Iniciar sesión</a>
                    <a href="<?= url('register') ?>">Registrarse</a>
                <?php endif; ?>
            </nav>
        </header>

        <div class="container">
            <?php if ($flashSuccess): ?>
                <div class="flash flash-success"><?= e($flashSuccess) ?></div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="flash flash-error"><?= e($flashError) ?></div>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </body>
</html>