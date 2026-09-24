<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924070259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'CHECK constraints que Doctrine no genera';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE medical_schedules ADD CONSTRAINT schedule_weekday CHECK (weekday BETWEEN 1 AND 7), ADD CONSTRAINT schedule_hours CHECK (start_time < end_time), ADD CONSTRAINT schedule_slot CHECK (slot_minutes BETWEEN 5 AND 240)');
        $this->addSql('ALTER TABLE medical_absences ADD CONSTRAINT absence_range CHECK (starts_at < ends_at)');
        $this->addSql("ALTER TABLE turns ADD CONSTRAINT turn_status CHECK (status IN ('booked', 'checked_in', 'attended', 'no_show', 'cancelled')), ADD CONSTRAINT turn_range CHECK (starts_at < ends_at)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE medical_schedules DROP CONSTRAINT schedule_weekday, DROP CONSTRAINT schedule_hours, DROP CONSTRAINT schedule_slot');
        $this->addSql('ALTER TABLE medical_absences DROP CONSTRAINT absence_range');
        $this->addSql('ALTER TABLE turns DROP CONSTRAINT turn_status, DROP CONSTRAINT turn_range');
    }
}
