Cada vez que me des una nueva respuesta, arranca con mi nombre.

# Proyecto

Migración de `D:\xampp\htdocs\php-mvc` (MVC casero) a Symfony 7.4 LTS. El proyecto viejo sigue siendo la referencia de lógica y vistas.

- PHP 8.2.12 (XAMPP). No subir a Symfony 8: pide PHP 8.4.
- Base: MariaDB 10.4 de XAMPP, base `php_mvc`, configurada en `.env.local`. Las tablas ya existen y tienen datos reales: no correr `doctrine:schema:update --force` ni migraciones sin revisar antes qué cambian. Hoy `--dump-sql` muestra `DROP TABLE products` (no tiene entidad): un `--force` la borraría.

## Correr

MySQL tiene que estar prendido en el XAMPP Control Panel.

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

El `public/index.php` del final es necesario: sin él, el servidor de PHP devuelve 404 para los assets de AssetMapper.

## Estructura y convenciones

- Módulos en `src/<Modulo>/{Controllers,Interfaces,Models,Repositories,Schemas,Services}`, igual que el proyecto viejo.
  - Doctrine mapea todo `src/` (`dir: src`, `prefix: App`).
  - Las rutas se toman de cualquier controller en `src/` (`routing.controllers`).
  - Una interfaz con una sola implementación se autowirea sola; no hay `container.php`.
- Estilo: `array()` en vez de `[]`, interfaces `I<Nombre>Service`, repositorios que extienden `App\Core\Repository`.
- Schemas con atributos de Symfony Validator + `fromPost()`. Los services validan y tiran `App\Core\ValidationException`.
- Buscar por id: el service tira `App\Core\NotFoundException` en vez de devolver `null` (tiene `#[WithHttpStatus(404)]`), y el controller no chequea. Así están `Roles` y `Specialities`; las vistas `edit` no tienen estado "no encontrado".
- Controllers:
  - Extienden `AbstractController` y las rutas van con `#[Route]`, con nombres `modulo.accion`.
  - Una sola acción por formulario, con `methods: array('GET', 'POST')`: GET muestra el form y POST lo guarda en la misma URL. Son `modulo.create` (`/create`) y `modulo.edit` (`/{id}/edit`); los viejos `renderCreate`/`saveOnCreate` y las rutas `modulo.store`/`modulo.update` no se migran. `RoleController` es el modelo a copiar.
  - Si un formulario vuelve con error, responder 422 (`new Response(status: 422)`); si no, Turbo no muestra la página.
  - Los forms de borrar llevan token CSRF `delete-<modulo>-<id>`, validado con `isCsrfTokenValid`.
- Vistas en `templates/<modulo>/*.html.twig`, que extienden `base.html.twig`.
  - Las pantallas sin nav (login) pisan `{% block layout %}` en vez de `body`.
  - El CSS está en `assets/styles/app.css` (AssetMapper).
  - Los forms con `_csrf_token` de un token stateless (`authenticate`, `logout`, ver `csrf.yaml`) llevan `data-controller="csrf-protection"` en el input: sin eso no se carga el JS que arma el token.
  - Los links del nav de módulos sin migrar están comentados, porque `path()` falla si la ruta no existe.

## Estado

Migrado:
- `Core`: `Repository`, `ValidationException` y `NotFoundException`. El Router, el Controller y el container viejos no se usan.
- `Users`: models, services, repositories y schemas. `UserController.php` está vacío.
- `Roles`: completo (4 rutas: index, create, edit, delete + sus vistas) y probado.
  - `roles.area` (enum `RoleArea`: ADMINISTRATIVE, MEDIC, NURSE, PATIENT), nullable y no única: varios roles por área. Se elige con un select al crear/editar el rol. Antes era `roles.code` único (`RoleCode`); se renombró con SQL a mano.
  - Cada alta de perfil ofrece solo los roles de su área (`IRoleService::getRolesByArea()`). Un rol sin área (CADETE) no aparece en ningún alta.
- `Specialities`: completo (las mismas 4 rutas + vistas) y probado. El link del nav ya está activo.
- `Administratives`: completo (index, create, edit, activate, deactivate + vistas). Probados los caminos de error; falta probar un alta, una edición y una baja reales.
  - El alta crea usuario + legajo en un solo flush.
  - El puesto es el rol del usuario (`users.role_id`), elegido de un select con los roles de área ADMINISTRATIVE; el service verifica que el id sea de esa área. `sector` sigue siendo texto libre. La columna `administratives.position` se borró.
  - En `edit` el puesto solo se cambia con `isGranted('administratives.change_position')`; sin voter nadie lo tiene y se muestra fijo. Renombrarlo al patrón del catálogo cuando se haga el voter.
  - Baja/reactivación cambian `users.status` (el `UserChecker` bloquea el login) y usan el token CSRF `status-administrative-<id>`.
- `Auth`: login con Security, probado. Reemplaza a `AuthService` / `LoginSchema` / `$_SESSION`, que ya se borraron.
  - `User` implementa `UserInterface` + `PasswordAuthenticatedUserInterface` (identifier = email, password = `passwordHash`). `getRoles()` devuelve siempre `ROLE_USER`: los permisos los va a decidir el voter. `__serialize()` guarda en sesión solo id, email y un crc32c del hash.
  - `security.yaml`: entity provider por `email`, `form_login` en `login`, `logout` (POST, con CSRF), `remember_me` (checkbox `remember`, 1 semana), `^/login` público y el resto `ROLE_USER`.
  - `AuthController::login()` pasa `errors.credentials` y `old.email` a `auth/login.html.twig`. `form_login` redirige al fallar, así que el login no usa el 422.
  - `Auth/Services/UserChecker` bloquea usuarios inactivos en `checkPostAuth` (en pre revelaría qué emails existen).

Pendiente, en este orden:
1. `git init` + commit inicial (todavía no es repo).
2. Botón de logout en el nav de `base.html.twig`: form POST a `path('logout')` con `csrf_token('logout')`, dentro de `{% if app.user %}`.
3. Permisos y voter:
   - `permissions` ya existe con 16 filas `<accion>_<modulo>` (create/read/update/delete × administratives, medics, patients, turns). Es un catálogo fijo: sin CRUD, porque el código los chequea por nombre.
   - Hecho: entidad `RolePermission` (tabla `roles_permissions`, única por `role_id` + `permission_id`, `ON DELETE CASCADE` por rol). Crear/editar rol usan los schemas `CreateRole`/`UpdateRole` (con `permissionIds`) y muestran checkboxes (grilla módulo × acción, `roles/_permissions.html.twig`). Rol y permisos se guardan en un solo flush (`IRolePermissionsService::syncPermissions()`).
   - `RolePermissionsService` no depende de `IRoleService` (recibe el `Role`): `RoleService` depende de él, y al revés arma una dependencia circular.
   - La tabla `roles_permissions` ya está creada y el ciclo crear/editar/borrar rol con permisos está probado.
   - Falta: el voter con `roleHasPermission()`, aplicado con `#[IsGranted('<permiso>')]` / `is_granted()` en Twig. Reemplaza el chequeo que tenía el Router viejo.
   - Falta decidir: un permiso para gestionar roles (sin él cualquiera se da todos los permisos), cómo se carga el primer rol administrador, y el nombre del permiso de cambiar puesto.
4. El resto de los módulos: Patients.

Menores:
- `public/favicon.png` no existe.
- `app.css` importa la fuente de Google que `base.html.twig` ya carga con `<link>`.
- El JS de Vitalis del proyecto viejo no está migrado: `data-vitalis-loading` (spinner), `data-vitalis-toggle` (mostrar contraseña) y `data-vitalis-capslock` no hacen nada todavía. Se usan en el login y en `roles/create`.
