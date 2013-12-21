<?php
defined('_JEXEC') or die;

class UserSchedHelper
{

	public static function addSubmenu ($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_USERSCHED_SUBMENU_USERSCHED'),
			'index.php?option=com_usersched',
			$vName == 'usersched'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_USERSCHED_SUBMENU_CALENDARS'),
			'index.php?option=com_usersched&view=calendars',
			$vName == 'calendars'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_USERSCHED_SUBMENU_CONFIGURATIONS'),
			'index.php?option=com_usersched&view=configs',
			$vName == 'configs'
		);

		if ($vName=='configs') {
			JToolBarHelper::title(
				JText::sprintf('COM_CATEGORIES_CATEGORIES_TITLE', JText::_('com_contact')),
				'contact-categories');
		}
	}

	public static function getActions ()
	{
		$user = JFactory::getUser();
		$result = new JObject;
		$assetName = 'com_usersched';

		$actions = JAccess::getActions($assetName);

		foreach ($actions as $action) {
			$result->set($action->name,	$user->authorise($action->name, $assetName));
		}

		return $result;
	}

/*
	public function getPagination()
	{
		// Get a storage key.
//		$store = $this->getStoreId('getPagination');
		$store = UserSchedHelper::getStoreId('getPagination');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Create the pagination object.
		jimport('joomla.html.pagination');
		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page = new JPagination($this->getTotal(), $this->getStart(), $limit);

		// Add the object to the internal cache.
		$this->cache[$store] = $page;

		return $this->cache[$store];
	}

	protected function getStoreId($id = '')
	{
		// Add the list state to the store id.
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');

		return md5($this->context . ':' . $id);
	}
*/
//		$params = JComponentHelper::getParams('com_search');

	public static function getUserScheds ($u=true,$g=false,$s=false)
	{
		$scheds = array();
		$spath = JPATH_SITE . '/userstor/';
		$folds = JFolder::folders($spath);
		foreach ($folds as $fold) {
			if (file_exists($spath.$fold.'/com_usersched/sched.sql3')) {
				switch ($fold[0]) {
					case '@':
						if (!$u) break;
						$uid = (int)substr($fold,1);
						$user =& JFactory::getUser($uid);
						$scheds[] = array('name'=>$user->name,'uname'=>$user->username,'uid'=>$uid);
						break;
					case '_':
						if (!$g) break;
						$gid = (int)substr($fold,1);
						$group = UserSchedHelper::getGroupTitle($gid);
						$scheds[] = array('name'=>'GROUP','uname'=>$group,'uid'=>$gid);
						break;
				}
			}
		}
		return $scheds;
	}

	public static function getGroupTitle ($gid)
	{
		// Get the title of the group.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('title'));
		$query->from($db->quoteName('#__usergroups'));
		$query->where($db->quoteName('id') . ' = ' . (int) $gid);
		$db->setQuery($query);
		$title = $db->loadResult();
		return $title;
	}

}