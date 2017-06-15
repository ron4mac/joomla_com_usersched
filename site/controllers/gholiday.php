<?php
defined('_JEXEC') or die;

class GHolidayController extends JControllerLegacy
{
	function __construct ($default=array())
	{
		parent::__construct($default);
		if (!isset($this->input)) $this->input = JFactory::getApplication()->input;		//J2.x
	}


	/*	ajax call from client scheduler for holidays (from Google) */
	public function holidays ()
	{
		$yr = $this->input->get('yr', 2017);	// year
		$rg = $this->input->get('rg', 'usa__en%40holiday');	// region
		echo $this->hCache($yr, $rg);
	}


	private function hCache ($yr, $rg)
	{
		if (is_writable(JPATH_CACHE)) {
			// check cache dir or create cache dir
			if (!JFolder::exists(JPATH_CACHE.'/com_usersched')) {
				JFolder::create(JPATH_CACHE.'/com_usersched/'); 
			}

			$cache_file = JPATH_CACHE.'/com_usersched/'.$yr.'-'.$rg.'.json';

			// check cache file, if not then write cache file
			if (!JFile::exists($cache_file) || filesize($cache_file) == 0 || ((filemtime($cache_file) + 86400 ) < time())) {
				$data = $this->getGholidays($yr, $rg);
				JFile::write($cache_file, $data);
			}

			// read cache file
			$data = JFile::read($cache_file);
			return $data;
		} else {
			return $this->getGholidays($yr, $rg);
		}
	}


	private function getGholidays ($yr, $rg)
	{
		$url = 'https://www.googleapis.com/calendar/v3/calendars/'.$rg.'.calendar.google.com/events?key=AIzaSyAxlVigwBVLSu-ryKOr1c4mXextZ6nPkyc';
		$url .= '&timeMin='.$yr.'-01-01T00%3A00%3A00%2B00%3A00&timeMax='.($yr+1).'-01-01T00%3A00%3A00%2B00%3A00&singelEvents=true';
		$downloader = new FOFDownload();
		$data = $downloader->getFromURL($url);
		return $data;
	}

	private function curly ()
	{
		
	}

}
