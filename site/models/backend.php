<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

require_once 'usersched.php';

class UserSchedModelBackend extends UserSchedModelUserSched
{
	protected $db;

	public function __construct ($config = [])
	{
		parent::__construct($config);
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
		foreach ($events as $index=>$event){
			$events[$index]['text'] = htmlentities($event['text']);
		}
		return $events;
	}

	public function create ($event)
	{
		$queryText = 'INSERT INTO `events` (
			`start_date`,
			`end_date`,
			`category`,
			`text`,
			`event_pid`,
			`event_length`,
			`rec_type`,
			`user`,
			`alert_lead`,
			`alert_user`,
			`alert_meth`)
			VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)';
		$queryParams = [
			$event['start_date'],
			$event['end_date'],
			!empty($event['category']) ? $event['category'] : 0,
			$event['text'],
			!empty($event['event_pid']) ? $event['event_pid'] : 0,
			!empty($event['event_length']) ? $event['event_length'] : 0,
			!empty($event['rec_type']) ? $event['rec_type'] : '',
			!empty($event['user']) ? $event['user'] : 0,
			!empty($event['alert_lead']) ? $event['alert_lead'] : 0,
			!empty($event['alert_user']) ? $event['alert_user'] : '',
			!empty($event['alert_meth']) ? $event['alert_meth'] : 0
			];

		$this->dbex($queryText, $queryParams);
		return $this->db->lastInsertId();
	}

	public function update ($event, $id)
	{
		$queryText = 'UPDATE `events` SET
			`start_date`=?,
			`end_date`=?,
			`category`=?,
			`text`=?,
			`event_pid`=?,
			`event_length`=?,
			`rec_type`=?,
			`user`=?,
			`alert_lead`=?,
			`alert_user`=?,
			`alert_meth`=?
			WHERE `event_id`=?';

		$queryParams = [
			$event['start_date'],
			$event['end_date'],
			!empty($event['category']) ? $event['category'] : 0,
			$event['text'],
			!empty($event['event_pid']) ? $event['event_pid'] : 0,
			!empty($event['event_length']) ? $event['event_length'] : 0,
			!empty($event['rec_type']) ? $event['rec_type'] : '',
			!empty($event['user']) ? $event['user'] : 0,
			!empty($event['alert_lead']) ? $event['alert_lead'] : 0,
			!empty($event['alert_user']) ? $event['alert_user'] : '',
			!empty($event['alert_meth']) ? $event['alert_meth'] : 0,
			$id
		];
		if (!empty($event['rec_type']) && $event['rec_type'] != 'none') {
			//all modified occurrences must be deleted when you update recurring series
			//https://docs.dhtmlx.com/scheduler/server_integration.html#savingrecurringevents
			$subQueryText = 'DELETE FROM `events` WHERE `event_pid`=? ;';
			$this->dbex($subQueryText, [$id]);
		}

		$this->dbex($queryText, $queryParams);
	}

	public function delete ($id)
	{
		// some logic specific to recurring events support
		// https://docs.dhtmlx.com/scheduler/server_integration.html#savingrecurringevents
		$subQueryText = 'SELECT * FROM `events` WHERE `event_id`=? LIMIT 1;';
		$event = $this->dbex($subQueryText, [$id], 1);

		if ($event['event_pid']) {
			// deleting a modified occurrence from a recurring series
			// If an event with the event_pid value was deleted - it needs updating
			// with rec_type==none instead of deleting.
			$subQueryText='UPDATE `events` SET `rec_type`=\'none\' WHERE `event_id`=?;';
			$this->dbex($subQueryText, [$id]);
		} else {
			if (!empty($event['rec_type']) && $event['rec_type'] != 'none') {//!
				// if a recurring series deleted, delete all modified occurrences of the series
				$subQueryText = 'DELETE FROM `events` WHERE `event_pid`=? ;';
				$this->dbex($subQueryText, [$id]);
			}
			/*	end of recurring events data processing*/

			$queryText = 'DELETE FROM `events` WHERE `event_id`=? ;';
			$this->dbex($queryText, [$id]);
		}
	}

	private function dbex ($qt, $qp, $ftch=false)
	{
		$q = $this->db->prepare($qt);
		$this->loggit($q->queryString);
		$q->execute($qp);
		if ($ftch) return $ftch>1 ? $q->fetchAll(PDO::FETCH_ASSOC) : $q->fetch();
	}

	private function loggit ($msg)
	{
		if (RJC_DEV) file_put_contents('LOG.txt', $msg."\n", FILE_APPEND);
	}

}