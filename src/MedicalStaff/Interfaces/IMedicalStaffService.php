<?php
namespace App\MedicalStaff\Interfaces;

use App\MedicalStaff\Models\MedicalStaff;
use App\MedicalStaff\Schemas\MedicalStaffRegistrationSchema;
use App\MedicalStaff\Schemas\MedicalStaffUpdateSchema;


interface IMedicalStaffService
{
    /**
     * Get medical staff by ID
     */
    public function getMedicalStaffById(int $id): MedicalStaff;

    /**
     * Get all medical staff
     */
    public function getAllMedicalStaff(): array;

    /**
     * Get available positions for medical staff
     */
    public function getPositions(): array;

    /**
     * Register a new medical staff member
     */
    public function register(MedicalStaffRegistrationSchema $schema): int;

    /**
     * Update medical staff information
     */
    public function update(int $id, MedicalStaffUpdateSchema $schema): void;

    /**
     * Activate a medical staff member
     */
    public function activate(int $id): void;

    /**
     * Deactivate a medical staff member
     */
    public function deactivate(int $id): void;

    public function getMedicalStaffUpdateSchemaById(int $id): MedicalStaffUpdateSchema;
    public function getMedicalStaffRegistrationSchema(): MedicalStaffRegistrationSchema;
}