<h2>Registro</h2>

<?php if (!empty($error)): ?>
    <div class="error"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST" action="<?= url('register') ?>">
    <?= csrf_field() ?>

    <label>Usuario</label><br>
    <input type="text" name="username" required>

    <br><br>

    <label>Contraseña</label><br>
    <input type="password" name="password" required>

    <br><br>

    <button type="submit">Registrarme</button>
</form>

<br>
<p>¿Tiene cuenta? <a href="<?= url('login') ?>">Inicie sesión</a></p>
