<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use RJCreations\Component\Usersched\Site\View\UschedView;

require_once JPATH_SITE.'/components/com_usersched/helpers/ical.php';

class UserschedViewIcal extends UschedView
{
	public $jID;
	public $data;
	protected $cal_type;

	public function display ($tpl = null): void
	{
		$calid = UserSchedHelper::uState('calid');
		[$this->cal_type, $this->jID] = explode(':',$calid);

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
