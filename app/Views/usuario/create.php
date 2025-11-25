<h2>Crear usuario</h2>

<?php if (!empty($error)): ?>
    <div class="error"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST" action="<?= url('usuarios') ?>">
    <?= csrf_field() ?>

    <label>Usuario</label><br>
    <input type="text" name="username" value="<?= e($username ?? '') ?>" required>

    <br><br>

    <label>Contraseña</label><br>
    <input type="password" name="password" required>

    <br><br>

    <button type="submit">Crear</button>
</form>

<p><a href="<?= url('usuarios') ?>">← Volver</a></p>
