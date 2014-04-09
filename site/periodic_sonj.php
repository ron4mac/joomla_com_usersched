<?php

define('_JEXEC', 1);
define('JPATH_SITE', substr(__DIR__,0,-25));

require_once JPATH_SITE . '/libraries/rjuserdata/userdata.php';

class R_DateTime extends DateTime {
	public function __construct($s='now', $z=null, $t=null) {
		parent::__construct($s,$z);
		if ($t) $this->setTimestamp($t);
	}
	public function __toString() {
		return $this->format('Y-m-d H:i:s');
	}
/*
	public function diff($now = 'NOW') {
		if(!($now instanceOf DateTime)) {
			$now = new DateTime($now);
		}
		return parent::diff($now);
	}
	public function getAge($now = 'NOW') {
		return $this->diff($now)->format('%y');
	}
*/
	public function setDay2($day) {
		$new = preg_match('/^(\d\d\d\d)\-(\d\d)/', $this->format('Y-m-d'), $m);	//var_dump($m);
		$this->setDate($m[1],$m[2],$day);
	}
	public function getDay() {
		return $this->format('d') + 0;
	}
	public function getDow() {
		return $this->format('w') + 0;
	}
	public function setMonth($mth) {
		$new = preg_match('/^(\d\d\d\d)\-\d\d\-(\d\d)/', $this->format('Y-m-d'), $m);	//var_dump($m);
		$this->setDate($m[1],$mth,$m[2]);
	}
	public function getMonth() {
		return $this->format('m') + 0;
	}
	public function getFullYear() {
		return $this->format('Y') + 0;
	}
	function addTo($dobj,$inc,$mode){
		global $actb;
		$ndate = new R_DateTime($dobj->__toString());
		switch($mode){
			case 'week':
				$inc *= 7;
			case 'day':
				$ndate->setDay2($ndate->getDay() + $inc);
				if (!$dobj->getHours() && $ndate->getHours()) //shift to yesterday
					$ndate->setTime($ndate->getTime() + 60 * 60 * 1000 * (24 - $ndate->getHours()));
				break;
			case 'month': $ndate->setMonth($ndate->getMonth()+$inc); break;
			case 'year': $ndate->setYear($ndate->getFullYear()+$inc); break;
			case 'hour': $ndate->setHours($ndate->getHours()+$inc); break;
			case 'minute': $ndate->setMinutes($ndate->getMinutes()+$inc); break;
			default:
				return $actb['add_'.$mode]($dobj,$inc,$mode);
				exit();
		}
		return $ndate;
	}
}

echo date('Y/m/d H:i');
processAlerts(0);
// possibly go through all user databases here

function processAlerts () {
	$caldb = new RJUserData('sched',false,0,true,'com_usersched');
	if (!$caldb->dataExists()) return;

	// open databse r/w
	$caldb->connect();

	$db = $caldb->getDbase();

	// remove expired alerted sentinals (1 day)
	$db->query('DELETE FROM alerted WHERE atime < '.(time()-86400));

	$alertees = $caldb->getTable('alertees','',true);
	if (!$alertees) return;	// can't alert if no one to alert

	$alerted = $caldb->getTable('alerted','',true);

	$atime = time();

	// get event range
	$fields = 'event_id,strtotime(`start_date`) AS t_start,start_date,end_date,text,rec_type,event_pid,event_length,alert_lead,alert_user,alert_meth';
	$where = 'alert_user != \'\' AND t_start > '.($atime-86400);
	$evts = $caldb->getTable('events', $where, true, $fields);
	
//	echo'<pre>';
//	var_dump($alerted,$alertees);
//	var_dump($evts);
//	echo'</pre>';exit;
	
	foreach ($evts as $evt) {
//		if (wasAlerted($evt['event_id'], $alerted)) continue;
		if ($evt['rec_type'] && !recursNow($evt)) continue;

		sendAlerts($evt, $alertees);
		$db->query('INSERT INTO alerted (eid,atime) VALUES ('.$evt['event_id'].','.$atime.')');
	}
}

function wasAlerted ($id, $stray) {
	foreach ($stray as $st) {
		if ($st['eid'] == $id) return true;
	}
	return false;
}

function recursNow ($evt) {
	list($rec_pattern, $xtra) = explode('#', $evt['rec_type']);
	list($type,$count,$day,$count2,$daysl) = explode('_', $rec_pattern);
	var_dump($type,$count,$day,$count2,$daysex);
	$dt = new R_DateTime($evt['start_date']);
	switch ($type) {
		case 'day':
			$tyc = 'D';
			break;
		case 'week':
			$tyc = 'W';
			break;
		case 'month':
			$tyc = 'M';
			break;
		case 'year':
			$tyc = 'Y';
			break;
	}
	$tint = new DateInterval('P'.$count.$tyc);
	$days = array();
	if ($daysl) {
		$days = explode(',',$daysl);
		//!! need to handle monday as begin of week
	}
	
	echo '<br />== '. $dt->format('Y-m-d H:i D');
	for ($i=0;$i<20;$i++) {
		if ($count2) {
			$wk = $count2;
			$dt->setDay2(1);	// set to 1st of month
			//echo '<br /> @1 '. $dt->format('Y-m-d H:i D');
			$wk = ($wk - 1) * 7;	// offset to Nth
			$cday = $dt->getDow();	// get dow for 1st of month
			$nday = $day * 1 + $wk - $cday + 1;
			//echo " > $wk $cday $nday";
			$dt->setDay2($nday <= $wk ? ($nday + 7) : $nday);
		}
		echo '<br />'. $dt->format('Y-m-d H:i D');
		$dt->add($tint);
	}
	return true;
}

function sendAlerts ($evt, $alertees) {
	if ($evt['alert_meth'] & 1) sendMailAlert($evt, $alertees);
	if ($evt['alert_meth'] & 2) sendSmsAlert($evt, $alertees);
}

function sendMailAlert ($evt, $alertees) {
	//$mailer = JFactory::getMailer();
	$from = 'ron@rnp-web.net';
	$ausrs = explode(',',$evt['alert_user']);
	$toList = array();
	foreach ($alertees as $a) {
		if (in_array($a['id'], $ausrs)) $toList[] = $a['email'];
	}
	echo '<h2>'.implode(',',$toList).' +++ '.$evt['text'].'</h2>';
//	$mailer->addRecipient($toList,array('Ron Crans'));
//	$mailer->addReplyTo($from,'Ron Crans');
//	$mailer->setSender(array($from,'Ron Crans'));
//	$mailer->setSubject('Calendar Alert');
//	$mailer->setBody($evt['text']);
//	$mailer->Send();
	//$config = JFactory::getConfig();
//	$mailer->sendMail($from, 'Ron Crans', $toList, 'Calendar Alert', $evt['text']);
	//$mailer->sendMail($config->get('mailfrom'), $config->get('fromname'), $toList, $config->get('sitename').'_SO: Calendar Alert', $evt['text']);
//	echo'<pre>';var_dump($mailer);echo'</pre>';

	$headers = 'From: ' . $from . "\r\n";
	mail(implode(',', $toList), '_SONJ: Calendar Alert', $evt['text'], $headers);
}

function sendSmsAlert ($evt, $alertees) {
	$mailer = JFactory::getMailer();
	$ausrs = explode(',',$evt['alert_user']);
	$toList = array();
	foreach ($alertees as $a) {
		if (in_array($a['id'], $ausrs)) $toList[] = $a['sms'];
	}
	echo '<h2>'.implode(',',$toList).' +++ '.$evt['text'].'</h2>';
	$config = JFactory::getConfig();
	$mailer->sendMail($config->get('mailfrom'), $config->get('fromname'), $toList, $config->get('sitename').': Calendar Alert', $evt['text']);
}

function mailAlert($event, $who)
{
	$subj = 'Calendar event alert';
	$body = 'This is an automatic message from "".'."\n".'http://rnp-web.net/j301/' . "\n\n";
	$body .= $event->get_date_string(true) . ' at ' . $event->get_time_span_string() . "\n";
	$body .= $event->get_desc() ? $event->get_desc() : '[ no description ]';
	$from = 'Our-Stuff-Calendar <no-reply@rjconline.net>';
	$tolist = explode(',', $event->get_alertusers());
	foreach ($tolist as $uid) {
		$user = $phpcdb->get_user($uid);
		$to[] = $user->get_emailaddr();
	}
	$headers = 'From: ' . $from . "\r\n";
	mail(implode(',', $to), $subj, $body, $headers);
	$phpcdb->set_occurrence_has_fired($event->get_oid(), 1);
}

function mailEventAlert($event)
{
	global $phpc_includes_path, $phpcdb;

	require_once("$phpc_includes_path/phpcuser.class.php");

	$calcfg = $phpcdb->get_calendar_config($event->get_cid());
	$calttl = $calcfg['calendar_title'];

	$to = array();
	$subj = 'Calendar event alert: ' . $event->get_subject();
	$body = 'This is an automatic message from "'.$calttl.'".'."\n".'http://rjconline.net/pcal2/' . "\n\n";
	$body .= $event->get_date_string(true) . ' at ' . $event->get_time_span_string() . "\n";
	$body .= $event->get_desc() ? $event->get_desc() : '[ no description ]';
	$from = 'PHP-Calendar <no-reply@rjconline.net>';
	$tolist = explode(',', $event->get_alertusers());
	foreach ($tolist as $uid) {
		$user = $phpcdb->get_user($uid);
		$to[] = $user->get_emailaddr();
	}
	$headers = 'From: ' . $from . "\r\n";
	mail(implode(',', $to), $subj, $body, $headers);
	$phpcdb->set_occurrence_has_fired($event->get_oid(), 1);
}

function smsEventAlert($event)
{
	global $phpc_includes_path, $phpcdb;

	require_once("$phpc_includes_path/phpcuser.class.php");

	$to = array();
	$subj = 'Alert: ' . $event->get_subject();
	$body = '';
	if ($event->get_alertlead() > 0) $body .= $event->get_date_string(true) . ' at ' . $event->get_time_span_string() . "\n";
	$body .= $event->get_desc();
	$from = 'no-reply@rjconline.net';
	$tolist = explode(',', $event->get_alertusers());
	foreach ($tolist as $uid) {
		$user = $phpcdb->get_user($uid);
		$to[] = $user->get_smsaddr();
	}
	$headers = 'From: ' . $from . "\r\n";
	mail(implode(',', $to), $subj, $body, $headers, '-f "no-reply@rjconline.net"');
	$phpcdb->set_occurrence_has_fired($event->get_oid(), 1);
}
