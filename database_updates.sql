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

