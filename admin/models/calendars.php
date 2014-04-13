<?php
defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.application.component.modellist');

class UserschedModelCalendars extends JModelList
{

	protected $_total = -1;

	public function getItems ()
	{	//return array();
		// Get a storage key.
		$store = $this->getStoreId('list');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		jimport('rjuserdata.userdata');
		$scheds = array();
		$folds = RJUserDbs::getDbPaths('g','sched');
		foreach ($folds as $fold) {
			$gid = (int)substr($fold,1);
			$group = UserSchedHelper::getGroupTitle($gid);
			if (!$group) $group = "&lt; group {$gid} &gt;";
			$members = JAccess::getUsersByGroup($gid);
			$scheds[] = array('name'=>$group,'members'=>count($members),'gid'=>$gid);
		}
		$this->_total = count($scheds);

		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
		// Add the items to the internal cache.
		$this->cache[$store] = array_slice($scheds,$start,$limit?$limit:null);

		return $this->cache[$store];
	}

	public function getTotal ()
	{
		// Get a storage key.
		$store = $this->getStoreId('getTotal');

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

}
