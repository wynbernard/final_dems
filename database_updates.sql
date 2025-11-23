-- Database updates to add pickup point and special needs fields to pre_reg_table
-- Run this SQL script to update your database schema

ALTER TABLE `pre_reg_table` 
ADD COLUMN `pickup_point_name` VARCHAR(200) DEFAULT '' AFTER `ethnicity`,
ADD COLUMN `have_vehicle` VARCHAR(10) DEFAULT '' AFTER `pickup_point_name`,
ADD COLUMN `vehicle_type` VARCHAR(50) DEFAULT '' AFTER `have_vehicle`,
ADD COLUMN `intend_evacuation` VARCHAR(20) DEFAULT '' AFTER `vehicle_type`,
ADD COLUMN `where_to_go` VARCHAR(100) DEFAULT '' AFTER `intend_evacuation`,
ADD COLUMN `have_special_needs` VARCHAR(10) DEFAULT '' AFTER `where_to_go`,
ADD COLUMN `special_needs` TEXT DEFAULT '' AFTER `have_special_needs`;

-- Update existing records to have empty values for new fields (optional)
UPDATE `pre_reg_table` SET 
    `pickup_point_name` = '',
    `have_vehicle` = '',
    `vehicle_type` = '',
    `intend_evacuation` = '',
    `where_to_go` = '',
    `have_special_needs` = '',
    `special_needs` = ''
WHERE `pickup_point_name` IS NULL;

-- Add population data to barangay management table
ALTER TABLE `barangay_manegement_table` 
ADD COLUMN `total_population` INT(11) DEFAULT 0 AFTER `longitude`,
ADD COLUMN `population_updated_date` DATE DEFAULT NULL AFTER `total_population`;

-- Add disaster-prone type to barangay management table
ALTER TABLE `barangay_manegement_table` 
ADD COLUMN `disaster_prone_type` VARCHAR(100) DEFAULT '' AFTER `total_population`;

-- Create brgy_forecasts table with scaling only
CREATE TABLE IF NOT EXISTS `brgy_forecasts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `barangay_name` varchar(255) NOT NULL,
  `scale_range` varchar(20) DEFAULT NULL,
  `forecast` float DEFAULT NULL,
  `lower_bound` float DEFAULT NULL,
  `upper_bound` float DEFAULT NULL,
  `accuracy_percentage` float DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_barangay_date` (`barangay_name`, `date`),
  KEY `idx_scale` (`scale_range`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create brgy_record_table if it doesn't exist
CREATE TABLE IF NOT EXISTS `brgy_record_table` (
  `brgy_record_id` int(11) NOT NULL AUTO_INCREMENT,
  `barangay_name` varchar(200) NOT NULL,
  `total_evacuess` int(11) DEFAULT 0,
  `disaster_id` int(11) NOT NULL,
  `scale` varchar(50) DEFAULT '',
  `date` date NOT NULL,
  `status` varchar(50) DEFAULT 'Evacuated',
  PRIMARY KEY (`brgy_record_id`),
  KEY `idx_barangay_disaster` (`barangay_name`, `disaster_id`),
  KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

