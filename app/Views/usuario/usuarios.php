<h1>Usuarios</h1>

<ul>
<?php foreach ($users as $u) : ?>
    <li>
        <?= e($u['Username']) ?> - 
        <a href="<?= url('usuarios/' . (int)$u['ID_Usuario']) ?>">ver</a>
    </li>
<?php endforeach; ?>
</ul>
