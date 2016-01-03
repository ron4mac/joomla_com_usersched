<?php
defined('_JEXEC') or die;

function bugout ($msg, $vars='') {
	global $isDevel;
	if (!$isDevel) return;
	echo $msg;
	if ($vars) {
		echo'<pre>';var_dump($vars);echo'</pre>';
	} else echo'<br />';
}

function gatherEvents ($ddb, $rBeg, $rEnd, $useLead=true, $useRecur=true, $where='')
{
	$eray = array();
	if (!$ddb->dataExists()) return $eray;
	
}

/*
	with a recurring event, determine if an instance occurs within the time range
		$evt - the event to be examined
		$rBeg - the start of the time range (unix)
		$rEnd - the end of the time range (unix)
		$lasto - if true, return last possible occurance, otherwise return the first possible
	lead time is not used for calculation, so adjust $rBeg $rEnd to account for lead time if necessary
*/
function recursNow (&$evt, $rBeg, $rEnd, $lasto=true) {
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
			$pdelta = (int)(($rBeg - $evt['t_start']) / $divsr);
			bugout($rBeg.':'.$evt['t_start'].':'.$pdelta.':'.$count);
			if ($pdelta<0) {
				$dt->sub(new DateInterval('P'.-($pdelta*$count).$tyc));
			} else {
				$dt->add(new DateInterval('P'.($pdelta*$count).$tyc));
			}
			break;
		case 'week':
			$tyc = 'W';
			$divsr = $count * 604800;
			$pdelta = (int)(($rBeg - $evt['t_start']) / $divsr);
//			bugout($rBeg.':'.$evt['t_start'].':'.$pdelta.':'.$count);
			$dt->add(new DateInterval('P'.($pdelta*$count).$tyc));
			break;
		case 'month':
			$tyc = 'M';
			$cdt = new R_DateTime(date('Y-m-d H:i',$rBeg));
			$dim = ($cdt->getFullYear() - $dt->getFullYear()) * 12;
			$dim += $cdt->getMonth() - $dt->getMonth();
			$nop = (int) ($dim / $count);
			$dt->add(new DateInterval('P'.($nop*$count).$tyc));
			break;
		case 'year':
			$tyc = 'Y';
			$cdt = new R_DateTime(date('Y-m-d H:i',$rBeg));
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
			$utime = $sd->getTimestamp();
			if ($utime>$rBeg && $utime<$rEnd) {
				$dt = $sd;
				if (!$lasto) break;
			}
		}
	}
//	bugout( '# - '.$dt->format('Y-m-d H:i D') );

	$closetime = $dt->getTimestamp();
//	$diffr = $rBeg-$closetime;
//	if (($diffr<0) || ($diffr>86400)) {
//		return false;
//	}

	if ($closetime<$rBeg || $closetime>$rEnd) return false;

	bugout( $rBeg-$closetime.'-- '.date('Y-m-d H:i',$rBeg).' - '. $dt->format('Y-m-d H:i D') );

	$evt['t_start'] = $closetime;
	$evt['start_date'] = $dt->format('Y-m-d H:i');
	return true;
}


class R_DateTime extends DateTime {
	public function __construct($s='now', $z=null, $t=null) {
		parent::__construct($s,$z);
		if ($t) $this->setTimestamp($t);
	}
	public function __toString() {
		return $this->format('Y-m-d H:i:s');
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
