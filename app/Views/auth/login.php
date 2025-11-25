<h2>Iniciar sesión</h2>

<?php if (!empty($error)): ?>
    <div class="error"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST" action="<?= url('login') ?>">
    <?= csrf_field() ?>

    <label>Usuario</label><br>
    <input type="text" name="username">

    <br><br>

    <label>Contraseña</label><br>
    <input type="password" name="password">

    <br><br>

    <button type="submit">Ingresar</button>
</form>

<br>
<p>¿No tiene cuenta? <a href="<?= url('register') ?>">Registrese</a></p>
