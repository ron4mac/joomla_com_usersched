<?php
defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.application.component.modellist');

class UserschedModelSkins extends JModelList
{

	protected $_total = -1;
/*
	public function __construct($config = array())
	{   
		$config['filter_fields'] = array('fullname', 'username', 'userid');
		parent::__construct($config);
	}
*/

	public function deleteSkins ($skins)
	{
		$spath = JPATH_COMPONENT_SITE . '/skins/';
		foreach ($skins as $skin) {
			JFolder::delete($spath.$skin);
		}
	}

	public function addSkin($fref, $skinName='new_skin')
	{
		$zip = new ZipArchive();
		if ($zip->open($fref) !== true) return 1;
		$spath = JPATH_COMPONENT_SITE . '/skins/'.$skinName.'/';
		for($i = 0; $i < $zip->numFiles; $i++) {
			$entry = $zip->getNameIndex($i);
			if ($zip->extractTo($spath, $entry) !== true) return 2;
		}
		@rename($spath.'dhtmlxscheduler.css',$spath.'dhtmlxscheduler_custom.css');
		return 0;
	}

	public function getItems ()
	{	//return array();
		// Get a storage key.
		$store = $this->getStoreId('skin_list');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		$params = JComponentHelper::getParams('com_usersched');

		$skins = array();
		$spath = JPATH_COMPONENT_SITE . '/skins/';
		if (!file_exists($spath)) return $skins;
		$folds = JFolder::folders($spath);
		foreach ($folds as $fold) {
			$skins[] = array(
					'name'=>$fold,
					'isUdef'=>($fold==$params->get('default_skin')),
					'isGdef'=>($fold==$params->get('group_default_skin')),
					'isSdef'=>($fold==$params->get('site_default_skin'))
					);
			$this->_total++;
		}

		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
//		$listOrder = $this->getState('list.ordering');
//		$listDirn = $this->getState('list.direction');
	//	echo $listOrder;echo $listDirn;
/*
		foreach ($skins as $key => $row) {
			$name[$key]  = $row['name'];
			$uname[$key] = $row['uname'];
			$uid[$key] = $row['uid'];
		}
		
		// Sort the data with volume descending, edition ascending
		// Add $data as the last parameter, to sort by the common key
		switch ($listOrder) {
			case 'username':
				array_multisort($uname, SORT_ASC, $name, SORT_ASC, $uid, SORT_ASC, $skins);
				break;
			case 'fullname':
				array_multisort($name, SORT_ASC, $uname, SORT_ASC, $uid, SORT_ASC, $skins);
				break;
			case 'userid':
				array_multisort($uid, SORT_ASC, $uname, SORT_ASC, $name, SORT_ASC, $skins);
				break;
		}
*/

		// Add the items to the internal cache.
		$this->cache[$store] = array_slice($skins,$start,$limit?$limit:null);

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

	protected function populateState ($ordering = null, $direction = null) {
//		parent::populateState('username', 'ASC');
	}

}
