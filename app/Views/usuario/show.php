<h2>Usuario #<?= e($user['ID_Usuario']) ?></h2>

<p><strong>Username:</strong> <?= e($user['Username']) ?></p>
<p><strong>Activo:</strong> <?= $user['Activo'] ? 'Sí' : 'No' ?></p>

<p>
    <a href="<?= url("usuarios/{$user['ID_Usuario']}/edit") ?>">Editar</a> |
    <a href="<?= url("usuarios/{$user['ID_Usuario']}/password") ?>">Cambiar contraseña</a>
</p>

<form method="POST" action="<?= url("usuarios/{$user['ID_Usuario']}/" . ($user['Activo'] ? 'desactivar' : 'activar')) ?>">
    <?= csrf_field() ?>
    <button type="submit">
        <?= $user['Activo'] ? 'Desactivar' : 'Activar' ?>
    </button>
</form>

<p><a href="<?= url('usuarios') ?>">← Volver</a></p>