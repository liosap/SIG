# SIG – Sistema Integral de Gestión

**Documentación Técnica – Fase 1**

## 1. Introducción

El proyecto **SIG – Sistema Integral de Gestión** está desarrollado en **PHP 8.2+**, sin framework, utilizando una arquitectura tipo **MVC** con capas claramente separadas:

* **Controllers:** Orquestan la lógica a partir de la petición.
* **Services:** Contienen reglas de negocio y validaciones de alto nivel.
* **Repositories:** Gestionan el acceso a la base de datos.
* **Middlewares:** Ejecutan filtros antes de los controladores (auth, CSRF).
* **Validation:** Sistema propio de validación.
* **View/Views:** Plantillas y vistas HTML/PHP.
* **Core:** Router, Request, Response y clase Application.

El objetivo es que cualquier desarrollador pueda entender fácilmente la estructura y ampliar el sistema.

---

## 2. Estructura de carpetas y archivos

```
📁 sig
│
├── 📁 app
│   │
│   ├── 📁 Controllers
│   │   ├── 📄 AuthController.php
│   │   ├── 📄 HomeController.php
│   │   └── 📄 UsuarioController.php
│   │
│   ├── 📁 Helpers
│   │   ├── 📄 Csrf.php
│   │   └── 📄 helpers.php
│   │
│   ├── 📁 Middlewares
│   │   ├── 📄 AuthMiddleware.php
│   │   └── 📄 CsrfMiddleware.php
│   │
│   ├── 📁 Models
│   │   └── 📄 Usuario.php
│   │
│   ├── 📁 Repositories
│   │   └── 📄 UsuarioRepository.php
│   │
│   ├── 📁 Services
│   │   └── 📄 UsuarioService.php
│   │
│   ├── 📁 Validation
│   │   ├── 📄 RuleInterface.php
│   │   ├── 📄 ValidationException.php
│   │   ├── 📄 Validator.php
│   │   └── 📁 Rules
│   │       ├── 📄 AlphaNumRule.php
│   │       ├── 📄 MaxRule.php
│   │       ├── 📄 MinRule.php
│   │       └── 📄 RequiredRule.php
│   │
│   ├── 📁 View
│   │   └── 📄 View.php
│   │
│   └── 📁 Views
│       ├── 📁 auth
│       │   ├── 📄 login.php
│       │   └── 📄 register.php
│       │
│       ├── 📁 errors
│       │   ├── 📄 404.php
│       │   ├── 📄 500.php
│       │   └── 📄 error-template.php
│       │
│       ├── 📁 home
│       │   └── 📄 index.php
│       │
│       ├── 📁 layouts
│       │   └── 📄 main.php
│       │
│       └── 📁 usuario
│           ├── 📄 change_password.php
│           ├── 📄 create.php
│           ├── 📄 dashboard.php
│           ├── 📄 edit.php
│           ├── 📄 index.php
│           ├── 📄 show.php
│           └── 📄 usuarios.php
│
├── 📁 bootstrap
│   ├── 📄 app.php
│   └── 📁 logs
│       └── 📄 app.log
│
├── 📁 config
│   ├── 📄 container.php
│   └── 📄 settings.php
│
├── 📁 core
│   ├── 📄 Application.php
│   ├── 📄 Router.php
│   └── 📁 Http
│       ├── 📄 Request.php
│       └── 📄 Response.php
│
├── 📁 public
│   └── 📄 index.php
│
├── 📁 routes
│   └── 📄 web.php
│
├── 📄 .env
├── 📄 .htaccess
├── 📄 arbol.txt
├── 📄 composer.json
└── 📄 composer.lock
```

---

## 3. Flujo general de la aplicación

1. El navegador solicita una URL (ej: `/login`).
2. Apache dirige todo a `public/index.php`.
3. `index.php`:

   * Carga autoload.
   * Carga `.env`.
   * Configura sesión y cabeceras de seguridad.
   * Ejecuta `bootstrap/app.php`.
4. `bootstrap/app.php`:

   * Construye el contenedor DI.
   * Carga rutas.
   * Registra handlers globales.
   * Devuelve una instancia de `Application`.
5. `Application->run()`:

   * Crea un `Request`.
   * Envía la petición al `Router`.
6. `Router`:

   * Encuentra la ruta.
   * Ejecuta middlewares.
   * Ejecuta el controlador.
7. El controlador:

   * Usa Services y Repositories.
   * Retorna vista, JSON o redirect.
8. `View` renderiza HTML final.

---

## 4. Punto de entrada y Bootstrap

### 4.1 `public/index.php`

Realiza:

* Definición de `BASE_PATH`
* Carga de autoload de Composer
* Carga de variables desde `.env`
* Configuración de sesión y seguridad
* Envío de cabeceras seguras (CSP, X-Frame-Options…)
* Modo debug
* Arranque de la aplicación

### 4.2 `bootstrap/app.php`

* Construye contenedor DI
* Carga rutas
* Registra manejadores globales
* Instancia `Application`

---

## 5. Configuración e Inyección de Dependencias

### 5.1 `config/settings.php`

Incluye:

* Configuración del entorno
* Configuración de la base de datos

### 5.2 `config/container.php`

Registra:

* Autowiring
* Settings
* Conexión PDO
* Logger (Monolog)
* Repositories
* Services
* Controllers

---

## 6. Núcleo: Application, Router, Request, Response

### 6.1 `Application.php`

* Recibe contenedor y rutas.
* Registra rutas en el router.
* Ejecuta la aplicación.

### 6.2 `Router.php`

* Guarda rutas GET/POST.
* Soporta middlewares.
* Soporta parámetros tipados `{id:int}`.
* Resuelve controladores dinámicamente.

### 6.3 `Request.php`

Modela la petición HTTP. Incluye:

* Método, URI, headers, session.
* Métodos helper: `input()`, `all()`, `isAjax()`, `acceptsJson()`.

### 6.4 `Response.php`

Provee:

* `json()`
* `redirect()`
* `view()`
* `error()`

---

## 7. Helpers y CSRF

### 7.1 Helpers

* `e()` → escape HTML
* `url()` → generar URLs
* `csrf_field()` → campo hidden
* `flash()` → mensajes flash

### 7.2 CSRF

* Generación y validación de tokens utilizando la sesión.

---

## 8. Middlewares

### AuthMiddleware

* Verifica usuario autenticado.
* Devuelve JSON o redirige según tipo de petición.

### CsrfMiddleware

* Valida token CSRF en peticiones POST.

---

## 9. Usuario: Modelo, Repositorio y Servicio

### Modelo `Usuario`

Representa un registro de usuario.

### `UsuarioRepository`

Acceso a BD:

* Búsquedas.
* Creación.
* Actualización.
* Funciones de seguridad antifuerza bruta.

### `UsuarioService`

Reglas de negocio:

* Registro
* Autenticación
* Cambio de contraseña
* Activación/desactivación

---

## 10. Controladores

### HomeController

* Muestra portada o redirige al dashboard.

### AuthController

* Login, logout, registro.
* Uso de CSRF, sesiones y flashes.

### UsuarioController

* Dashboard
* CRUD interno de usuarios
* Cambios de contraseña
* Activar/desactivar usuarios

---

## 11. Sistema de Vistas

### View.php

* Renderiza vistas dentro de layouts.
* Usa `ob_start()` para capturar contenido.

### Layout principal

* Header, mensajes flash, contenedor principal.

### Vistas disponibles

* Auth (login, register)
* Usuario (dashboard, index, show, create, edit…)
* Home (index)
* Errors (404, 500)

---

## 12. Rutas

Ejemplo:

```php
['GET', '/', 'HomeController@index', []],
['GET', '/login', 'AuthController@showLogin', []],
['POST', '/login', 'AuthController@login', ['csrf']],
['GET', '/dashboard', 'UsuarioController@dashboard', ['auth']],
```

---

## 13. Cómo ejecutar el proyecto

### Requisitos:

* PHP 8.2+
* XAMPP (Apache + MySQL)
* Composer

### Pasos:

1. Copiar en `C:\xampp\htdocs\sig`
2. Ejecutar:

   ```
   composer install
   ```
3. Configurar `.env`
4. Crear BD y tabla `usuarios`
5. Acceder en el navegador:
   `http://localhost/sig/public`

---

## 14. Cómo añadir nuevas funcionalidades

### Página sin lógica de BD:

1. Crear método en un controlador.
2. Crear vista.
3. Agregar ruta.

### Funcionalidad con BD:

1. Añadir métodos a Service.
2. Añadir métodos a Repository.
3. Crear controlador o métodos nuevos.
4. Crear vistas.
5. Registrar rutas + middlewares.
