<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

require_once JPATH_COMPONENT.'/helpers/ical.php';
require_once JPATH_COMPONENT.'/views/uschedview.php';

class UserschedViewIcal extends UserschedView
{
	protected $cal_type;

	function display ($tpl = null)
	{
		$calid = UserSchedHelper::uState('calid');
		list($this->cal_type, $this->jID) = explode(':',$calid);

		switch ($this->cal_type) {
			case 20:		// user
				if ($this->jID <= 0) return;
				$caldb = new RJUserData('sched');
				break;
			case 21:		// group
				$caldb = new RJUserData('sched',false,$this->jID,true);
				break;
			case 22:		// site
				$caldb = new RJUserData('sched',false,0,true);
				break;
			default:
				$caldb = null;
		}

		if ($caldb && $caldb->dataExists()) {
			$evts = $caldb->getTable('events');
			$this->data = $evts;
			parent::display($tpl);
		} else {
		//	parent::display('nope');
			$this->data = [];
			parent::display($tpl);
		}
	}

}
