<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.0
*/
namespace RJCreations\Component\Usersched\Site\Model;

defined('_JEXEC') or die;

use RJCreations\Component\Usersched\Site\Model\UserschedModel;

class BackendrModel extends UserschedModel
{
	protected $db;

	public function __construct ($config = [], $factory = null)
	{
		parent::__construct($config, $factory);
		$this->db = $this->getDatabase()->getConnection();
	}

	public function read ($requestParams)
	{
		$queryParams = [];
		$queryText = 'SELECT `event_id` AS `id`, * FROM `events`';
		if (isset($requestParams['from']) && isset($requestParams['to'])) {
			$queryText .= ' WHERE `end_date`>=? AND `start_date` < ?;';
			$queryParams = [$requestParams['from'], $requestParams['to']];
		}
		$events = $this->dbex($queryText, $queryParams, 2);

		foreach ($events as $ix=>$ev) {
			// mask out these fields for conversion purposes
			unset($events[$ix]['rec_type']);
			unset($events[$ix]['event_pid']);
			unset($events[$ix]['event_length']);
			// transform some text
			$events[$ix]['text'] = htmlentities($ev['text']);
		}

		return $events;
	}

	public function create ($event)
	{
		$queryText = 'INSERT INTO `events` (
			`start_date`,
			`end_date`,
			`text`,
			`rrule`,
			`duration`,
			`recurring_event_id`,
			`original_start`,
			`deleted`,
			`category`,
			`user`,
			`alert_lead`,
			`alert_user`,
			`alert_meth`)
			VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)';

		$this->dbex($queryText, $this->qValues($event));
		return $this->db->lastInsertId();
	}

	public function update ($event, $id)
	{
		$queryText = 'UPDATE `events` SET
			`start_date`=?,
			`end_date`=?,
			`text`=?,
			`rrule`=?,
			`duration`=?,
			`recurring_event_id`=?,
			`original_start`=?,
			`deleted`=?,
			`category`=?,
			`user`=?,
			`alert_lead`=?,
			`alert_user`=?,
			`alert_meth`=?
			WHERE `event_id`=?';

		// If a series was modified, all the modified and deleted occurrences of the series should be deleted.
		// Series can be identified by the non-empty value of the rrule property and the empty value of the recurring_event_id one.
		// Modified occurrences of the series are all the records in which recurring_event_id matches the id of the series.
		if ($event['rrule'] && !$event['recurring_event_id']) {
			//all modified occurrences must be deleted when you update recurring series
			//https://docs.dhtmlx.com/scheduler/server_integration.html#savingrecurringevents
			$subQueryText = 'DELETE FROM `events` WHERE `recurring_event_id`=? ;';
			$this->dbex($subQueryText, [$id]);
		}

		$this->dbex($queryText, $this->qValues($event, $id));
	}

	public function delete ($id)
	{
		// some logic specific to recurring events support
		// https://docs.dhtmlx.com/scheduler/server_integration.html#savingrecurringevents
		$subQueryText = 'SELECT * FROM `events` WHERE `event_id`=? LIMIT 1;';
		$event = $this->dbex($subQueryText, [$id], 1);
		// If an event with the non-empty recurring_event_id is deleted, it needs to be updated with deleted=true instead of deleting.
		if ($event['recurring_event_id']) {
			$subQueryText='UPDATE `events` SET `deleted`=true WHERE `event_id`=?;';
			$this->dbex($subQueryText, [$id]);
		} else {
			if ($event['rrule']) { 
				// if a recurring series deleted, delete all modified occurrences of the series
				$subQueryText = 'DELETE FROM `events` WHERE `recurring_event_id`=? ;';
				$this->dbex($subQueryText, [$id]);
			}
			$queryText = 'DELETE FROM `events` WHERE `event_id`=? ;';
			$this->dbex($queryText, [$id]);
		}
	}

	private function qValues ($evt, $xtra=null)
	{
		$vals = [
			$evt['start_date'],
			$evt['end_date'],
			$evt['text'],
			$evt['rrule'] ?? null,
			$evt['duration'] ?? null,
			$evt['recurring_event_id'] ?? null,
			$evt['original_start'] ?? null,
			$evt['deleted'] ?? null,
			$evt['category'] ?? 0,
			$evt['user'] ?? 0,
			$evt['alert_lead'] ?? 0,
			$evt['alert_user'] ?? '',
			$evt['alert_meth'] ?? 0
			];
		if ($xtra) $vals[] = $xtra;
		return $vals;
	}

	private function dbex ($qt, $qp, $ftch=false)
	{
		$q = $this->db->prepare($qt);
		$this->loggit($q->queryString);
		$q->execute($qp);
		if ($ftch) return $ftch>1 ? $q->fetchAll(\PDO::FETCH_ASSOC) : $q->fetch(\PDO::FETCH_ASSOC);
	}

	private function loggit ($msg)
	{
		if (RJC_DEV) file_put_contents('LOG.txt', $msg."\n", FILE_APPEND);
	}

}