<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/models/usersched.php';

class UserSchedModelDaterange extends UserSchedModelUserSched
{

	public function __construct ($config = [])
	{
		$dbFile = UschedHelper::userDataPath() . '/sched.sql3';
		// if no calendar yet exists, dont let the model parent create one
		if (!file_exists($dbFile)) {
			$config['dbo'] = 0;	// signal to NOT create db file
		}
		parent::__construct($config);
	}

	public function hasData ()
	{
		$dbFile = UschedHelper::userDataPath() . '/sched.sql3';
		return file_exists($dbFile);
	//	return (bool) $this->getDbo();
	}

	public function evtSearch ($for)
	{
		$db = $this->getDbo();
		$db->setQuery('SELECT strtotime(`start_date`) AS t_start, strtotime(`end_date`) as t_end, * FROM events WHERE text LIKE '.$db->quote("%{$for}%").' ORDER BY t_start');
		$evts = $db->loadAssocList();
		foreach ($evts as $k=>$evt) {
			//var_dump($evt);
			if (strpos($evt['end_date'],'9999-')===0) {
				$evts[$k]['t_end'] = $evt['t_start'] + $evt['event_length'];
			}
		}
		return $evts;
	}

}