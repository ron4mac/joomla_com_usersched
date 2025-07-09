<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.0
*/
namespace RJCreations\Component\Usersched\Site\Model;

defined('_JEXEC') or die;

//require_once 'usersched.php';
use RJCreations\Component\Usersched\Site\Model\UserschedModel;

class BackendModel extends UserschedModel
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
		foreach ($events as $ix=>$ev){
			$events[$ix]['text'] = htmlentities($ev['text']);
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
			VALUES (?,?,?,?,?,?,?,?,?,?,?)';

		$this->dbex($queryText, $this->qValues($event));
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

		if (!empty($event['rec_type']) && $event['rec_type'] != 'none') {
			//all modified occurrences must be deleted when you update recurring series
			//https://docs.dhtmlx.com/scheduler/server_integration.html#savingrecurringevents
			$subQueryText = 'DELETE FROM `events` WHERE `event_pid`=? ;';
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

	private function qValues ($evt, $xtra=null)
	{
		$vals = [
			$evt['start_date'],
			$evt['end_date'],
			$evt['category'] ?? 0,
			$evt['text'],
			$evt['event_pid'] ?? 0,
			$evt['event_length'] ?? 0,
			$evt['rec_type'] ?? '',
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