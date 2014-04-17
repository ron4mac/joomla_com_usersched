<?php
defined('_JEXEC') or die;
/*
 * This is a base view class to (hopefully) avoid duplication of code needed by all views
 */

require_once JPATH_COMPONENT.'/helpers/usersched.php';
jimport('joomla.application.component.helper');
jimport('rjuserdata.userdata');

class UserschedView extends JViewLegacy
{

	protected $cOpts;						// component options
	protected $show_versions, $version;		// settings for version display
	protected $user, $cal_type, $auth;		// user and calendar/authid info
	protected $params;						// calendar instance (menu item) parameters

	public function __construct ($config=array())
	{
		parent::__construct($config);
		// get the component options
		$this->cOpts = JComponentHelper::getParams('com_usersched');
		// mainline a few of them
		$this->show_versions = $this->cOpts->get('show_versions', true);
		$this->version = $this->cOpts->get('version', 'n.n.n');
		// and get other generally need info
		$this->user = JFactory::getUser();
		$calid = UserSchedHelper::uState('calid');
		list($this->cal_type, $this->auth) = explode(':',$calid?$calid:'-1:');
		// get the calendar instance params
		$this->params = JFactory::getApplication()->getParams();
	}

	public function display ($tpl=null)
	{
		$app = JFactory::getApplication();

		// setup the page heading for each view
		// because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $app->getMenu()->getActive();
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', $this->params->get('page_title'));
		}

		parent::display($tpl);
	}

	protected function getUserDatabase ()
	{
		switch ($this->cal_type) {
			case 0:		// user
				$uid = $this->user->id;
				if ($uid <= 0) return false;
				return new RJUserData('sched');
				break;
			case 1:		// group
				$gid = $this->params->get('group_auth');
				return new RJUserData('sched',false,$gid,true);
				break;
			case 2:		// site
				$aid = $this->params->get('site_auth');
				return new RJUserData('sched',false,0,true);
				break;
		}
		return false;
	}

}
