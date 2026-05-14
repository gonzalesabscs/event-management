-- Add logo upload functionality to system settings
-- This script adds the logo path setting to the system

-- Add logo setting if it doesn't exist
INSERT INTO `tbl_system_settings` (`setting_key`, `setting_value`, `setting_description`, `updated_date`) 
VALUES ('clinic_logo', '', 'Path to the clinic logo image', NOW())
ON DUPLICATE KEY UPDATE `setting_description` = 'Path to the clinic logo image';

-- Create uploads directory structure (note: actual directory creation must be done via PHP or manually)
-- Recommended directory: uploads/logo/
