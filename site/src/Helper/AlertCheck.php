<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.0
*/
namespace RJCreations\Component\Usersched\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;

use RJCreations\Component\Usersched\Site\RRule;

require_once JPATH_COMPONENT.'/classes/rdatetime.php';

class AlertCheck {

//	const DNM = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
	const TENMINSECS = 600;
	const DAYSECS = 86400;
//	const WEEKSECS = 604800;

	protected $db;
	protected $bug;
	protected $config;
	protected $alertees;

	public function __construct ($dbp, $cfg, $bug=false)
	{
		$this->bug = $bug;
		$this->config = $cfg;
		$this->bugout('@@@@@@@ DBASE '.basename(dirname($dbp,2)).'/'.basename(dirname($dbp)));
		$opt = ['driver'=>'sqlite','host'=>'','user'=>'','password'=>'','database'=>$dbp,'prefix'=>''];
		$db = DatabaseDriver::getInstance($opt);
		$db->connect();
		$db->getConnection()->sqliteCreateFunction('strtotime', 'strtotime', 1);
		$this->db = $db;
	}

	public function processAlerts ($time)
	{
		$this->bugout("CURTIME $time ".date(DATE_RFC822,$time));

		$this->alertees = $this->getTable('alertees');
		if (!$this->alertees) {	// can't alert if no one to alert
			$this->bugout('<br><br>');
			return;
		}

		// remove expired alerted sentinals (> 1 day)
//		$this->db->setQuery('DELETE FROM alerted WHERE ('.$time.' - atime + 5)> lead'/*.self::DAYSECS*/)->execute();
		$this->db->setQuery('DELETE FROM alerted WHERE ('.$time.' - atime)>'.self::DAYSECS)->execute();

		$alerted = $this->getTable('alerted','*','','eid');

		$atime = $time;

		// get event range
		$fields = '*, strtotime(start_date) AS t_start, strtotime(end_date) as t_end';
		//$where = 'alert_user != \'\' AND ((substr(end_date,1,5) == \'9999-\')OR(t_end > '.$atime.')) AND ((t_start - alert_lead) <= ('.$atime.' + 5))';
		$where = 'alert_user != \'\' AND ((substr(end_date,1,5) == \'9999-\') OR (t_end > '.$atime.')) AND ((t_start - alert_lead) <= '.$atime.')';
		$evts = $this->getTable('events', $fields, $where);
//		var_dump($atime,$evts);
												/// @@@@@@ MIGHT WANT TO GET RECURRING EVENTS SEPARATELY
		foreach ($evts as $evt) {
			$this->bugout('<br>Event: '.$evt['text']);
			// skip if was already alerted within timeframe
			if ($this->wasAlerted($evt['event_id'], $alerted)) continue;
			// skip recurring old type events
			if (!empty($evt['rec_type']) && empty($evt['rrule'])) continue;
			// skip if recurring and and no hit
			if (!empty($evt['rrule']) && !$this->recursNow($evt, $atime-self::TENMINSECS, $atime+self::TENMINSECS)) continue;
			// skip if XXXXX or event start was more than a day ago
//			if (/*($atime + $evt['alert_lead']-$evt['t_start'])<0 ||*/ ($atime-$evt['t_start'])>86399) continue;

			$this->bugout('ALERT '.$evt['start_date']);
			$this->sendAlerts($evt, $atime);
		//	$this->markAlerted($evt['event_id'], $evt['t_start']-$evt['alert_lead'], $evt['alert_lead']+$evt['t_end']-$evt['t_start'] /*$atime*/);
//			$this->markAlerted($evt['event_id'], $evt['t_start']/* -$evt['alert_lead'] */, max($evt['alert_lead'],self::DAYSECS));
			$this->markAlerted($evt['event_id'], $atime, max($evt['alert_lead'],self::DAYSECS));
		}
		$this->bugout('<br><br>');
	}

	private function recursNow (&$evt, $rBeg, $rEnd)
	{
		$this->bugout('Recursing: ',[$rBeg, date(DATE_RFC822,$rBeg), $rEnd, date(DATE_RFC822,$rEnd)/*, $evt*/]);
		$rr = new RRule\RRule($evt['rrule'], $evt['start_date']);
		$occ = $rr->getOccurrencesBetween($rBeg - $evt['alert_lead'], $rEnd, 1);
		if ($occ) {
			//echo'<xmp>';var_dump($occ);echo'</xmp>';
			$evt['t_start'] = $occ[0]->getTimestamp();
			$evt['start_date'] = $occ[0]->format('Y-m-d H:i');
			return true;
		} else {
			return false;
		}
	}

	private function old_recursNow (&$evt, $rBeg, $rEnd)
	{
		if (trim($evt['rec_type']) == 'none') return false;
		list($rec_pattern, $xtra) = explode('#', $evt['rec_type']);
		if ($rec_pattern == 'none') return false;
		list($type,$count,$day,$count2,$daysl) = explode('_', $rec_pattern);
	//	$this->bugout('PATTERN',[$type,$count,$day,$count2,$daysl]);
		$this->bugout('RECTYPE '.$evt['rec_type']);
		$this->bugout('START '.$evt['start_date']);
		$dt = new R_DateTime($evt['start_date']);
		$divsr = 1;
		switch ($type) {
			case 'day':
				$divsr = $count * self::DAYSECS;
				$pdelta = (int)floor(($rBeg + $evt['alert_lead'] - $evt['t_start']) / $divsr);
				$dt->add(new \DateInterval('P'.($pdelta*$count).'D'));
				$this->bugout('DAYCAN '.$dt->format('Y-m-d H:i D'));
				break;
			case 'week':
				$divsr = $count * self::WEEKSECS;
				$pdelta = (int)(($rBeg - $evt['t_start']) / $divsr);
				// add prior occurences to arrive at next occurence date
				if ($pdelta<0) {
					$dt->sub(new \DateInterval('P'.(abs($pdelta+1)*$count).'W'));
				} else {
					$dt->add(new \DateInterval('P'.($pdelta*$count).'W'));
				}
				$this->bugout('TSTART',[$divsr,$dt->format('Y-m-d H:i D')]);
				do {
					foreach (explode(',',$daysl) as $dow) {
						$dt->nextDow($dow, $count);
						$this->bugout('NEXTDOW '.$dt->format('Y-m-d H:i D'));
						$ts = $dt->getTimestamp();
						if ($ts >= $evt['t_start'] && $ts >= $rBeg) break 3;
					}
					$dt->modify("+$count ".self::DNM[0]);
					$this->bugout('NEXTWOO '.$dt->format('Y-m-d H:i D'));
				} while ($dt->getTimestamp() < $rEnd);

				break;
			case 'month':
				$cdt = new R_DateTime(date('Y-m-d H:i',$rBeg /*+ $evt['alert_lead']*/));
				$dim = ($cdt->getFullYear() - $dt->getFullYear()) * 12;
				$dim += $cdt->getMonth() - $dt->getMonth();
				$nop = (int) ($dim / $count);
				$dt->add(new \DateInterval('P'.($nop*$count).'M'));
				break;
			case 'year':
				$cdt = new R_DateTime(date('Y-m-d H:i',$rBeg /*+ $evt['alert_lead']*/));
				$diy = $cdt->getFullYear() - $dt->getFullYear();
				$nop = (int) ($diy / $count);
				$dt->add(new \DateInterval('P'.($nop*$count).'Y'));
				if ($day) {
					$dt->modify("+$count2 ".self::DNM[$day]);
				}
				$this->bugout('YEARCAN '.$dt->format('Y-m-d H:i D'));
				break;
		}
/*
		$days = [];
		if ($daysl) {
			$days = explode(',',$daysl);
			//!! need to handle monday as begin of week
		}

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
*/

		$this->bugout('CANDD '.$dt->format('Y-m-d H:i D'));

		$closetime = $dt->getTimestamp();
		$diffr = $rBeg - $closetime + $evt['alert_lead'];
		$this->bugout('CLODIF',[$rBeg,$closetime,$evt['alert_lead'],$diffr]);
	//	$this->bugout('CLODIF',[$evt,date(DATE_RFC822,$closetime),$diffr]);
		if (($diffr<0) || ($diffr>self::DAYSECS)) {
			return false;
		}

		$evt['t_start'] = $closetime;
		$evt['t_end'] = $evt['t_start'] + $evt['event_length'];
		$evt['start_date'] = $dt->format('Y-m-d H:i');
		return true;
	}

	private function sendAlert ($addr, $subj, $body, $ausrs)
	{
		try {
			$mailer = Factory::getMailer();
			foreach ($this->alertees as $a) {
				if (in_array($a['id'], $ausrs) && $a[$addr]) $mailer->addRecipient($a[$addr], $a['name']);
			}
			$mailer->setSubject($subj);
			$mailer->setBody($body);
			if (!$mailer->Send()) echo 'Mailing failed: '.$mailer->ErrorInfo."\n";
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	protected function sendAlerts ($evt, $atime)
	{
	//	if ($this->bug) return;
		$ausrs = explode(',',$evt['alert_user']);

		$toTime = $evt['rrule'] ? ($evt['t_start'] + $evt['duration']) : $evt['t_end'];
		$evtTime = $this->formattedDateTime($evt['t_start'], $toTime);
		$lb = "\n"; $lbb = "\n\n";
		if ($evt['alert_meth'] & 1) {	//email
			$surl = $this->config->live_site;
			$body = sprintf(Text::_('COM_USERSCHED_ALERT_BLURB'), $this->config->sitename, date('D j F Y g:ia'), $lb, $surl, $lb);
			$body .= $evtTime . $lbb;
			$body .= $evt['text'];
			$this->sendAlert('email', 'Re: Calendar Alert', $body, $ausrs);
		}
		if ($evt['alert_meth'] & 2) {	//SMS
			$splt = explode($lb,$evt['text'],2);
			$body = $evtTime . $lbb;
			$body .= isset($splt[1]) ? $splt[1] : '';
			$this->sendAlert('sms', 'Calendar Alert -- '.$splt[0], $body, $ausrs);
		}
	}

	private function getTable ($table, $values='*', $where='', $key=null, $col=null)
	{	//var_dump('SELECT '.$values.' FROM ' . $table . ($where ? (' WHERE '.$where) : ''));
		$this->db->setQuery('SELECT '.$values.' FROM ' . $table . ($where ? (' WHERE '.$where) : ''));
		return $this->db->loadAssocList($key, $col);
	}

	// mark (for a day) that an alert was triggered
	protected function markAlerted ($id, $atime, $lead)
	{
		if ($this->bug) return;
		if ($lead < self::DAYSECS) $lead = self::DAYSECS;
		$toa = date(DATE_RFC822,$atime);
		$this->db->setQuery('INSERT INTO alerted (eid,atime,lead,toa) VALUES ('.$id.','.$atime.','.$lead.',"'.$toa.'")');
		$this->db->execute();
	}
	
	// see if the event's alert has already been triggered
	protected function wasAlerted ($id, $stray)
	{	//file_put_contents('LOG.txt', 'WA '.print_r([$id, $stray],true)."\n", FILE_APPEND);
		return $this->bug ? false : !empty($stray[$id]);
//		foreach ($stray as $st) {
//			if ($st['eid'] == $id) return true;
//		}
//		return false;
	}

	private function formattedDateTime ($from, $to=0)
	{
		if ($to-$from == self::DAYSECS) {
			return date('D j F Y', $from);
		}
		$fdt = date('D j F Y g:ia', $from);
		if ($to) {
			if ($to-$from > self::DAYSECS) {
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

	private function bugout ($msg, $vars='')
	{
		if (!$this->bug) return;
		echo $msg.' ';
		if ($vars) {
			var_dump($vars);
		} else echo'<br />';
	}

}
