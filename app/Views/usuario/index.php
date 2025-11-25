<h1>Usuarios</h1>

<a class="btn btn-primary" href="<?= url('usuarios/create') ?>">+ Crear Usuario</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Estado</th>
            <th>Ultimo acceso</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($users)): ?>
        <tr>
            <td colspan="5">No hay usuarios registrados.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($users as $user): ?> 
            <tr>
                <td><?= e($user['ID_Usuario']) ?></td>
                <td><?= e($user['Username']) ?></td>
                <td>
                    <?php if ($user['Activo']): ?>
                        <span style="color: green">Activo</span>
                    <?php else: ?>
                        <span style="color: red;">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($user['FechaUltimaAutenticacion']): ?>
                        <?= e(date('d/m/Y H:i', strtotime($user['FechaUltimaAutenticacion']))) ?>
                    <?php else: ?>
                        Nunca
                    <?php endif; ?>
                <td>
                    <a href="<?= url('usuarios/' . $user['ID_Usuario']) ?>">Ver</a>
                    <a href="<?= url('usuarios/' . $user['ID_Usuario'] . '/edit') ?>">Editar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<p><a href="<?= url('dashboard') ?>">&larr; Volver al Escritorio</a></p>