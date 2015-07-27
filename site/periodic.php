<?php

function bugout ($msg, $vars='', $bufr=0) {
	global $isDevel;

	if (!$isDevel) return;

//	if ($bufr&1) {
//		@ob_end_clean();
//		ob_start();
//	}

	echo $msg;
	if ($vars) {
		echo'<pre>';var_dump($vars);echo'</pre>';
	} else echo'<br />';

//	if ($bufr&2) {
//		ob_end_flush();
//		ob_start();
//	}
}

function getTable ($db, $table, $where='', $all=false, $values='*')
{
	if ($db) {
		$rslt = $db->query('SELECT '.$values.' FROM ' . $table . ($where ? (' WHERE '.$where) : ''));
		if ($rslt) {
			if ($all) {
				$rows = array();
				while ($row = $rslt->fetchArray(SQLITE3_ASSOC))
					$rows[] = $row;
			} else {
				$rows = $rslt->fetchArray(SQLITE3_ASSOC);
			}
			$rslt->finalize();
			return $rows;
		}
	}
	return null;
}

// mark (for a day) that an alert was triggered
function markAlerted ($id, $lead, $atime, $db) {	//return;
	global $isDevel, $simulate, $alertRay;
	if ($lead < 86400) $lead = 86400;
	if ($isDevel || $simulate) {
		$alertRay[$id] = array($atime, $lead);
	} else {
		$db->execute('INSERT INTO alerted (eid,atime,lead) VALUES ('.$id.','.$atime.','.$lead.')');
	}
}

// see if the event's alert has already been triggered
function wasAlerted ($id, $stray) {
//	global $isDevel;
//	if ($isDevel) return false;
	global  $isDevel, $simulate, $alertRay;
	if ($isDevel || $simulate) return isset($alertRay[$id]);
	foreach ($stray as $st) {
		if ($st['eid'] == $id) return true;
	}
	return false;
}

// remove expired alerted sentinals (> 1 day)
function clearOldMarks ($atime, $db) {
	global $isDevel, $simulate, $alertRay;
	if ($isDevel || $simulate) {
		foreach ($alertRay as $id=>$last) {
			if (($atime - $last[0]) >= $last[1]) unset($alertRay[$id]);
		}
	} else {
		$db->execute('DELETE FROM alerted WHERE ('.$atime.' - `atime`) >= `lead`');
	}
}

bugout(date('Y/m/d H:i'), '');

if ($simulate) {
	$rangt = 259200;	// ±3 days (worth)
	$granu = 900;	// granularity 15 min
	if (isset($_GET['date']) && (($tbase = strtotime($_GET['date'])) !== false)) {
	} else {
		$tbase = (int)(time() / $granu) * $granu;
	}
	$stime = $tbase - $rangt;
	while ($stime < ($tbase+$rangt)) {
		processAlerts(0, $stime);
		$stime += $granu;
		if (($stime-$tbase)%86400 == 0) bugout('<hr />','',3);
	}
	exit();
}

// processAlerts(0, time());
// possibly go through all user databases here

function processAlerts ($id, $tt) {
	global $db, $simulate;

	bugout('@@'.date('Y/m/d H:i D',$tt), '', 1);

	$needLang = 1;

	clearOldMarks($tt, $db);

	$alertees = getTable($db,'alertees','',true);
	if (!$alertees) return;	// can't alert if no one to alert

	$alerted = getTable($db,'alerted','',true);

	$atime = $tt;

	// get event range
	$fields = 'event_id,strtotime(`start_date`) AS t_start, strtotime(`end_date`) as t_end,start_date,end_date,text,rec_type,event_pid,event_length,alert_lead,alert_user,alert_meth';
	$where = 'alert_user != \'\' AND ((substr(`end_date`,1,5) == \'9999-\')OR(`t_end` > '.$atime.')) AND (t_start - alert_lead) <= ('.$atime.' + 5)';
	$evts = getTable($db,'events', $where, true, $fields);
	
											/// @@@@@@ MIGHT WANT TO GET RECURRING EVENTS SEPARATELY
	foreach ($evts as $evt) {
		// skip if was already alerted within timeframe
		if (wasAlerted($evt['event_id'], $alerted)) continue;
		bugout($evt['start_date'].' $ '.$evt['rec_type']);
		// skip if recurring and and no hit
		if ($evt['rec_type'] && !recursNow($evt, $atime)) continue;
		// skip if XXXXX or event start was more than a day ago
		if (/*($atime + $evt['alert_lead']-$evt['t_start'])<0 ||*/ ($atime-$evt['t_start'])>86399) continue;

		bugout( '&gt;&gt; '. date('Y-m-d H:i D',$atime) );

		if ($needLang) {getAlertLang(); $needLang = 0;}
		sendAlerts($evt, $alertees);
		markAlerted ($evt['event_id'], $evt['alert_lead'], $atime, $db);
	}
}

function recursNow (&$evt, $atime) {
	bugout($evt['rec_type'].' '.$evt['start_date']);
	list($rec_pattern, $xtra) = explode('#', $evt['rec_type']);
	if ($rec_pattern == 'none') return false;
	list($type,$count,$day,$count2,$daysl) = explode('_', $rec_pattern);
//	bugout('',array($type,$count,$day,$count2,$daysl,$xtra));
	$dt = new R_DateTime($evt['start_date']);
//	bugout( '@@ '. $dt->format('Y-m-d H:i D') );
	$divsr = 1;
	switch ($type) {
		case 'day':
			$tyc = 'D';
			$divsr = $count * 86400;
			$pdelta = (int)(($atime + $evt['alert_lead'] - $evt['t_start']) / $divsr);
//			bugout($atime.':'.$evt['t_start'].':'.$pdelta.':'.$count);
			$dt->add(new DateInterval('P'.($pdelta*$count).$tyc));
			break;
		case 'week':
			$tyc = 'W';
			$divsr = $count * 604800;
			$edays = explode(',',$daysl);
			$frstt = $evt['t_start'];
			$iters = (int)(($atime - $frstt) / $divsr);
			$frstt += $iters * $divsr;
			$tgt = 0;
			while (!$tgt) {
				foreach ($edays as $eday) {
					$etim = $frstt + $eday * 86400;
					bugout('^^&gt;'.date('Y-m-d H:i',$etim));
					if ($etim >= $atime) {
						$tgt = $etim;
						break 2;
					}
				}
				$frstt += $divsr;
				if (($frstt-$evt['alert_lead'])>$atime) return false;
			}
			$dt = new R_DateTime('', null, $tgt);
			bugout('#=- '.$dt->format('Y-m-d H:i D'));
			break;
		case 'month':
			$tyc = 'M';
			$cdt = new R_DateTime(date('Y-m-d H:i',$atime /*+ $evt['alert_lead']*/));
			$dim = ($cdt->getFullYear() - $dt->getFullYear()) * 12;
			$dim += $cdt->getMonth() - $dt->getMonth();
			$nop = (int) ($dim / $count);
			$dt->add(new DateInterval('P'.($nop*$count).$tyc));
			break;
		case 'year':
			$tyc = 'Y';
			$cdt = new R_DateTime(date('Y-m-d H:i',$atime /*+ $evt['alert_lead']*/));
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
		bugout( ' * '. $dt->format('Y-m-d H:i D') );
	}
//	bugout( $dt->format('Y-m-d H:i D') );
/*	if ($daysl) {
		$sd = clone $dt;
		//echo $dt->format('Y-m-d H:i D');
		$wdys = explode(',', $daysl);
		//set to beginning of week
		$sd->sub(new DateInterval('P'.$sd->getDow().'D'));
		$bow = $sd->getDay();
		foreach ($wdys as $wdy) {
			$sd->setDay2($bow+$wdy);
			$closetime = $sd->getTimestamp();
			$diffr = $atime - $closetime + $evt['alert_lead'];
			if (($diffr<0) || ($diffr>86400)) {
				bugout( ' - '. $sd->format('Y-m-d H:i D') );
				continue;
			}
			bugout( ' = '. $sd->format('Y-m-d H:i D') );
			$dt = $sd;
			break;
//			echo $dt->format('Y-m-d H:i D');
		}
	}*/

	$closetime = $dt->getTimestamp();
	$diffr = $atime - $closetime + $evt['alert_lead'];
	if (($diffr<0) || ($diffr>86400)) {
		return false;
	}

	bugout( $atime-$closetime.'-- '.date('Y-m-d H:i',$atime).' - '. $dt->format('Y-m-d H:i D') );

	$evt['t_start'] = $closetime;
	$evt['start_date'] = $dt->format('Y-m-d H:i');
	return true;
}

function sendAlerts ($evt, $alertees) {
	global $alertLang, $isDevel;
	$sn = $_SERVER['SCRIPT_NAME'];
	$surl = getConfig('live_site');
	$ausrs = explode(',',$evt['alert_user']);

	$mail = array();
	$mail['from'] = getConfig('mailfrom');
	$mail['fromname'] = getConfig('fromname');
	$mail['subject'] = 'Calendar Alert';
//	$mail['body'] = 'This is an automatic message from "'.getConfig('sitename').'".'."\n" . $surl . "\n\n";
//	$mail['body'] = 'This is an automatic message from "'.getConfig('sitename').'" sent '.date('D j F Y g:ia').".\n" . $surl ."\n";
	$lb = "\n";
	$mail['body'] = sprintf($alertLang->_text('BLURB'), getConfig('sitename'), date('D j F Y g:ia'), $lb, $surl, $lb);
	$toTime = $evt['rec_type'] ? ($evt['t_start'] + $evt['event_length']) : $evt['t_end'];
	$mail['body'] .= formattedDateTime($evt['t_start'], $toTime) . $lb.$lb;
	$mail['body'] .= $evt['text'];

	if ($isDevel) {
		bugout('', $mail, 2);
	} else {
		if ($evt['alert_meth'] & 1) sendMailAlert($ausrs, $mail, $alertees);
		if ($evt['alert_meth'] & 2) {
			//abreviate for text
			$splt = explode("\n",$evt['text'],2);
			$mail['subject'] .= ' -- '.$splt[0];
			$mail['body'] = formattedDateTime($evt['t_start'], $toTime) . "\n\n";
			$mail['body'] .= $splt[1];
			sendSmsAlert($ausrs, $mail, $alertees);
		}
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
