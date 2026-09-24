<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924070300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Datos base: permisos, roles, catálogos y admin';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO permissions (name) VALUES
            ('read_roles'), ('create_roles'), ('update_roles'), ('delete_roles'),
            ('read_specialities'), ('create_specialities'), ('update_specialities'), ('delete_specialities'),
            ('read_administratives'), ('create_administratives'), ('update_administratives'),
            ('read_medics'), ('create_medics'), ('update_medics'),
            ('read_patients'), ('create_patients'), ('update_patients'),
            ('read_health_insurances'), ('create_health_insurances'), ('update_health_insurances'),
            ('read_consulting_rooms'), ('create_consulting_rooms'), ('update_consulting_rooms'),
            ('read_turns'), ('create_turns'), ('update_turns')");

        $this->addSql("INSERT INTO roles (name, area) VALUES
            ('Administrador', 'ADMINISTRATIVE'), ('Medico', 'MEDIC'), ('Paciente', 'PATIENT')");

        $this->addSql("INSERT INTO roles_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r CROSS JOIN permissions p WHERE r.name = 'Administrador'");

        $this->addSql("INSERT INTO roles_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r JOIN permissions p ON p.name IN ('read_turns', 'update_turns') WHERE r.name = 'Medico'");

        $this->addSql("INSERT INTO specialities (name) VALUES ('Clínica Médica')");
        $this->addSql("INSERT INTO health_insurances (name, is_active) VALUES ('OSDE', 1), ('Swiss Medical', 1), ('Galeno', 1)");
        $this->addSql("INSERT INTO consulting_rooms (name, is_active) VALUES ('Consultorio 1', 1)");

        $this->addSql("INSERT INTO users (dni, first_name, last_name, email, password_hash, status, birth_date, created_at, role_id)
            SELECT '99999999', 'ADMIN', 'VITALIS', 'admin@vitalis.test',
                   '\$2y\$13\$9oEPJb8qYMNdEtWQj24zkueC3kT4ijNSTlocdWFSyG7.BLP0oRT32',
                   'active', '1990-01-01', NOW(), id
            FROM roles WHERE name = 'Administrador'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM users WHERE dni = '99999999'");
        $this->addSql("DELETE FROM consulting_rooms WHERE name = 'Consultorio 1'");
        $this->addSql("DELETE FROM health_insurances WHERE name IN ('OSDE', 'Swiss Medical', 'Galeno')");
        $this->addSql("DELETE FROM specialities WHERE name = 'Clínica Médica'");
        $this->addSql("DELETE FROM roles WHERE name IN ('Administrador', 'Medico', 'Paciente')");
        $this->addSql('DELETE FROM permissions');
    }
}
