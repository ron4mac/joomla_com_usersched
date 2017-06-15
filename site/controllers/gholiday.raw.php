<?php
defined('_JEXEC') or die;

class UserSchedControllerGHoliday extends JControllerLegacy
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
		$rg = $this->input->get('rg', 'usa__en');	// region
		echo $this->hCache($yr, $rg);
	}

	// a cache for holidays so we don't hit up Google all that often (once a day)
	private function hCache ($yr, $rg)
	{
		if (is_writable(JPATH_CACHE)) {
			$cdir = JPATH_CACHE.'/'.$this->input->get('option');
			// check cache dir or create cache dir
			if (!JFolder::exists($cdir)) {
				JFolder::create($cdir); 
			}

			$cache_file = $cdir.'/'.$yr.'-'.$rg.'.json';

			// check cache file, if not then write cache file
			if (!JFile::exists($cache_file) || filesize($cache_file) == 0 || ((filemtime($cache_file) + 86400 ) < time())) {
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
		$url = 'https://www.googleapis.com/calendar/v3/calendars/'.$rg.'@holiday.calendar.google.com/events?key=AIzaSyAxlVigwBVLSu-ryKOr1c4mXextZ6nPkyc';
		$url .= '&timeMin='.$yr.'-01-01T00%3A00%3A00%2B00%3A00&timeMax='.($yr+1).'-01-01T00%3A00%3A00%2B00%3A00&singelEvents=true';
		$downloader = new FOFDownload();
		$downloader->setAdapterOptions(array(CURLOPT_SSL_VERIFYPEER => 0,CURLOPT_SSL_VERIFYHOST => 0));
		$data = $downloader->getFromURL($url);
		return $data;
	}

}
