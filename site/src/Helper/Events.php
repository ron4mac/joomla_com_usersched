<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
namespace RJCreations\Component\Usersched\Site\Helper;

defined('_JEXEC') or die;

use RJCreations\Component\Usersched\Site\RRule;

require_once JPATH_SITE.'/components/com_usersched/classes/rdatetime.php';

abstract class Events
{

	public static function bugout ($msg, $vars='')
	{
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
		return null;
	}

	/*
		with a recurring event, determine if an instance occurs within the time range
			$evt - the event to be examined
			$rBeg - the start of the time range (unix)
			$rEnd - the end of the time range (unix)
			$lasto - if true, return last possible occurance, otherwise return the first possible
		lead time is not used for calculation, so adjust $rBeg $rEnd to account for lead time if necessary
	*/
	public static function recursNow (array &$evt, $rBeg, $rEnd, $lasto=true) {
		$lasto=false;
		$rr = new RRule\RRule($evt['rrule'], $evt['start_date']);
		$occ = $rr->getOccurrencesBetween($rBeg, $rEnd, 1);
		if ($occ) {
			//echo'<xmp>';var_dump($occ);echo'</xmp>';
			$evt['t_start'] = $occ[0]->getTimestamp();
			$evt['start_date'] = $occ[0]->format('Y-m-d H:i');
			return true;
		}
		return false;
	}

}
