<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.0
*/
namespace RJCreations\Component\Usersched\Administrator\Model;

defined('_JEXEC') or die;

use RJCreations\Library\RJUserCom;

\jimport('joomla.filesystem.folder');
\jimport('joomla.application.component.modellist');

class UserschedModel extends \Joomla\CMS\MVC\Model\ListModel
{

	protected $_total = -1;

	public function __construct($config = [], $factory = null)
	{   
		$config['filter_fields'] = ['fullname', 'username', 'userid'];
		parent::__construct($config, $factory);
	}

	public function getItems ()
	{	//return [];
		// Get a storage key.
		$store = $this->getStoreId('list');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

//		jimport('rjuserdata.userdata');
		$scheds = [];
//		$folds = UschedHelper::getDbPaths('u','sched');
		$folds =  RJUserCom::getDbPaths('u','sched');
		foreach ($folds as $fold=>$info) {
			$userid = (int)substr($fold,1);
			$user = JUser::getInstance($userid);
			$scheds[] = ['name'=>$user->name,'uname'=>$user->username,'uid'=>$userid];
		}
		$this->_total = count($scheds);

		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
		$listOrder = $this->getState('list.ordering');
		$listDirn = $this->getState('list.direction');
	//	echo $listOrder;echo $listDirn;

		foreach ($scheds as $key => $row) {
			$name[$key]  = $row['name'];
			$uname[$key] = $row['uname'];
			$uid[$key] = $row['uid'];
		}
		
		if ($this->_total)
		// Sort the data with volume descending, edition ascending
		// Add $data as the last parameter, to sort by the common key
		switch ($listOrder) {
			case 'username':
				array_multisort($uname, SORT_ASC, $name, SORT_ASC, $uid, SORT_ASC, $scheds);
				break;
			case 'fullname':
				array_multisort($name, SORT_ASC, $uname, SORT_ASC, $uid, SORT_ASC, $scheds);
				break;
			case 'userid':
				array_multisort($uid, SORT_ASC, $uname, SORT_ASC, $name, SORT_ASC, $scheds);
				break;
		}


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

	protected function populateState ($ordering = null, $direction = null) {
		parent::populateState('username', 'ASC');
	}

}
