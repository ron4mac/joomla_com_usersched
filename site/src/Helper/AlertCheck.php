<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
namespace RJCreations\Component\Usersched\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;

use RJCreations\Component\Usersched\Site\RRule;

require_once JPATH_SITE.'/components/com_usersched/classes/rdatetime.php';

class AlertCheck {

//	const DNM = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
	const RANGESECS = 1200;
	const DAYSECS = 86400;
//	const WEEKSECS = 604800;

	protected $db;
	protected $alertees;

	public function __construct ($dbp, protected $config, protected $bug=false)
	{
		$this->bugout('@@@@@@@ DBASE '.basename(dirname($dbp,2)).'/'.basename(dirname($dbp)));
		$opt = ['driver'=>'sqlite','host'=>'','user'=>'','password'=>'','database'=>$dbp,'prefix'=>''];
		$db = DatabaseDriver::getInstance($opt);
		$db->connect();
		$db->getConnection()->sqliteCreateFunction('strtotime', 'strtotime', 1);
		$this->db = $db;
	}

	public function processAlerts ($time): void
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
			if (!empty($evt['rrule']) && !$this->recursNow($evt, $atime-self::RANGESECS, $atime+$evt['alert_lead']+self::RANGESECS)) continue;
			// skip if XXXXX or event start was more than a day ago
//			if (/*($atime + $evt['alert_lead']-$evt['t_start'])<0 ||*/ ($atime-$evt['t_start'])>86399) continue;

			$this->bugout('ALERT '.$evt['start_date']);
//			$this->sendAlerts($evt, $atime);
		//	$this->markAlerted($evt['event_id'], $evt['t_start']-$evt['alert_lead'], $evt['alert_lead']+$evt['t_end']-$evt['t_start'] /*$atime*/);
//			$this->markAlerted($evt['event_id'], $evt['t_start']/* -$evt['alert_lead'] */, max($evt['alert_lead'],self::DAYSECS));
			$this->markAlerted($evt['event_id'], $atime, max($evt['alert_lead'],self::DAYSECS));
			$this->sendAlerts($evt, $atime);
		}
		$this->bugout('<br><br>');
	}

	private function recursNow (array &$evt, int|float $rBeg, float|int $rEnd): bool
	{
		$this->bugout('Recursing: ',[$rBeg, date(DATE_RFC822,$rBeg), $rEnd, date(DATE_RFC822,$rEnd)/*, $evt*/]);
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

	private function sendAlert (string $addr, string $subj, string $body, array $ausrs): void
	{
		try {
			$mailer = Factory::getMailer();
			foreach ($this->alertees as $a) {
				if (in_array($a['id'], $ausrs) && $a[$addr]) $mailer->addRecipient($a[$addr], $a['name']);
			}
			$mailer->setSubject($subj);
			$mailer->setBody($body);
			if (!$mailer->Send()) echo 'Mailing failed: '.$mailer->ErrorInfo."\n";
		} catch (\Exception $exception) {
			echo $exception->getMessage();
			var_dump($mailer->getAllRecipientAddresses());
		}
	}

	protected function sendAlerts (array $evt, $atime)
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
			$this->sendAlert('email', 'Calendar Alert', $body, $ausrs);
		}
		if ($evt['alert_meth'] & 2) {	//SMS
			$splt = explode($lb,$evt['text'],2);
			$body = $evtTime . $lbb;
			$body .= $splt[1] ?? '';
			$this->sendAlert('sms', 'Calendar Alert -- '.$splt[0], $body, $ausrs);
		}
	}

	private function getTable (string $table, string $values='*', string $where='', $key=null, $col=null)
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
	protected function wasAlerted ($id, array $stray): bool
	{	//file_put_contents('LOG.txt', 'WA '.print_r([$id, $stray],true)."\n", FILE_APPEND);
		return $this->bug ? false : !empty($stray[$id]);
//		foreach ($stray as $st) {
//			if ($st['eid'] == $id) return true;
//		}
//		return false;
	}

	private function formattedDateTime ($from, $to=0): string
	{
		if ($to-$from == self::DAYSECS) {
			return date('D j F Y', $from);
		}
		$fdt = date('D j F Y g:ia', $from);
		if ($to) {
			if ($to-$from > self::DAYSECS) {
				if ((date('Hi',$from).date('Hi',$to)) === '00000000') {
					return date('D j F Y', $from).' - '.date('D j F Y', $from);
				}
				$fdt .= ' - '.date('D j F Y g:ia', $to);
			} else {
				$fdt .= ' to '.date('g:ia', $to);
			}
		}
		return $fdt;
	}

	private function bugout (string $msg, $vars=''): void
	{
		if (!$this->bug) return;
		echo $msg.' ';
		if ($vars) {
			var_dump($vars);
		} else echo'<br />';
	}

}
