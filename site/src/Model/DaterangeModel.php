<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2025 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.1
*/
namespace RJCreations\Component\Usersched\Site\Model;

defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/src/Model/UserschedModel.php';

class DaterangeModel extends UserschedModel
{

	public function __construct ($config = [], $factory = null)
	{
		$dbFile = \UschedHelper::userDataPath() . '/usersched.db3';
		// if no calendar yet exists, dont let the model parent create one
		if (!file_exists($dbFile)) {
			$config['dbo'] = 0;	// signal to NOT create db file
		}
		parent::__construct($config, $factory);
	}

	public function hasData ()
	{
		$dbFile = \UschedHelper::userDataPath() . '/usersched.db3';
		return file_exists($dbFile);
	//	return (bool) $this->getDbo();
	}

	public function evtSearch ($for)
	{
		$db = $this->getDbo();
		$db->setQuery('SELECT strtotime(`start_date`) AS t_start, strtotime(`end_date`) as t_end, * FROM events WHERE text LIKE '.$db->quote("%{$for}%").' ORDER BY t_start');
		$evts = $db->loadAssocList();
		foreach ($evts as $k=>$evt) {
			//var_dump($evt);jexit();
			if (strpos($evt['end_date'],'9999-')===0) {
				$evts[$k]['t_end'] = $evt['t_start'] + $evt['duration'];
			}
		}
		return $evts;
	}

}