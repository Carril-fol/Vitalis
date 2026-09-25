<div align="center">

<img src="./assets/images/log.webp" alt="Logo de Vitalis" width="120" />

<h1>Vitalis</h1>

**Sistema de gestión para centros clínicos**

Turnos · Agenda médica · Pacientes · Obras sociales · Notas de consulta · Roles y permisos

<br/>

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Symfony](https://img.shields.io/badge/Symfony-7.4-000000?style=for-the-badge&logo=symfony&logoColor=white)
![Doctrine](https://img.shields.io/badge/Doctrine-ORM-FC6A31?style=for-the-badge)
![MariaDB](https://img.shields.io/badge/MariaDB-003545?style=for-the-badge&logo=mariadb&logoColor=white)
![Twig](https://img.shields.io/badge/Twig-Templates-8BC34A?style=for-the-badge)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white)

<br/>

[Funcionalidades](#funcionalidades) •
[Inicio rápido](#inicio-rápido) •
[Arquitectura](#arquitectura) •
[Modelo de datos](#modelo-de-datos) •
[Estructura](#estructura)

</div>

<br/>

> [!NOTE]
> **Origen del proyecto.** Esta es la reescritura en Symfony de
> [Centro-clinico-Vitalis](https://github.com/Carril-fol/Centro-clinico-Vitalis),
> un proyecto universitario grupal de 2024 hecho en PHP sin framework junto a
> [ZekkiMe](https://github.com/ZekkiMe).

---

## Funcionalidades

<table>
  <tr>
    <td width="50%" valign="top">
      <h3>Turnos</h3>
      Reserva con validación de agenda, ausencias y solapamientos. Check-in, atención, ausente y cancelación con motivo.
    </td>
    <td width="50%" valign="top">
      <h3>Agenda médica</h3>
      Horarios semanales por consultorio y ausencias por rango de fechas.
    </td>
  </tr>
  <tr>
    <td width="50%" valign="top">
      <h3>Notas de consulta</h3>
      Los médicos que atienden registran una nota que queda en la historia del paciente.
    </td>
    <td width="50%" valign="top">
      <h3>Roles y permisos</h3>
      Acceso basado en roles (RBAC): los administradores delegan permisos a cada rol para restringir su uso dentro de la aplicación.
    </td>
  </tr>
  <tr>
    <td width="50%" valign="top">
      <h3>Catálogos</h3>
      Especialidades, obras sociales y consultorios.
    </td>
    <td width="50%" valign="top">
      <h3>Landing pública</h3>
      Página pública del centro, con acceso separado para el personal.
    </td>
  </tr>
</table>

### Ciclo de vida de un turno

```mermaid
stateDiagram-v2
    direction LR
    [*] --> booked: Reserva
    booked --> checked_in: Check-in
    checked_in --> attended: Atención
    booked --> no_show: Ausente
    booked --> cancelled: Cancelación (con motivo)
    attended --> [*]
    no_show --> [*]
    cancelled --> [*]
```

---

## Qué cambió respecto del original

| Aspecto                  | Original (2024)    | Esta versión                              |
|--------------------------|--------------------|-------------------------------------------|
| Framework                | PHP sin framework  | **Symfony 7.4**                           |
| Acceso a datos           | SQL a mano         | **Doctrine ORM**                          |
| Esquema de la base       | Script SQL         | **Doctrine Migrations** versionadas       |
| Permisos                 | —                  | **Voter de Symfony** con permisos por rol |
| Seguridad de formularios | —                  | **CSRF** en todos los formularios         |
| Puesta en marcha         | Manual             | **`docker compose up`**                   |

---

## Inicio rápido

### Con Docker (recomendado)

**1. Crear `.env.docker`** en la raíz del proyecto:

```env
APP_SECRET=
DB_ROOT_PASSWORD=
```

| Variable           | Requerida | Descripción                            |
|--------------------|:---------:|----------------------------------------|
| `APP_SECRET`       | Sí        | Secreto de Symfony                     |
| `DB_ROOT_PASSWORD` | Sí        | Contraseña de root de MariaDB          |
| `APP_PORT`         | No        | Puerto del host (por defecto `8000`)   |

**2. Levantar los contenedores:**

```bash
docker compose --env-file .env.docker up --build -d
```

> [!TIP]
> Al arrancar, el contenedor corre las migrations automáticamente: crea las tablas,
> los roles, los permisos, los catálogos base y el usuario administrador.

**3. Cargar datos de prueba** — médicos, pacientes, horarios y turnos:

```bash
docker compose --env-file .env.docker exec -T db mariadb -uroot -p<DB_ROOT_PASSWORD> php_mvc < seed.sql
```

En PowerShell:

```powershell
Get-Content seed.sql -Raw | docker compose --env-file .env.docker exec -T db mariadb -uroot -p<DB_ROOT_PASSWORD> php_mvc
```

**4. Abrir** [http://localhost:8000](http://localhost:8000)

<details>
<summary><b>Levantar en local (XAMPP)</b></summary>

<br/>

**Requisitos:** PHP 8.2+, Composer y MySQL/MariaDB.

```bash
composer install
```

Crear `.env.local` con la conexión a la base:

```env
DATABASE_URL="mysql://root:<password>@127.0.0.1:3306/php_mvc?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
```

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
mysql -uroot -p php_mvc < seed.sql      # opcional
php -S localhost:8000 -t public
```

</details>

### Usuarios de prueba

Todos usan la contraseña **`admin123`**.

| Rol           | Email                          | Origen       |
|---------------|--------------------------------|--------------|
| Administrador | `admin@vitalis.test`           | migrations   |
| Médico        | `laura.benitez@vitalis.test`   | `seed.sql`   |
| Paciente      | `julieta.morales@vitalis.test` | `seed.sql`   |

> [!WARNING]
> Estas credenciales son solo para desarrollo. Cambialas antes de desplegar en cualquier entorno real.

---

## Arquitectura

Monolito **MVC organizado por módulo** (`src/Turns`, `src/Patients`, …). Cada módulo tiene `Controllers`, `Forms`, `Models` y `Services`.

```mermaid
flowchart LR
    B["Navegador<br/><small>Turbo + Stimulus</small>"] -->|HTTP| A[Apache]
    A --> F["public/index.php<br/><small>Kernel de Symfony</small>"]
    F --> S["Security<br/><small>firewall + PermissionVoter</small>"]
    S --> C[Controller]
    C --> FM["Form<br/><small>validación</small>"]
    C --> SV["Service<br/><small>reglas de negocio</small>"]
    SV --> EM["Doctrine<br/><small>EntityManager</small>"]
    EM --> DB[("MariaDB")]
    C --> T[Twig]
    T -->|HTML| B

    classDef client fill:#E3F2FD,stroke:#1E88E5,color:#0D47A1
    classDef infra fill:#ECEFF1,stroke:#607D8B,color:#263238
    classDef security fill:#FFEBEE,stroke:#E53935,color:#B71C1C
    classDef app fill:#F3E5F5,stroke:#8E24AA,color:#4A148C
    classDef data fill:#E8F5E9,stroke:#43A047,color:#1B5E20

    class B client
    class A,F infra
    class S security
    class C,FM,SV,T app
    class EM,DB data
```

| Capa            | Responsabilidad                                                                 |
|-----------------|---------------------------------------------------------------------------------|
| **Controllers** | Reciben el request, chequean permisos con `#[IsGranted]` y delegan.             |
| **Services**    | Reglas de negocio: disponibilidad de turnos, solapamiento de horarios, quién puede escribir una nota. |
| **Models**      | Entidades de Doctrine mapeadas con atributos.                                   |
| **Forms**       | Tipos de formulario de Symfony con sus validaciones.                            |

---

## Modelo de datos

```mermaid
erDiagram
    roles ||--o{ users : tiene
    roles }o--o{ permissions : roles_permissions
    users ||--o| administratives : es
    users ||--o| medical_staff : es
    users ||--o| patients : es
    specialities |o--o{ medical_staff : especialidad
    health_insurances ||--o{ patients : cubre
    medical_staff ||--o{ medical_schedules : atiende
    medical_staff ||--o{ medical_absences : falta
    consulting_rooms ||--o{ medical_schedules : en
    patients ||--o{ turns : reserva
    medical_staff ||--o{ turns : atiende
    consulting_rooms ||--o{ turns : en
    users ||--o{ turns : "crea / cancela"
    turns ||--o| consultations : nota
    users ||--o{ consultations : escribe

    users {
        int id PK
        string dni UK
        string email UK
        string status
        int role_id FK
    }
    turns {
        int id PK
        datetime starts_at
        datetime ends_at
        string status "booked | checked_in | attended | no_show | cancelled"
        int patient_id FK
        int medical_staff_id FK
        int consulting_room_id FK
    }
    medical_schedules {
        int id PK
        int weekday "1-7"
        time start_time
        time end_time
        int slot_minutes "5-240"
    }
    consultations {
        int id PK
        text content
        int turn_id FK
    }
```

### Migraciones

La estructura se maneja con **Doctrine Migrations** (`migrations/`). Después de cambiar una entidad:

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

> [!IMPORTANT]
> Los `CHECK` constraints están en una migration aparte, escrita a mano,
> porque Doctrine no los genera a partir de las entidades.

---

## Estructura

El código está organizado por módulo dentro de `src/`:

```text
src/
└── <Módulo>/
    ├── Controllers/
    ├── Forms/
    ├── Models/      ← entidades
    └── Services/
```

| Módulo                                                | Qué hace                                             |
|-------------------------------------------------------|------------------------------------------------------|
| `Auth`                                                | Login y chequeo de permisos (`PermissionVoter`)      |
| `Users`                                               | Usuarios base                                        |
| `Roles`, `Permissions`                                | Roles por área y sus permisos                        |
| `Administratives`                                     | Personal administrativo                              |
| `MedicalStaff`                                        | Médicos, horarios y ausencias                        |
| `Patients`                                            | Pacientes y obra social                              |
| `Specialities`, `HealthInsurances`, `ConsultingRooms` | Catálogos                                            |
| `Turns`                                               | Reserva, check-in, atención y cancelación de turnos  |
| `Consultations`                                       | Notas de consulta e historia del paciente            |
| `Dashboard`, `Landing`                                | Panel principal y página pública                     |

---

<div align="center">
<sub>Hecho con Symfony · Reescritura de un proyecto universitario de 2024</sub>
</div>