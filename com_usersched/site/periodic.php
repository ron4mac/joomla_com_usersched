<?php
defined('_JEXEC') or die;

require_once 'helpers/events.php';

function old_bugout ($msg, $vars='') {
	global $isDevel;
	if (!$isDevel) return;
	echo $msg;
	if ($vars) {
		echo'<pre>';var_dump($vars);echo'</pre>';
	} else echo'<br />';
}

function markAlerted ($id, $atime, $db) {
	global $simulate, $alertRay;
	if ($simulate) {
		$alertRay[$id] = $atime;
	} else {
		$db->query('INSERT INTO alerted (eid,atime) VALUES ('.$id.','.$atime.')');
	}
}

function wasAlerted ($id, $stray) {
//	global $isDevel;
//	if ($isDevel) return false;
	global  $simulate, $alertRay;
	if ($simulate) return isset($alertRay[$id]);
	foreach ($stray as $st) {
		if ($st['eid'] == $id) return true;
	}
	return false;
}

// remove expired sentinals (> 1 day)
function clearOldMarks ($atime, $db) {
	global $simulate, $alertRay;
	if ($simulate) {
		foreach ($alertRay as $id=>$last) {
			if ($last < ($atime-86400)) unset($alertRay[$id]);
		}
	} else {
		$db->query('DELETE FROM alerted WHERE atime < '.($atime-86400));
	}
}

bugout('[['.date('Y/m/d H:i').']]');

if ($simulate) {
	$rangt = 15724800;	//+-6mo
	$tbase = (int)(time() / 1800) * 1800;
	$stime = $tbase - $rangt;
	while ($stime < ($tbase+$rangt)) {
		processAlerts(0, $stime);
		$stime += 1800;
	}
	exit();
}

processAlerts(0, time());
// possibly go through all user databases here

function processAlerts ($id, $tt) {
	global $simulate;
	$caldb = new RJUserData('sched',false,0,true,'com_usersched');
	if (!$caldb->dataExists()) return;

	// open databse r/w
	$caldb->connect();

	$db = $caldb->getDbase();

	clearOldMarks($tt, $db);

	$alertees = $caldb->getTable('alertees','',true);
	if (!$alertees) return;	// can't alert if no one to alert

	$alerted = $caldb->getTable('alerted','',true);

	$atime = $tt;

	// get event range
//	$fields = 'event_id,strtotime(`start_date`) AS t_start, strtotime(`end_date`) as t_end,start_date,end_date,text,rec_type,event_pid,event_length,alert_lead,alert_user,alert_meth';
	$fields = 'strtotime(`start_date`) AS t_start, strtotime(`end_date`) as t_end, *';
//	$where = 'alert_user != \'\' AND t_start > '.($atime-86400);
	$where = 'alert_user != \'\' AND (t_start - alert_lead) < '.$atime;	//and not more than a day old
	$evts = $caldb->getTable('events', $where, true, $fields);
	
											/// @@@@@@ MIGHT WANT TO GET RECURRING EVENTS SEPARATELY
	foreach ($evts as $evt) {
		if (wasAlerted($evt['event_id'], $alerted)) continue;
//		if ($evt['rec_type'] && !recursNow($evt, $atime)) continue;
		$rEnd = $atime - $evt['alert_lead'];
		$rBeg = $rEnd - 84600;
		if ($evt['rec_type']) {
			if (!recursNow($evt, $rBeg, $rEnd, true)) continue;
		} else {
			//var_dump($atime,$rBeg,$rEnd,$evt);
			if (($atime-$evt['t_start']-$evt['alert_lead']) > 86400) continue;
		}

//		bugout( '&lt;&lt; '. date('Y-m-d H:i D',$atime) );

//		if (($atime + $evt['alert_lead']-$evt['t_start'])<0 || ($atime-$evt['t_start'])>86400) continue;

//		bugout( '&gt;&gt; '. date('Y-m-d H:i D',$atime) );
	
		sendAlerts($evt, $alertees);
		markAlerted ($evt['event_id'], $atime, $db);
	}
}

function old_recursNow (&$evt, $atime) {
	list($rec_pattern, $xtra) = explode('#', $evt['rec_type']);
	list($type,$count,$day,$count2,$daysl) = explode('_', $rec_pattern);
//	bugout('',array($type,$count,$day,$count2,$daysl,$xtra));
	$dt = new R_DateTime($evt['start_date']);
//	bugout( '@@ '. $dt->format('Y-m-d H:i D') );
	$divsr = 1;
	switch ($type) {
		case 'day':
			$tyc = 'D';
			$divsr = $count * 86400;
			$pdelta = (int)(($atime + $evt['alert_lead'].- $evt['t_start']) / $divsr);
//			bugout($atime.':'.$evt['t_start'].':'.$pdelta.':'.$count);
			$dt->add(new DateInterval('P'.($pdelta*$count).$tyc));
			break;
		case 'week':
			$tyc = 'W';
			$divsr = $count * 604800;
			$pdelta = (int)(($atime + $evt['alert_lead'] - $evt['t_start']) / $divsr);
//			bugout($atime.':'.$evt['t_start'].':'.$pdelta.':'.$count);
			$dt->add(new DateInterval('P'.($pdelta*$count).$tyc));
			break;
		case 'month':
			$tyc = 'M';
			$cdt = new R_DateTime(date('Y-m-d H:i',$atime + $evt['alert_lead']));
			$dim = ($cdt->getFullYear() - $dt->getFullYear()) * 12;
			$dim += $cdt->getMonth() - $dt->getMonth();
			$nop = (int) ($dim / $count);
			$dt->add(new DateInterval('P'.($nop*$count).$tyc));
			break;
		case 'year':
			$tyc = 'Y';
			$cdt = new R_DateTime(date('Y-m-d H:i',$atime + $evt['alert_lead']));
			$diy = $cdt->getFullYear() - $dt->getFullYear();
			$nop = (int) ($diy / $count);
			$dt->add(new DateInterval('P'.($nop*$count).$tyc));
			break;
	}

	$days = array();
	if ($daysl) {
		$days = explode(',',$daysl);
		//!! need to handle monday as begin of week
	}

//	bugout( '== '. $dt->format('Y-m-d H:i D') );

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
//	bugout( $dt->format('Y-m-d H:i D') );
	if ($daysl) {
		$sd = clone $dt;
		$wdys = explode(',', $daysl);
		//set to beginning of week
		$sd->sub(new DateInterval('P'.$sd->getDow().'D'));
		$bow = $sd->getDay();
		foreach ($wdys as $wdy) {
			$sd->setDay2($bow+$wdy);
//			bugout( ' - '. $sd->format('Y-m-d H:i D') );
		}
	}

	$closetime = $dt->getTimestamp();
	$diffr = $atime-$closetime;
	if (($diffr<0) || ($diffr>86400)) {
		return false;
	}

	bugout( $atime-$closetime.'-- '.date('Y-m-d H:i',$atime).' - '. $dt->format('Y-m-d H:i D') );

	$evt['t_start'] = $closetime;
	$evt['start_date'] = $dt->format('Y-m-d H:i');
	return true;
}

function sendAlerts ($evt, $alertees) {
	global $isDevel;
	$sn = $_SERVER['SCRIPT_NAME'];
	$surl = getConfig('live_site');
	$ausrs = explode(',',$evt['alert_user']);

	$mail = array();
	$mail['from'] = getConfig('mailfrom');
	$mail['fromname'] = getConfig('fromname');
	$mail['subject'] = 'Calendar Alert';
	$mail['body'] = 'This is an automatic message from "'.getConfig('sitename').'".'."\n" . $surl . "\n\n";
	$toTime = $evt['rec_type'] ? ($evt['t_start'] + $evt['event_length']) : $evt['t_end'];
	$mail['body'] .= formattedDateTime($evt['t_start'], $toTime) . "\n";
	$mail['body'] .= $evt['text'];

	if ($isDevel) {
		bugout('',$mail);
	} else {
		if ($evt['alert_meth'] & 1) sendMailAlert($ausrs, $mail, $alertees);
		if ($evt['alert_meth'] & 2) sendSmsAlert($ausrs, $mail, $alertees);
	}
}

function formattedDateTime ($from, $to=0) {
	if ($to-$from == 86400) {
		return date('D j F Y', $from);
	}
	$fdt = date('D j F Y g:ia', $from);
	if ($to) {
		if ($to-$from > 86400) {
			if ((date('Hi',$from).date('Hi',$to)) == '00000000') {
				return date('D j F Y', $from).' - '.date('D j F Y', $from);
			} else {
				$fdt .= ' - '.date('D j F Y g:ia', $to);
			}
		} else {
			$fdt .= ' to '.date('g:ia', $to);
		}
	}
	return $fdt;
}

/*
class R_DateTime extends DateTime {
	public function __construct($s='now', $z=null, $t=null) {
		parent::__construct($s,$z);
		if ($t) $this->setTimestamp($t);
	}
	public function __toString() {
		return $this->format('Y-m-d H:i:s');
	}
	public function diff($now = 'NOW') {
		if(!($now instanceOf DateTime)) {
			$now = new DateTime($now);
		}
		return parent::diff($now);
	}
	public function getAge($now = 'NOW') {
		return $this->diff($now)->format('%y');
	}
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
*/