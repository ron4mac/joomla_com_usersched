<?php
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/helpers/usersched.php';
require_once JPATH_COMPONENT.'/helpers/ical.php';
require_once JPATH_COMPONENT.'/views/uschedview.php';

class UserschedViewIcal extends UserschedView
{
	protected $cal_type;

	function display ($tpl = null)
	{
		$app = JFactory::getApplication();
		$this->params = $app->getParams();	//var_dump($this->params);jexit();
		$this->user = JFactory::getUser();
		$calid = UserSchedHelper::uState('calid');
		list($this->cal_type, $this->jID) = explode(':',$calid);

		switch ($this->cal_type) {
			case 0:		// user
				if ($this->jID <= 0) return;
				$caldb = new RJUserData('sched');
				break;
			case 1:		// group
				$caldb = new RJUserData('sched',false,$this->jID,true);
				break;
			case 2:		// site
				$caldb = new RJUserData('sched',false,0,true);
				break;
			default:
				$caldb = null;
		}

		JHtml::stylesheet('components/com_usersched/static/ical.css');
		if ($caldb->dataExists()) {
			$evts = $caldb->getTable('events');
			$this->data = $evts;
			parent::display($tpl);
		} else {
			parent::display('nope');
		}
	}

}
