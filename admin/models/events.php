<?php
defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.application.component.modellist');
jimport('rjuserdata.userdata');

class UserSchedModelEvents extends JModelList
{
	protected $_total = -1;

	public function __construct($config = array())
	{
		$config['filter_fields'] = array('startdate','rectype');
		parent::__construct($config);
	}

	public function deleteEvents ($eids, $uid, $isGrp)
	{
		$events = array();
		$db = new RJUserData('sched', false, $uid, $isGrp);
		$db3 = $db->getDbase();
		$db3->db_connect(false);
		foreach ($eids as $id) {
			$db3->execute("DELETE FROM events WHERE event_id = $id");
		}
	}

	public function getItems ()
	{
		// Get a storage key.
		$store = $this->getStoreId('evtlist');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		$events = array();
		$db = new RJUserData('sched', false, $this->getState('usched_uid'), $this->getState('usched_isgrp'));
		$events = $db->getTable('events','',true);
		$this->_total = count($events);

		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
		$listOrder = $this->getState('list.ordering');
		$listDirn = $this->getState('list.direction');

		foreach ($events as $key => $row) {
			$sdate[$key] = $row['start_date'];
			$rtype[$key]  = $row['rec_type'];
		}

		if ($this->_total)
		// Sort the data with volume descending, edition ascending
		// Add $data as the last parameter, to sort by the common key
		switch ($listOrder) {
			case 'startdate':
				array_multisort($sdate, SORT_ASC, $rtype, SORT_ASC, $events);
				break;
			case 'rectype':
				array_multisort($rtype, SORT_ASC, $sdate, SORT_ASC, $events);
				break;
		}

		// Add the items to the internal cache.
		$this->cache[$store] = array_slice($events,$start,$limit?$limit:null);

		return $this->cache[$store];
	}

	public function getTotal ()
	{
		// Get a storage key.
		$store = $this->getStoreId('evtTotal');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		// Load the total if none
		if ($this->_total < 0) $this->getItems();

		// Add the total to the internal cache.
		$this->cache[$store] = $this->_total;

		return $this->cache[$store];
	}

	protected function populateState ($ordering = null, $direction = null) {
		parent::populateState('startdate', 'ASC');

		$input = JFactory::getApplication()->input;
		$uid = $input->getInt('uid');
		if ($uid) $this->setState('usched_uid',$uid);
		$isGrp = $input->getBool('isgrp');
		if ($isGrp) $this->setState('usched_isgrp',$isGrp);
	}

}
