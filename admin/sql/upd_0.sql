ALTER TABLE `events` ADD COLUMN `rrule` TEXT;
ALTER TABLE `events` ADD COLUMN `duration` INTEGER;
ALTER TABLE `events` ADD COLUMN `recurring_event_id` INTEGER;
ALTER TABLE `events` ADD COLUMN `original_start` datetime;
ALTER TABLE `events` ADD COLUMN `deleted` BOOLEAN;
PRAGMA user_version=1
