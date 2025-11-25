<h2>Editar usuario</h2>

<?php if (!empty($error)): ?>
    <div class="error"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST" action="<?= url("usuarios/{$user['ID_Usuario']}/update") ?>">
    <?= csrf_field() ?>

    <label>Usuario</label><br>
    <input type="text" name="username" value="<?= e($user['Username']) ?>" required>

    <br><br>

    <button type="submit">Guardar cambios</button>
</form>

<p><a href="<?= url("usuarios/{$user['ID_Usuario']}") ?>">← Volver</a></p>
