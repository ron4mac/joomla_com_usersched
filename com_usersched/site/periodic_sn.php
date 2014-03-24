<?php
/*
	periodic alert checker/sender
	this version does not use the Joomla core framework
*/
ini_set('max_execution_time', 60);
define('_JEXEC', 1);
define('JPATH_SITE', substr(__DIR__,0,-25));

require_once JPATH_SITE . '/configuration.php';
require_once JPATH_SITE . '/libraries/phpmailer/phpmailer.php';
require_once JPATH_SITE . '/libraries/rjuserdata/userdata.php';

$isDevel = ($_GET['develop'] == 'Y');
$simulate = ($_GET['simulate'] == 'Y');
$alertRay = array();

$config = new JConfig();
date_default_timezone_set($config->offset);

function getConfig ($item) {
	global $config;
	return $config->$item;
}

function sendMailAlert ($ausrs, $mail, $alertees) {
	$mailer = new PHPMailer(true);
	foreach ($alertees as $a) {
		if (in_array($a['id'], $ausrs)) $mailer->AddAddress($a['email']);
	}
	$mailer->SetFrom($mail['from'], $mail['fromname']);
	$mailer->Subject = $mail['subject'];
	$mailer->Body = $mail['body'];
	if (!$mailer->Send()) echo 'Mailing failed: '.$mailer->ErrorInfo;
	$mailer->__destruct();
}

function sendSmsAlert ($ausrs, $mail, $alertees) {
	$mailer = new PHPMailer(true);
	foreach ($alertees as $a) {
		if (in_array($a['id'], $ausrs)) $mailer->AddAddress($a['sms']);
	}
	$mailer->SetFrom($mail['from'], $mail['fromname']);
	$mailer->Subject = $mail['subject'];
	$mailer->Body = $mail['body'];
	if (!$mailer->Send()) echo 'Mailing failed: '.$mailer->ErrorInfo;
	$mailer->__destruct();
}

include 'periodic.php';
