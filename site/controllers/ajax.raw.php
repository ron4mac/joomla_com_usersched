<?php
defined('_JEXEC') or die;

class UserSchedControllerAjax extends JControllerLegacy
{
	public function __construct ($default=array())
	{
		parent::__construct($default);
		if (!isset($this->input)) $this->input = JFactory::getApplication()->input;		//J2.x
	}

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
		$db = JFactory::getDbo();
		// set groups to just get registered users
		$groups = array(2);
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
		$evts = array();
		foreach ($rows as $u) {
			$dob = unQuote($u->dob);
			$bday = strtotime($dob);
			$nxd = $bday + 86400;
			if ($bday) {
				$evts[] = array('text'=>$u->name,'start_date'=>$yr.date('-m-d',$bday),'end_date'=>$yr.date('-m-d',$nxd),'xevt'=>'isBrthday');
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
		jimport('joomla.filesystem.folder');
		if (is_writable(JPATH_CACHE)) {
			$cdir = JPATH_CACHE.'/'.$this->input->get('option');
			// check cache dir or create cache dir
			if (!JFolder::exists($cdir)) {
				JFolder::create($cdir); 
			}

			$cache_file = $cdir.'/'.$yr.'-'.$rg.'.json';

			// check cache file, if not then write cache file
			if (!JFile::exists($cache_file) || filesize($cache_file) == 0 || ((filemtime($cache_file) + 604800 ) < time())) {
				$data = $this->getGholidays($yr, $rg);
				JFile::write($cache_file, $data);
			} else {
				// read cache file
				$data = JFile::read($cache_file);
			}
			return $data;
		} else {
			return $this->getGholidays($yr, $rg);
		}
	}

	private function getGholidays ($yr, $rg)
	{
		$key = JComponentHelper::getParams('com_usersched')->get('googapi_key','');
		$url = 'https://www.googleapis.com/calendar/v3/calendars/'.$rg.'@holiday.calendar.google.com/events?key='.$key;
		$url .= '&timeMin='.$yr.'-01-01T00%3A00%3A00%2B00%3A00&timeMax='.($yr+1).'-01-01T00%3A00%3A00%2B00%3A00&singelEvents=true';
		$downloader = new FOFDownload();
		$downloader->setAdapterOptions(array(CURLOPT_SSL_VERIFYPEER => 0,CURLOPT_SSL_VERIFYHOST => 0));
		$data = $downloader->getFromURL($url);
		return $data;
	}

	
}
