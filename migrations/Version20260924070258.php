<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260924070258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE administratives (id INT AUTO_INCREMENT NOT NULL, sector VARCHAR(100) NOT NULL, notes LONGTEXT DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_F8FECB64A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE consultations (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, turn_id INT NOT NULL, created_by_id INT NOT NULL, UNIQUE INDEX UNIQ_242D8F531F4F9889 (turn_id), INDEX IDX_242D8F53B03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE consulting_rooms (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, is_active TINYINT NOT NULL, UNIQUE INDEX UNIQ_8F9249F05E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE health_insurances (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, is_active TINYINT NOT NULL, UNIQUE INDEX UNIQ_7A30B8DC5E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE medical_absences (id INT AUTO_INCREMENT NOT NULL, starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, reason VARCHAR(150) DEFAULT NULL, medical_staff_id INT DEFAULT NULL, INDEX absence_medic_range (medical_staff_id, starts_at), INDEX IDX_855A02A871D7BA9A (medical_staff_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE medical_schedules (id INT AUTO_INCREMENT NOT NULL, weekday SMALLINT NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, slot_minutes SMALLINT NOT NULL, medical_staff_id INT NOT NULL, consulting_room_id INT NOT NULL, INDEX medic_weekday (medical_staff_id, weekday), INDEX IDX_C448823471D7BA9A (medical_staff_id), INDEX IDX_C448823495D24FCF (consulting_room_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE medical_staff (id INT AUTO_INCREMENT NOT NULL, license_number VARCHAR(20) DEFAULT NULL, user_id INT NOT NULL, speciality_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_7788A8C2EC7E7152 (license_number), UNIQUE INDEX UNIQ_7788A8C2A76ED395 (user_id), INDEX IDX_7788A8C23B5A08D7 (speciality_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE patients (id INT AUTO_INCREMENT NOT NULL, member_number VARCHAR(50) NOT NULL, user_id INT NOT NULL, health_insurance_id INT NOT NULL, UNIQUE INDEX UNIQ_2CCC2E2CA76ED395 (user_id), UNIQUE INDEX insurance_member (health_insurance_id, member_number), INDEX IDX_2CCC2E2C66591349 (health_insurance_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE permissions (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_2DEDCC6F5E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE roles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, area VARCHAR(30) DEFAULT NULL, UNIQUE INDEX UNIQ_B63E2EC75E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE roles_permissions (role_id INT NOT NULL, permission_id INT NOT NULL, INDEX IDX_CEC2E043D60322AC (role_id), INDEX IDX_CEC2E043FED90CCA (permission_id), PRIMARY KEY (role_id, permission_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE specialities (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_FFAFEB115E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE turns (id INT AUTO_INCREMENT NOT NULL, starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, status VARCHAR(20) NOT NULL, active_slot TINYINT DEFAULT NULL, reason VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, cancelled_at DATETIME DEFAULT NULL, cancellation_reason VARCHAR(255) DEFAULT NULL, patient_id INT NOT NULL, medical_staff_id INT NOT NULL, consulting_room_id INT NOT NULL, created_by_id INT NOT NULL, cancelled_by_id INT DEFAULT NULL, UNIQUE INDEX medic_slot (medical_staff_id, starts_at, active_slot), INDEX IDX_F3963B2D6B899279 (patient_id), INDEX IDX_F3963B2D71D7BA9A (medical_staff_id), INDEX IDX_F3963B2D95D24FCF (consulting_room_id), INDEX IDX_F3963B2DB03A8386 (created_by_id), INDEX IDX_F3963B2D187B2D12 (cancelled_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, dni VARCHAR(8) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL, password_hash VARCHAR(255) NOT NULL, phone VARCHAR(30) DEFAULT NULL, address VARCHAR(150) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, status VARCHAR(20) NOT NULL, birth_date DATE NOT NULL, created_at DATETIME NOT NULL, role_id INT NOT NULL, UNIQUE INDEX UNIQ_1483A5E97F8F253B (dni), UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), INDEX IDX_1483A5E9D60322AC (role_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE administratives ADD CONSTRAINT FK_F8FECB64A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE consultations ADD CONSTRAINT FK_242D8F531F4F9889 FOREIGN KEY (turn_id) REFERENCES turns (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE consultations ADD CONSTRAINT FK_242D8F53B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE medical_absences ADD CONSTRAINT FK_855A02A871D7BA9A FOREIGN KEY (medical_staff_id) REFERENCES medical_staff (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE medical_schedules ADD CONSTRAINT FK_C448823471D7BA9A FOREIGN KEY (medical_staff_id) REFERENCES medical_staff (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE medical_schedules ADD CONSTRAINT FK_C448823495D24FCF FOREIGN KEY (consulting_room_id) REFERENCES consulting_rooms (id)');
        $this->addSql('ALTER TABLE medical_staff ADD CONSTRAINT FK_7788A8C2A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE medical_staff ADD CONSTRAINT FK_7788A8C23B5A08D7 FOREIGN KEY (speciality_id) REFERENCES specialities (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE patients ADD CONSTRAINT FK_2CCC2E2CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE patients ADD CONSTRAINT FK_2CCC2E2C66591349 FOREIGN KEY (health_insurance_id) REFERENCES health_insurances (id)');
        $this->addSql('ALTER TABLE roles_permissions ADD CONSTRAINT FK_CEC2E043D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE roles_permissions ADD CONSTRAINT FK_CEC2E043FED90CCA FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE turns ADD CONSTRAINT FK_F3963B2D6B899279 FOREIGN KEY (patient_id) REFERENCES patients (id)');
        $this->addSql('ALTER TABLE turns ADD CONSTRAINT FK_F3963B2D71D7BA9A FOREIGN KEY (medical_staff_id) REFERENCES medical_staff (id)');
        $this->addSql('ALTER TABLE turns ADD CONSTRAINT FK_F3963B2D95D24FCF FOREIGN KEY (consulting_room_id) REFERENCES consulting_rooms (id)');
        $this->addSql('ALTER TABLE turns ADD CONSTRAINT FK_F3963B2DB03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE turns ADD CONSTRAINT FK_F3963B2D187B2D12 FOREIGN KEY (cancelled_by_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES roles (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE administratives DROP FOREIGN KEY FK_F8FECB64A76ED395');
        $this->addSql('ALTER TABLE consultations DROP FOREIGN KEY FK_242D8F531F4F9889');
        $this->addSql('ALTER TABLE consultations DROP FOREIGN KEY FK_242D8F53B03A8386');
        $this->addSql('ALTER TABLE medical_absences DROP FOREIGN KEY FK_855A02A871D7BA9A');
        $this->addSql('ALTER TABLE medical_schedules DROP FOREIGN KEY FK_C448823471D7BA9A');
        $this->addSql('ALTER TABLE medical_schedules DROP FOREIGN KEY FK_C448823495D24FCF');
        $this->addSql('ALTER TABLE medical_staff DROP FOREIGN KEY FK_7788A8C2A76ED395');
        $this->addSql('ALTER TABLE medical_staff DROP FOREIGN KEY FK_7788A8C23B5A08D7');
        $this->addSql('ALTER TABLE patients DROP FOREIGN KEY FK_2CCC2E2CA76ED395');
        $this->addSql('ALTER TABLE patients DROP FOREIGN KEY FK_2CCC2E2C66591349');
        $this->addSql('ALTER TABLE roles_permissions DROP FOREIGN KEY FK_CEC2E043D60322AC');
        $this->addSql('ALTER TABLE roles_permissions DROP FOREIGN KEY FK_CEC2E043FED90CCA');
        $this->addSql('ALTER TABLE turns DROP FOREIGN KEY FK_F3963B2D6B899279');
        $this->addSql('ALTER TABLE turns DROP FOREIGN KEY FK_F3963B2D71D7BA9A');
        $this->addSql('ALTER TABLE turns DROP FOREIGN KEY FK_F3963B2D95D24FCF');
        $this->addSql('ALTER TABLE turns DROP FOREIGN KEY FK_F3963B2DB03A8386');
        $this->addSql('ALTER TABLE turns DROP FOREIGN KEY FK_F3963B2D187B2D12');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9D60322AC');
        $this->addSql('DROP TABLE administratives');
        $this->addSql('DROP TABLE consultations');
        $this->addSql('DROP TABLE consulting_rooms');
        $this->addSql('DROP TABLE health_insurances');
        $this->addSql('DROP TABLE medical_absences');
        $this->addSql('DROP TABLE medical_schedules');
        $this->addSql('DROP TABLE medical_staff');
        $this->addSql('DROP TABLE patients');
        $this->addSql('DROP TABLE permissions');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP TABLE roles_permissions');
        $this->addSql('DROP TABLE specialities');
        $this->addSql('DROP TABLE turns');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
