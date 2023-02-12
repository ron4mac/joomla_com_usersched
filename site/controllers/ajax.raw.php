<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Component\ComponentHelper;

JLoader::register('USchedAcheck', JPATH_COMPONENT.'/alertcheck.php');

class UserSchedControllerAjax extends JControllerLegacy
{
	//	ajax call from client scheduler for user birthdays
	public function birthdays ()
	{
		// clean up the dob as returned from the database
		function unQuote ($val, $comma=false)
		{
			$nq = str_replace('"','',$val);
			if ($comma && $nq) $nq .= ', ';
			return $nq;
		}

		$yr = $this->input->get('y');
		// get the database
		$db = Factory::getDbo();
		// set groups to just get registered users
		$groups = [2];
		// set a where clause for appropriate filtering
		$userGroupWhereStatement = 'u.block=0 AND u.id IN (SELECT ugm.user_id FROM #__user_usergroup_map ugm WHERE ';
		$hasGroups = false;
		if ($groups) {
			foreach ($groups as $value) {
				if ($value != '') {
					if ($hasGroups == false) {
						$userGroupWhereStatement .= 'ugm.group_id=' . $value;
						$hasGroups = true;
					} else {
						$userGroupWhereStatement .= ' OR ugm.group_id=' . $value;
					}
				}
			}
		}
		$userGroupWhereStatement .= ")";
		// create the query
		$query = 'SELECT u.name, u.block, (SELECT w.profile_value FROM #__user_profiles w WHERE w.user_id=u.id AND w.profile_key=\'profile.dob\') AS dob FROM #__users u';
		// add any filtering
		if ($hasGroups) {
			$query .= ' WHERE ' . $userGroupWhereStatement;
		}
		// fire the query to get user birthdays
		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		// turn them into calendar events
		$evts = [];
		foreach ($rows as $u) {
			if (empty($u->dob)) continue;
			$dob = unQuote($u->dob);
			$bday = strtotime($dob);
			$nxd = $bday + 86400;
			if ($bday) {
				$evts[] = ['text'=>$u->name,'start_date'=>$yr.date('-m-d',$bday),'end_date'=>$yr.date('-m-d',$nxd),'xevt'=>'isBrthday','readonly'=>true];
			}
		};
		// send the events to the client
		echo json_encode($evts);
	}

	//	ajax call from client scheduler for holidays (from Google)
	public function holidays ()
	{
		$yr = $this->input->get('yr', 2017);	// year
		$rg = $this->input->get('rg', 'usa__en');	// region
		echo $this->hCache($yr, $rg);
	}

	private function hCache ($yr, $rg)
	{
		if (is_writable(JPATH_CACHE)) {
			$cdir = JPATH_CACHE.'/'.$this->input->get('option');
			// check cache dir or create cache dir
			if (!Folder::exists($cdir)) {
				Folder::create($cdir); 
			}

			$cache_file = $cdir.'/'.$yr.'-'.$rg.'.json';

			// check cache file, if not then write cache file
			if (!file_exists($cache_file) || filesize($cache_file) == 0 || ((filemtime($cache_file) + 604800 ) < time())) {	// older than 1 week
				$data = $this->getGholidays($yr, $rg);
				file_put_contents($cache_file, $data);
			} else {
				// read cache file
				$data = file_get_contents($cache_file);
			}
			return $data;
		} else {
			return $this->getGholidays($yr, $rg);
		}
	}

	private function getGholidays ($yr, $rg)
	{
		$key = ComponentHelper::getParams('com_usersched')->get('googapi_key','');
		$url = 'https://www.googleapis.com/calendar/v3/calendars/'.$rg.'@holiday.calendar.google.com/events?key='.$key;
		$url .= '&timeMin='.$yr.'-01-01T00%3A00%3A00%2B00%3A00&timeMax='.($yr+1).'-01-01T00%3A00%3A00%2B00%3A00&singelEvents=true';
		$connector = HttpFactory::getHttp();
		$data = $connector->get($url);
		return $data->body;
	}

	// cron job access here to send alerts
	public function cron ()
	{
		$dbug = strpos($this->input->server->get('HTTP_USER_AGENT'), 'Wget') === false;

		$dbs = RJUserCom::getDbPaths(null, 'sched', true);

		// get the storage location path
		$results = Factory::getApplication()->triggerEvent('onRjuserDatapath');
		$dsp = isset($results[0]) ? trim($results[0]) : false;
		$stor = ($dsp ?: 'userstor');

		$config = new JConfig();
		$xtime = time();

		if ($dbug) echo'<pre>';
		foreach ($dbs as $dbp=>$inst) {
			foreach ($inst as $info) {
				$acheck = new USchedAcheck($info['path'], $config, $dbug);
				$acheck->processAlerts($xtime);
				unset($acheck);
			}
		}
		if ($dbug) echo'</pre>';
/*
		return;

		if ($dirh = opendir(JPATH_SITE . '/'.$stor)) {
			while (false !== ($entry = readdir($dirh))) {
				if ($entry != '.' && $entry != '..' && is_dir(JPATH_SITE.'/'.$stor.'/'.$entry)) {
					if ($entry[0] == '@' || $entry[0] == '_') {
						$grp = $entry[0] == '_';
						foreach (glob(JPATH_SITE.'/'.$stor.'/'.$entry.'/com_usersched_[0-9]*') as $mid) {
							$dbp = $mid.'/sched.sql3';
							if (file_exists($dbp)) {
								$acheck = new USchedAcheck($dbp, $config);
								$acheck->processAlerts($xtime);
								unset($acheck);
							}
						}
					}
				}
			}
			closedir($dirh);
		}
*/
	}
	
	
}
