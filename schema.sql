CREATE DATABASE IF NOT EXISTS php_mvc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE php_mvc;

CREATE TABLE roles (
    id   INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(50) NOT NULL,
    area VARCHAR(30) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE INDEX UNIQ_B63E2EC75E237E06 (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE permissions (
    id   INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE INDEX UNIQ_2DEDCC6F5E237E06 (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE roles_permissions (
    role_id       INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    INDEX IDX_CEC2E043D60322AC (role_id),
    INDEX IDX_CEC2E043FED90CCA (permission_id),
    CONSTRAINT FK_CEC2E043D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
    CONSTRAINT FK_CEC2E043FED90CCA FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE specialities (
    id   INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE INDEX UNIQ_FFAFEB115E237E06 (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    UNIQUE INDEX UNIQ_1483A5E97F8F253B (dni),
    UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email),
    INDEX IDX_1483A5E9D60322AC (role_id),
    CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES roles (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE administratives (
    id      INT AUTO_INCREMENT NOT NULL,
    user_id INT NOT NULL,
    sector  VARCHAR(100) NOT NULL,
    notes   LONGTEXT DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE INDEX UNIQ_F8FECB64A76ED395 (user_id),
    CONSTRAINT FK_F8FECB64A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE medical_staff (
    id             INT AUTO_INCREMENT NOT NULL,
    user_id        INT NOT NULL,
    speciality_id  INT DEFAULT NULL,
    license_number VARCHAR(20) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE INDEX UNIQ_7788A8C2A76ED395 (user_id),
    UNIQUE INDEX UNIQ_7788A8C2EC7E7152 (license_number),
    INDEX IDX_7788A8C23B5A08D7 (speciality_id),
    CONSTRAINT FK_7788A8C2A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT FK_7788A8C23B5A08D7 FOREIGN KEY (speciality_id) REFERENCES specialities (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE messenger_messages (
    id           BIGINT AUTO_INCREMENT NOT NULL,
    body         LONGTEXT NOT NULL,
    headers      LONGTEXT NOT NULL,
    queue_name   VARCHAR(190) NOT NULL,
    created_at   DATETIME NOT NULL,
    available_at DATETIME NOT NULL,
    delivered_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE health_insurances (
    id        INT AUTO_INCREMENT NOT NULL,
    name      VARCHAR(100) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE INDEX health_insurance_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE patients (
    id                  INT AUTO_INCREMENT NOT NULL,
    user_id             INT NOT NULL,
    health_insurance_id INT DEFAULT NULL,
    member_number       VARCHAR(50) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE INDEX UNIQ_2CCC2E2CA76ED395 (user_id),
    UNIQUE INDEX insurance_member (health_insurance_id, member_number),
    CONSTRAINT FK_2CCC2E2CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT patient_health_insurance FOREIGN KEY (health_insurance_id) REFERENCES health_insurances (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE consulting_rooms (
    id        INT AUTO_INCREMENT NOT NULL,
    name      VARCHAR(50) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE INDEX consulting_room_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE medical_schedules (
    id                 INT AUTO_INCREMENT NOT NULL,
    medical_staff_id   INT NOT NULL,
    consulting_room_id INT NOT NULL,
    weekday            SMALLINT NOT NULL,
    start_time         TIME NOT NULL,
    end_time           TIME NOT NULL,
    slot_minutes       SMALLINT NOT NULL DEFAULT 30,
    PRIMARY KEY (id),
    INDEX medic_weekday (medical_staff_id, weekday),
    CONSTRAINT schedule_medic FOREIGN KEY (medical_staff_id) REFERENCES medical_staff (id) ON DELETE CASCADE,
    CONSTRAINT schedule_room FOREIGN KEY (consulting_room_id) REFERENCES consulting_rooms (id),
    CONSTRAINT schedule_weekday CHECK (weekday BETWEEN 1 AND 7),
    CONSTRAINT schedule_hours CHECK (start_time < end_time),
    CONSTRAINT schedule_slot CHECK (slot_minutes BETWEEN 5 AND 240)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE medical_absences (
    id               INT AUTO_INCREMENT NOT NULL,
    medical_staff_id INT DEFAULT NULL,
    starts_at        DATETIME NOT NULL,
    ends_at          DATETIME NOT NULL,
    reason           VARCHAR(150) DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX absence_medic_range (medical_staff_id, starts_at),
    CONSTRAINT absence_medic FOREIGN KEY (medical_staff_id) REFERENCES medical_staff (id) ON DELETE CASCADE,
    CONSTRAINT absence_range CHECK (starts_at < ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE turns (
    id                  INT AUTO_INCREMENT NOT NULL,
    patient_id          INT NOT NULL,
    medical_staff_id    INT NOT NULL,
    consulting_room_id  INT NOT NULL,
    starts_at           DATETIME NOT NULL,
    ends_at             DATETIME NOT NULL,
    status              VARCHAR(20) NOT NULL DEFAULT 'booked',
    reason              VARCHAR(255) DEFAULT NULL,
    created_by_id       INT NOT NULL,
    created_at          DATETIME NOT NULL,
    cancelled_at        DATETIME DEFAULT NULL,
    cancelled_by_id     INT DEFAULT NULL,
    cancellation_reason VARCHAR(255) DEFAULT NULL,
    active_slot         TINYINT(1) AS (IF(status = 'cancelled', NULL, 1)) STORED,
    PRIMARY KEY (id),
    UNIQUE INDEX medic_slot (medical_staff_id, starts_at, active_slot),
    INDEX patient_turns (patient_id, starts_at),
    INDEX room_turns (consulting_room_id, starts_at),
    CONSTRAINT turn_patient FOREIGN KEY (patient_id) REFERENCES patients (id),
    CONSTRAINT turn_medic FOREIGN KEY (medical_staff_id) REFERENCES medical_staff (id),
    CONSTRAINT turn_room FOREIGN KEY (consulting_room_id) REFERENCES consulting_rooms (id),
    CONSTRAINT turn_created_by FOREIGN KEY (created_by_id) REFERENCES users (id),
    CONSTRAINT turn_cancelled_by FOREIGN KEY (cancelled_by_id) REFERENCES users (id),
    CONSTRAINT turn_status CHECK (status IN ('booked', 'checked_in', 'attended', 'no_show', 'cancelled')),
    CONSTRAINT turn_range CHECK (starts_at < ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO permissions (name) VALUES
    ('read_roles'), ('create_roles'), ('update_roles'), ('delete_roles'),
    ('read_specialities'), ('create_specialities'), ('update_specialities'), ('delete_specialities'),
    ('read_administratives'), ('create_administratives'), ('update_administratives'),
    ('read_medics'), ('create_medics'), ('update_medics'),
    ('read_patients'), ('create_patients'), ('update_patients'),
    ('read_health_insurances'), ('create_health_insurances'), ('update_health_insurances'),
    ('read_consulting_rooms'), ('create_consulting_rooms'), ('update_consulting_rooms'),
    ('read_turns'), ('create_turns'), ('update_turns');
