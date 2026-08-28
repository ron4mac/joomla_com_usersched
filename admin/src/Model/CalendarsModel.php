<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
namespace RJCreations\Component\Usersched\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use RJCreations\Library\RJUserCom;

\JLoader::register('UserSchedHelper', JPATH_ADMINISTRATOR.'/components/com_usersched/helpers/usersched.php');

class CalendarsModel extends \Joomla\CMS\MVC\Model\ListModel
{

	protected $_total = -1;

	public function getItems ()
	{
		// Get a storage key.
		$store = $this->getStoreId('list');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		$scheds = [];
		$folds =  RJUserCom::getDbPaths('g','usersched');	//RJUserDbs::getDbPaths('g','sched');
		foreach ($folds as $fold=>$mgis) foreach ($mgis as $mgi) {
			$gid = (int)substr($fold,1);
			$group = \UserSchedHelper::getGroupTitle($gid);
			if (!$group) $group = "&lt; group {$gid} &gt;";
			$members = Access::getUsersByGroup($gid);
			$scheds[] = ['name'=>$group,'members'=>count($members),'guid'=>'_'.$gid,'mnun'=>$mgi['mnun']];
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
