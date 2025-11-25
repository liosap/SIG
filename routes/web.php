<?php
declare(strict_types=1);

return [

    // --------------------------------
    // Home
    // --------------------------------
    ['GET', '/', 'HomeController@index', []],


    // --------------------------------
    // Autenticación
    // --------------------------------
    ['GET',  '/login',    'AuthController@showLogin', []],
    ['POST', '/login',    'AuthController@login',     ['csrf']],
    ['GET',  '/logout',   'AuthController@logout',    ['auth']],

    ['GET',  '/register', 'AuthController@showRegister', []],
    ['POST', '/register', 'AuthController@register',     ['csrf']],


    // --------------------------------
    // Dashboard del usuario
    // --------------------------------
    ['GET', '/dashboard', 'UsuarioController@dashboard', ['auth']],


    // --------------------------------
    // CRUD Usuarios
    // --------------------------------

    // Listado
    ['GET', '/usuarios', 'UsuarioController@index', ['auth']],

    // Crear usuario (vista)
    ['GET', '/usuarios/create', 'UsuarioController@create', ['auth']],

    // Guardar usuario nuevo
    ['POST', '/usuarios', 'UsuarioController@store', ['auth', 'csrf']],

    // Ver usuario
    ['GET', '/usuarios/{id:int}', 'UsuarioController@show', ['auth']],

    // Editar usuario (vista)
    ['GET', '/usuarios/{id:int}/edit', 'UsuarioController@edit', ['auth']],

    // Guardar cambios
    ['POST', '/usuarios/{id:int}/update', 'UsuarioController@update', ['auth', 'csrf']],

    // -------------------------
    // Cambio de contraseña
    // -------------------------
    ['GET',  '/usuarios/{id:int}/password', 'UsuarioController@changePasswordForm', ['auth']],
    ['POST', '/usuarios/{id:int}/password', 'UsuarioController@changePassword',     ['auth', 'csrf']],


    // -------------------------
    // Activar / Desactivar
    // -------------------------
    ['POST', '/usuarios/{id:int}/desactivar', 'UsuarioController@deactivate', ['auth', 'csrf']],
    ['POST', '/usuarios/{id:int}/activar',    'UsuarioController@activate',    ['auth', 'csrf']],
];
