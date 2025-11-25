<h2>Cambiar contraseña</h2>

<?php if (!empty($error)): ?>
    <div class="error"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST" action="<?= url("usuarios/{$user['ID_Usuario']}/password") ?>">
    <?= csrf_field() ?>

    <label>Nueva contraseña</label><br>
    <input type="password" name="password" required>

    <br><br>

    <label>Repetir contraseña</label><br>
    <input type="password" name="password2" required>

    <br><br>

    <button type="submit">Actualizar</button>
</form>

<p><a href="<?= url("usuarios/{$user['ID_Usuario']}") ?>">← Volver</a></p>
