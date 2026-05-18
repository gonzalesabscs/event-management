-- Add no-show and pet enhancement fields
-- This script adds fields for auto-cancellation and pet details

-- Add check-in/arrival status to appointments
ALTER TABLE `tbl_appointments` 
ADD COLUMN `checked_in` TINYINT(1) DEFAULT 0 AFTER `status`,
ADD COLUMN `checked_in_time` TIMESTAMP NULL DEFAULT NULL AFTER `checked_in`;

-- Add pet gender and age fields (required fields)
ALTER TABLE `tbl_appointments` 
ADD COLUMN `pet_gender` VARCHAR(10) NULL AFTER `pet_breed`,
ADD COLUMN `pet_age` INT(3) NULL AFTER `pet_gender`;

-- Add auto-cancelled tracking
ALTER TABLE `tbl_appointments` 
ADD COLUMN `auto_cancelled` TINYINT(1) DEFAULT 0 AFTER `cancellation_reason`,
ADD COLUMN `auto_cancelled_date` TIMESTAMP NULL DEFAULT NULL AFTER `auto_cancelled`;

-- Update existing appointments to have default check-in status
UPDATE `tbl_appointments` SET `checked_in` = 0 WHERE `checked_in` IS NULL;

-- Add index for faster queries on appointment_date and status
ALTER TABLE `tbl_appointments` 
ADD INDEX `idx_appointment_status` (`appointment_date`, `status`, `checked_in`);
