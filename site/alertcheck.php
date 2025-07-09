<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.0
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;

require_once JPATH_COMPONENT.'/classes/rdatetime.php';

define('HOURSECS', 3600);
define('DAYSECS', 86400);
define('WEEKSECS', 604800);

class USchedAcheck {

	const DNM = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

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
		$db = JDatabaseDriver::getInstance($opt);
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
		$this->db->setQuery('DELETE FROM alerted WHERE ('.$time.' - atime + 5)> lead'/*.DAYSECS*/)->execute();

		$alerted = $this->getTable('alerted','*','','eid','eid');

		$atime = $time;

		// get event range
		$fields = '*, strtotime(start_date) AS t_start, strtotime(end_date) as t_end';
		//$where = 'alert_user != \'\' AND ((substr(end_date,1,5) == \'9999-\')OR(t_end > '.$atime.')) AND ((t_start - alert_lead) <= ('.$atime.' + 5))';
		$where = 'alert_user != \'\' AND ((substr(end_date,1,5) == \'9999-\')OR(t_end > '.$atime.')) AND ((t_start - alert_lead) <= '.$atime.')';
		$evts = $this->getTable('events', $fields, $where);
//		var_dump($atime,$evts);
												/// @@@@@@ MIGHT WANT TO GET RECURRING EVENTS SEPARATELY
		foreach ($evts as $evt) {
			$this->bugout('<br>Event: '.$evt['text']);
			// skip if was already alerted within timeframe
			if ($this->wasAlerted($evt['event_id'], $alerted)) continue;
			// skip if recurring and and no hit
			if ($evt['rec_type'] && !$this->recursNow($evt, $atime)) continue;
			// skip if XXXXX or event start was more than a day ago
//			if (/*($atime + $evt['alert_lead']-$evt['t_start'])<0 ||*/ ($atime-$evt['t_start'])>86399) continue;

			$this->bugout('ALERT '.$evt['start_date']);
			$this->sendAlerts($evt, $atime);
		//	$this->markAlerted($evt['event_id'], $evt['t_start']-$evt['alert_lead'], $evt['alert_lead']+$evt['t_end']-$evt['t_start'] /*$atime*/);
			$this->markAlerted($evt['event_id'], $evt['t_start']-$evt['alert_lead'], max($evt['alert_lead'],DAYSECS));
		}
		$this->bugout('<br><br>');
	}

	private function recursNow (&$evt, $atime)
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
				$divsr = $count * DAYSECS;
				$pdelta = (int)floor(($atime + $evt['alert_lead'] - $evt['t_start']) / $divsr);
				$dt->add(new DateInterval('P'.($pdelta*$count).'D'));
				$this->bugout('DAYCAN '.$dt->format('Y-m-d H:i D'));
				break;
			case 'week':
				$divsr = $count * WEEKSECS;
				$edays = explode(',',$daysl);
				$fday = $edays[0];
				$frstt = $evt['t_start'];
				$iters = (int)(($atime - $frstt) / $divsr);							$this->bugout('TSTART',[$divsr,$iters,date(DATE_RFC822,$frstt)]);
				$frstt += $iters * $divsr;							$this->bugout('FITTER '.date(DATE_RFC822,$frstt));
				$tgt = 0;
				while (!$tgt) {
					$ltim = 0;
					foreach ($edays as $eday) {
						$etim = $frstt + ($eday-$fday) * DAYSECS;				$this->bugout('TITTER '.date(DATE_RFC822,$etim));
						if ($etim > $atime) {
							$tgt = $ltim ?: $etim;
							break 2;
						}
						$ltim = $etim;
					}
					$frstt += $divsr;
					if (($frstt-$evt['alert_lead'])>$atime) return false;
				}
				$dt = new R_DateTime('', null, $tgt);
				break;
			case 'month':
				$cdt = new R_DateTime(date('Y-m-d H:i',$atime /*+ $evt['alert_lead']*/));
				$dim = ($cdt->getFullYear() - $dt->getFullYear()) * 12;
				$dim += $cdt->getMonth() - $dt->getMonth();
				$nop = (int) ($dim / $count);
				$dt->add(new DateInterval('P'.($nop*$count).'M'));
				break;
			case 'year':
				$cdt = new R_DateTime(date('Y-m-d H:i',$atime /*+ $evt['alert_lead']*/));
				$diy = $cdt->getFullYear() - $dt->getFullYear();
				$nop = (int) ($diy / $count);
				$dt->add(new DateInterval('P'.($nop*$count).'Y'));
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
		$closetime = $dt->getTimestamp();
		$diffr = $atime - $closetime + $evt['alert_lead'];
		$this->bugout('CLODIF',[$atime,$closetime,$evt['alert_lead'],$diffr]);
	//	$this->bugout('CLODIF',[$evt,date(DATE_RFC822,$closetime),$diffr]);
		if (($diffr<0) || ($diffr>DAYSECS)) {
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
				if (in_array($a['id'], $ausrs) && $a[$addr]) $mailer->addRecipient($a[$addr]);
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
		if ($this->bug) return;
		$ausrs = explode(',',$evt['alert_user']);

		$toTime = $evt['rec_type'] ? ($evt['t_start'] + $evt['event_length']) : $evt['t_end'];
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
		if (true || $this->bug) return;
		if ($lead < DAYSECS) $lead = DAYSECS;
		$this->db->setQuery('INSERT INTO alerted (eid,atime,lead) VALUES ('.$id.','.$atime.','.$lead.')');
		$this->db->execute();
	}
	
	// see if the event's alert has already been triggered
	protected function wasAlerted ($id, $stray)
	{
		return !empty($stray[$id]);
//		foreach ($stray as $st) {
//			if ($st['eid'] == $id) return true;
//		}
//		return false;
	}

	private function formattedDateTime ($from, $to=0)
	{
		if ($to-$from == DAYSECS) {
			return date('D j F Y', $from);
		}
		$fdt = date('D j F Y g:ia', $from);
		if ($to) {
			if ($to-$from > DAYSECS) {
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
