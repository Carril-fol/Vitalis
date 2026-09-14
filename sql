-- Esquema del sistema de turnos.
--
-- Las tablas de la seccion 1 estan implementadas: son un volcado del esquema
-- que corre hoy, y coinciden con las entidades Doctrine de app/*/Models. Los
-- nombres de indices y constraints son los que genera Doctrine, asi que una
-- base creada con este archivo no le da diferencias a schema-tool.
--
-- La seccion 3 es diseño pendiente: todavia no hay entidades para esas tablas,
-- asi que Doctrine no las conoce.

CREATE DATABASE php_mvc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE php_mvc;


-- ---------------------------------------------------------------------------
-- 1. Tablas implementadas
-- ---------------------------------------------------------------------------
-- Ojo con el COLLATE: la base es utf8mb4_unicode_ci pero las tablas quedan en
-- utf8mb4_general_ci, que es el default de Doctrine. Se deja tal cual para que
-- refleje lo que hay. Las dos son case-insensitive, de lo que dependen los
-- chequeos de duplicados por nombre.

-- code es la clave estable con la que el codigo reconoce al rol, y el enum
-- RoleCode es su tipo. name se puede renombrar desde la pantalla de Roles sin
-- romper nada; code no se edita nunca.
--
-- Es nullable a proposito: un rol que invente la clinica no necesita codigo, y
-- MySQL admite varios NULL en un indice UNIQUE.
CREATE TABLE roles (
    id   INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(30) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY UNIQ_B63E2EC75E237E06 (name),
    UNIQUE KEY UNIQ_B63E2EC777153098 (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Los roles que el sistema necesita encontrar por code. Sin estas filas, el
-- alta de administrativos falla porque no encuentra su rol.
INSERT INTO roles (name, code) VALUES
    ('Administrativo', 'ADMINISTRATIVE'),
    ('Medico',         'MEDIC'),
    ('Enfermero',      'NURSE'),
    ('Paciente',       'PATIENT');


-- La entidad mapea a "specialities". El archivo anterior decia "specialties".
CREATE TABLE specialities (
    id   INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY UNIQ_FFAFEB115E237E06 (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE users (
    id            INT AUTO_INCREMENT NOT NULL,
    role_id       INT NOT NULL,
    dni           VARCHAR(8)   NOT NULL,
    first_name    VARCHAR(100) NOT NULL,
    last_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone         VARCHAR(30)  DEFAULT NULL,
    address       VARCHAR(150) DEFAULT NULL,
    city          VARCHAR(100) DEFAULT NULL,
    status        VARCHAR(20)  NOT NULL,
    birth_date    DATE NOT NULL,
    created_at    DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY UNIQ_1483A5E97F8F253B (dni),
    UNIQUE KEY UNIQ_1483A5E9E7927C74 (email),
    KEY IDX_1483A5E9D60322AC (role_id),
    CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES roles (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- user_id UNIQUE: un legajo por persona. El CASCADE borra el legajo cuando se
-- borra el usuario. notes es LONGTEXT porque el tipo 'text' de Doctrine mapea
-- a eso en MySQL.
CREATE TABLE administratives (
    id       INT AUTO_INCREMENT NOT NULL,
    user_id  INT NOT NULL,
    sector   VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    notes    LONGTEXT DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY UNIQ_F8FECB64A76ED395 (user_id),
    CONSTRAINT FK_F8FECB64A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE permissions (
    id   INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY UNIQ_2DEDCC6F5E237E06 (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ---------------------------------------------------------------------------
-- 3. Diseño pendiente (sin entidad todavia)
-- ---------------------------------------------------------------------------
-- Estas tres no existen en la base. Se mantienen como referencia del diseño.
-- Cuando se creen las entidades, Doctrine va a generar sus propios nombres de
-- indice y constraint, distintos de los de aca.
--
-- medics y patients repiten la forma de administratives: user_id UNIQUE mas
-- CASCADE, o sea un perfil por usuario.

CREATE TABLE medics (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL UNIQUE,
    speciality_id  INT NOT NULL,
    license_number VARCHAR(20) NOT NULL UNIQUE,
    FOREIGN KEY (user_id)       REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (speciality_id) REFERENCES specialities(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE patients (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL UNIQUE,
    health_insurance VARCHAR(100),
    member_number    VARCHAR(50) UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE turns (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    patient_id       INT NOT NULL,
    medic_id         INT NULL,                 -- NULL mientras el turno está REQUESTED
    speciality_id    INT NOT NULL,
    appointment_date DATE NOT NULL,
    start_time       TIME NOT NULL,
    status           ENUM('REQUESTED','PENDING','CANCELLED','ATTENDED')
                     NOT NULL DEFAULT 'REQUESTED',
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    booked_slot      TINYINT GENERATED ALWAYS AS (IF(status = 'CANCELLED', NULL, 1)) STORED,
    FOREIGN KEY (patient_id)    REFERENCES patients(id),
    FOREIGN KEY (medic_id)      REFERENCES medics(id),
    FOREIGN KEY (speciality_id) REFERENCES specialities(id),
    UNIQUE KEY medic_agenda (medic_id, appointment_date, start_time, booked_slot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


php -S 127.0.0.1:8000 -t public public/index.php