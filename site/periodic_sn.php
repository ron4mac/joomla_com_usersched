<?php
/*
	periodic alert checker/sender
	this version does not use the Joomla core framework
*/
exit();
ini_set('max_execution_time', 60);
define('_JEXEC', 1);
define('JPATH_SITE', substr(__DIR__,0,-25));

require_once JPATH_SITE . '/configuration.php';
require_once JPATH_SITE . '/libraries/vendor/phpmailer/phpmailer/class.phpmailer.php';
//require_once JPATH_SITE . '/libraries/phpmailer/phpmailer.php';
require_once JPATH_SITE . '/libraries/rjuserdata/userdata.php';

$isDevel = ($_GET['develop'] == 'Y');
$simulate = ($_GET['simulate'] == 'Y');
$alertRay = array();
$alertLang = new AlertLang('en-GB');

$config = new JConfig();
date_default_timezone_set($config->offset);

function getConfig ($item) {
	global $config;
	return $config->$item;
}

function getAlertLang () {
	global $db, $alertLang;
	$rslt = $db->query('SELECT `value` FROM `options` WHERE `id`=1');
	$vals = $rslt->fetchArray(SQLITE3_ASSOC)['value'];
	$rslt->finalize();
	$opts = unserialize($vals);
	$tag = $opts['alert_lang'];
	$alertLang->extractLang($tag);
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
	//$mailer->__destruct();
	unset($mailer);
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
	//$mailer->__destruct();
	unset($mailer);
}

include 'periodic.php';

$db = null;
if ($dirh = opendir(JPATH_SITE . '/userstor')) {
	while (false !== ($entry = readdir($dirh))) {
		if ($entry != '.' && $entry != '..' && is_dir(JPATH_SITE.'/userstor/'.$entry)) {
			if ($entry[0] == '@' || $entry[0] == '_') {
				$grp = $entry[0] == '_';
				$caldb = new RJUserData('sched',false,substr($entry,1),$grp,'com_usersched');
				if ($caldb->dataExists()) {
					// open databse r/w
					$caldb->connect();
					$db = $caldb->getDbase();
					processAlerts(0, time());
				}
			}
		}
	}
	closedir($dirh);
}

class AlertLang {
	protected $lang = array();
	protected $tag = '';

	public function __construct ($tag) {
		$this->extractLang($tag);
	}

	public function extractLang ($tag) {
		if ($tag == $this->tag) return;
		$fpath = __DIR__.'/language/'.$tag.'/'.$tag.'.com_usersched.ini';
		if (file_exists($fpath)) {
			$this->tag = $tag;
			$lines = file($fpath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			foreach ($lines as $line) {
				if (preg_match('/^COM_USERSCHED_ALERT_(\w+)="(.+)"$/', $line, $mtch)) {
					$this->lang[$mtch[1]] = $mtch[2];
				}
			}
		}
	}

	public function _text ($stub) {
		return $this->lang[$stub];
	}
}