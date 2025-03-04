BEGIN TRANSACTION;
CREATE TABLE `events` (
	`event_id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	`start_date` datetime NOT NULL,
	`end_date` datetime NOT NULL,
	`text` TEXT NOT NULL,
	`rrule` TEXT,
	`duration` INTEGER,
	`recurring_event_id` INTEGER,
	`original_start` datetime,
	`deleted` BOOLEAN,
	`category` INTEGER,
	`user` INTEGER NOT NULL,
	`lat` float(10,6) DEFAULT 0,
	`lng` float(10,6) DEFAULT 0,
	`alert_lead` INTEGER,
	`alert_user` BLOB,
	`alert_meth` TEXT
	);
CREATE TABLE `options` (
	`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	`name` TEXT NOT NULL,
	`value` TEXT NOT NULL
	);
CREATE TABLE `categories` (
	`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	`name` TEXT NOT NULL,
	`txcolor` varchar(15),
	`bgcolor` varchar(15)
	);
CREATE TABLE `alertees` (
	`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	`name` TEXT NOT NULL,
	`email` TEXT,
	`sms` TEXT
	);
CREATE TABLE `alerted` (
	`eid` INTEGER NOT NULL,
	`atime` INTEGER NOT NULL,
	`lead` INTEGER NOT NULL
	);
COMMIT