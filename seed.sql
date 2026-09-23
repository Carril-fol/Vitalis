
SET NAMES utf8mb4;

SET @pass         = '$2y$13$LJl4FjRy49yycuZBrurBMuC50Mu0rAtz9IkQEyueJWsXqMmU9/9nG';
SET @role_medic   = (SELECT id FROM roles WHERE area = 'MEDIC' AND name = 'Medico' LIMIT 1);
SET @role_patient = (SELECT id FROM roles WHERE area = 'PATIENT' ORDER BY id LIMIT 1);
SET @speciality   = (SELECT id FROM specialities ORDER BY id LIMIT 1);
SET @room         = (SELECT id FROM consulting_rooms ORDER BY id LIMIT 1);
SET @admin        = (SELECT id FROM users WHERE dni = '99999999' LIMIT 1);

INSERT IGNORE INTO users
    (dni, first_name, last_name, email, password_hash, phone, address, city, status, birth_date, created_at, role_id)
VALUES
    ('30111001', 'LAURA',   'BENITEZ', 'laura.benitez@vitalis.test',  @pass, '1130110001', 'AV. RIVADAVIA 4500', 'CABA',        'active', '1979-04-12', NOW(), @role_medic),
    ('30111002', 'MARTIN',  'SOSA',    'martin.sosa@vitalis.test',    @pass, '1130110002', 'BELGRANO 1220',      'MORON',       'active', '1982-11-03', NOW(), @role_medic),
    ('30111003', 'CECILIA', 'ROMERO',  'cecilia.romero@vitalis.test', @pass, '1130110003', 'SAN MARTIN 870',     'RAMOS MEJIA', 'active', '1985-06-27', NOW(), @role_medic),
    ('30111004', 'DIEGO',   'FERRARI', 'diego.ferrari@vitalis.test',  @pass, '1130110004', 'MITRE 233',          'CIUDADELA',   'active', '1976-01-19', NOW(), @role_medic),
    ('30111005', 'VALERIA', 'ACOSTA',  'valeria.acosta@vitalis.test', @pass, '1130110005', 'LAS HERAS 1490',     'HAEDO',       'active', '1988-09-08', NOW(), @role_medic);

INSERT IGNORE INTO medical_staff (user_id, speciality_id, license_number)
SELECT u.id, @speciality, CONCAT('MP 2000', RIGHT(u.dni, 1))
FROM users u
WHERE u.dni IN ('30111001', '30111002', '30111003', '30111004', '30111005');

INSERT IGNORE INTO users
    (dni, first_name, last_name, email, password_hash, phone, address, city, status, birth_date, created_at, role_id)
VALUES
    ('35222001', 'JULIETA', 'MORALES', 'julieta.morales@vitalis.test', @pass, '1135220001', 'ALSINA 55',      'CABA',        'active', '1995-02-14', NOW(), @role_patient),
    ('35222002', 'NICOLAS', 'PEREYRA', 'nicolas.pereyra@vitalis.test', @pass, '1135220002', 'SARMIENTO 1800', 'MORON',       'active', '1990-07-30', NOW(), @role_patient),
    ('35222003', 'SOFIA',   'IBARRA',  'sofia.ibarra@vitalis.test',    @pass, '1135220003', 'URQUIZA 640',    'RAMOS MEJIA', 'active', '2001-12-05', NOW(), @role_patient),
    ('35222004', 'GONZALO', 'MEDINA',  'gonzalo.medina@vitalis.test',  @pass, '1135220004', 'ESPANA 310',     'CIUDADELA',   'active', '1968-03-22', NOW(), @role_patient),
    ('35222005', 'CAMILA',  'VARELA',  'camila.varela@vitalis.test',   @pass, '1135220005', 'MORENO 2075',    'HAEDO',       'active', '1983-10-17', NOW(), @role_patient);

INSERT IGNORE INTO patients (user_id, health_insurance_id, member_number)
SELECT
    u.id,
    (SELECT h.id FROM health_insurances h ORDER BY h.id LIMIT 1) + (RIGHT(u.dni, 1) - 1) % 3,
    CONCAT('11', u.dni)
FROM users u
WHERE u.dni IN ('35222001', '35222002', '35222003', '35222004', '35222005');

INSERT IGNORE INTO medical_schedules (medical_staff_id, consulting_room_id, weekday, start_time, end_time, slot_minutes)
SELECT m.id, @room, s.weekday, s.start_time, s.end_time, 30
FROM medical_staff m
JOIN users u ON u.id = m.user_id
JOIN (
    SELECT '30111001' AS dni, 3 AS weekday, '09:00:00' AS start_time, '13:00:00' AS end_time
    UNION ALL SELECT '30111002', 3, '14:00:00', '18:00:00'
    UNION ALL SELECT '30111003', 2, '09:00:00', '13:00:00'
    UNION ALL SELECT '30111004', 4, '09:00:00', '13:00:00'
    UNION ALL SELECT '30111005', 5, '09:00:00', '13:00:00'
) s ON s.dni = u.dni;

INSERT IGNORE INTO turns
    (patient_id, medical_staff_id, consulting_room_id, starts_at, ends_at, status, reason, created_by_id, created_at, active_slot)
SELECT
    (SELECT p.id FROM patients p JOIN users pu ON pu.id = p.user_id WHERE pu.dni = t.patient_dni),
    (SELECT m.id FROM medical_staff m JOIN users mu ON mu.id = m.user_id WHERE mu.dni = t.medic_dni),
    @room,
    TIMESTAMP(CURDATE() + INTERVAL t.day_offset DAY, t.at),
    TIMESTAMP(CURDATE() + INTERVAL t.day_offset DAY, t.at) + INTERVAL 30 MINUTE,
    t.status,
    t.reason,
    @admin,
    NOW(),
    CASE WHEN t.status = 'cancelled' THEN NULL ELSE 1 END
FROM (
    SELECT '35222001' AS patient_dni, '30111001' AS medic_dni, 0 AS day_offset, '09:00:00' AS at, 'attended'   AS status, 'Control anual'            AS reason
    UNION ALL SELECT '35222002', '30111001', 0, '09:30:00', 'attended',   'Dolor de pecho al esfuerzo'
    UNION ALL SELECT '35222003', '30111001', 0, '10:00:00', 'checked_in', 'Resultado de electro'
    UNION ALL SELECT '35222004', '30111001', 0, '10:30:00', 'no_show',    'Control de presión'
    UNION ALL SELECT '35222005', '30111001', 0, '11:00:00', 'booked',     'Primera consulta'
    UNION ALL SELECT '35222001', '30111001', 0, '11:30:00', 'booked',     'Renovación de receta'
    UNION ALL SELECT '35222002', '30111002', 0, '14:00:00', 'booked',     'Seguimiento post internación'
    UNION ALL SELECT '35222003', '30111002', 0, '14:30:00', 'booked',     'Arritmia'
    UNION ALL SELECT '35222004', '30111001', 7, '09:00:00', 'booked',     'Control anual'
    UNION ALL SELECT '35222005', '30111001', 7, '09:30:00', 'booked',     'Ecocardiograma'
) t;
