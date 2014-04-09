<?php
/*
	periodic alert checker/sender
	this version uses the Joomla core framework
	a bit of hacking is needed to fire up the Joomla framework
*/
ini_set('max_execution_time', 60);
define('_JEXEC', 1);
define('JPATH_BASE', substr(__DIR__,0,-25));
chdir(JPATH_BASE);

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

$app = JFactory::getApplication('site');
$config = JFactory::getConfig();

$isDevel = ($app->input->get('develop') == 'Y');
$simulate = ($app->input->get('simulate') == 'Y');
$alertRay = array();

jimport('rjuserdata.userdata');
date_default_timezone_set ( $config->get('offset') );

function getConfig ($item) {
	global $config;
	return $config->get($item);
}

function sendMailAlert ($ausrs, $mail, $alertees) {
	$mailer = JFactory::getMailer();
	$toList = array();
	foreach ($alertees as $a) {
		if (in_array($a['id'], $ausrs)) $toList[] = $a['email'];
	}
	$mailer->sendMail($mail['from'], $mail['fromname'], $toList, $mail['subject'], $mail['body']);
}

function sendSmsAlert ($ausrs, $mail, $alertees) {
	$mailer = JFactory::getMailer();
	$toList = array();
	foreach ($alertees as $a) {
		if (in_array($a['id'], $ausrs)) $toList[] = $a['sms'];
	}
	$mailer->sendMail($mail['from'], $mail['fromname'], $toList, $mail['subject'], $mail['body']);
}

include 'periodic.php';
echo 'HALT';jexit();
?>