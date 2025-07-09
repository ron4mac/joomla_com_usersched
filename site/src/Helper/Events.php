<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.0
*/
namespace RJCreations\Component\Usersched\Site\Helper;

defined('_JEXEC') or die;

use RJCreations\Component\Usersched\Site\RRule;

require_once JPATH_COMPONENT.'/classes/rdatetime.php';

abstract class Events
{

	public static function bugout ($msg, $vars='') {
		if (!RJC_DEVR) return;
		echo $msg;
		if ($vars) {
			echo'<pre>';var_dump($vars);echo'</pre>';
		} else echo'<br />';
	}

	public static function gatherEvents ($ddb, $rBeg, $rEnd, $useLead=true, $useRecur=true, $where='')
	{
		$eray = [];
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
	public static function recursNow (&$evt, $rBeg, $rEnd, $lasto=true) {	$lasto=false;
		$rr = new RRule\RRule($evt['rrule'], $evt['start_date']);
		$occ = $rr->getOccurrencesBetween($rBeg, $rEnd, 1);
		if ($occ) {
			//echo'<xmp>';var_dump($occ);echo'</xmp>';
			$evt['t_start'] = $occ[0]->getTimestamp();
			$evt['start_date'] = $occ[0]->format('Y-m-d H:i');
			return true;
		} else {
			return false;
		}
	}


	public static function old_recursNow (&$evt, $rBeg, $rEnd, $lasto=true) {	$lasto=false;
		list($rec_pattern, $xtra) = array_pad(explode('#', $evt['rec_type']),2,null);
		list($type,$count,$day,$count2,$daysl) = array_pad(explode('_', $rec_pattern),5,null);
		$dnm = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
	//	self::bugout('',array($type,$count,$day,$count2,$daysl,$xtra));
		$dt = new R_DateTime($evt['start_date']);
		$dtts = $dt->getTimestamp();
		$rBdt = new R_DateTime();
		$rBdt->setTimestamp($rBeg);
	//	self::bugout( '@@ '. $dt->format('Y-m-d H:i D') );
		$divsr = 1;
		switch ($type) {
			case 'day':
			//	echo'<pre>';var_dump($evt);echo'</pre>';
				$divsr = $count * 86400;
				$pdelta = (int)(($rBeg - $evt['t_start']) / $divsr);
			//	self::bugout($rBeg.':'.$evt['t_start'].':'.$pdelta.':'.$count);
				if ($pdelta<0) {
					$dt->sub(new \DateInterval('P'.-($pdelta*$count).'D'));
				} else {
					$dt->add(new \DateInterval('P'.($pdelta*$count).'D'));
				}
				do {
					$ts = $dt->getTimestamp();
					if ($ts >= $evt['t_start'] && $ts >= $rBeg) break 2;
					$dt->modify("+$count days");
				} while ($dt->getTimestamp() < $rEnd);
				break;
			case 'week':
			//	echo'<pre>';var_dump($evt);echo'</pre>';
			//	self::bugout('daysl ' . $daysl);
				// calc time between instances
				$divsr = $count * 604800;
				// calc number of instances before this date range
	//			$pdelta = (int)(($rBeg - $evt['t_start']) / $divsr);
				$pdelta = (int)ceil(($rBeg - $evt['t_start']) / $divsr);	// - 2;
				$pdelta = (int)(($rBeg - $evt['t_start']) / $divsr);
			//	if ($pdelta<0) $pdelta = 1; 
			//	self::bugout('diff ' . $rBeg - $evt['t_start']);
			//	self::bugout($rBeg.':'.$evt['t_start'].':'.$pdelta.':'.$count);
				// add prior occurences to arrive at next occurence data
				if ($pdelta<0) {
					$dt->sub(new \DateInterval('P'.(abs($pdelta+1)*$count).'W'));
				} else {
					$dt->add(new \DateInterval('P'.($pdelta*$count).'W'));
				}
				do {
					foreach (explode(',',$daysl) as $dow) {
						//$dt->modify('next '.$dnm[$dow]);
						$dt->nextDow($dow, $count);
					//	self::bugout("DOW $dow ".$dt->format('Y-m-d H:i D'));
						$ts = $dt->getTimestamp();
						if ($ts >= $evt['t_start'] && $ts >= $rBeg) break 3;
					}
					$dt->modify("+$count ".$dnm[0]);
				} while ($dt->getTimestamp() < $rEnd);
				break;
			case 'month':
	//			echo'<pre>';var_dump($evt);echo'</pre>';
	//			echo $dt->format('Y-m-d H:i D'),'<br>';
				// bump up to within range
				$dt->setYear($rBdt->getFullYear());
				$dt->setMonth($rBdt->getMonth());
	//			echo $dt->format('Y-m-d H:i D'),'<br>';
				$mi = new \DateInterval('P'.$count.'M');
				do {
					if ($day) {
						$dt->setDay2(1);
						$dow = $dt->getDow();
						//$dt->setDay2($count2*7-$day+$dow);
						$adj = ($count2-1)*7+$day-$dow;
						$adj += ($dow>$day)?7:0;
						$dt->add(new \DateInterval('P'.$adj.'D'));
	//					echo "$count2 - $day - $dow - $adj : ";
					}
	//				echo $rBeg,'/',$dt->getTimestamp(),' ';
	//				echo $dt->format('Y-m-d H:i D'),'<br>';
					if ($dt->getTimestamp() > $rBeg) break 2;
					$dt->add($mi);
				} while ($dt->getTimestamp() < $rEnd);
				break;
			case 'year':
	//			echo'<pre>';var_dump($evt);echo'</pre>';
				$cdt = new R_DateTime(date('Y-m-d H:i',$rBeg));
				$diy = $cdt->getFullYear() - $dt->getFullYear();
				$nop = (int) ($diy / $count);
				$dt->add(new \DateInterval('P'.($nop*$count).'Y'));
				if ($day) {
					$dt->modify("+$count2 ".$dnm[$day]);
				}
				break;
		}

	//	self::bugout( '# - '.$dt->format('Y-m-d H:i D') );

		$closetime = $dt->getTimestamp();

		if ($closetime<$rBeg || $closetime>$rEnd) return false;

	//	self::bugout( $rBeg-$closetime.' -- '.date('Y-m-d H:i',$rBeg).' - '. $dt->format('Y-m-d H:i D') );

		// adjust the event for correct instance display
		$evt['t_start'] = $closetime;
		$evt['start_date'] = $dt->format('Y-m-d H:i');

		return true;
	}
}
