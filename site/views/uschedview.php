<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/*
 * This is a base view class to (hopefully) avoid duplication of code needed by all views
 */

jimport('joomla.application.component.helper');

//require_once JPATH_COMPONENT.'/helpers/usersched.php';
JLoader::register('UserSchedHelper', JPATH_COMPONENT . '/helpers/usersched.php');
JLoader::register('JHtmlUsersched', JPATH_COMPONENT . '/helpers/html/usersched.php');

class UserschedView extends JViewLegacy
{
	protected $cOpts;						// component options
	protected $show_versions, $version;		// settings for version display
	protected $user, $cal_type, $auth;		// user and calendar/authid info
	protected $params;						// calendar instance (menu item) parameters
	protected $mnuItm;						// menu id for this instance

	public function __construct ($config=[])
	{
		parent::__construct($config);
		if (!$this->document) $this->document = Factory::getDocument();
		// get the menu id
		$app = Factory::getApplication();
		$this->mnuItm = $app->input->getInt('Itemid');
		// get the component options
		$this->cOpts = ComponentHelper::getParams('com_usersched');
		// mainline a few of them
		$this->show_versions = $this->cOpts->get('show_versions', true);
		$this->version = $this->cOpts->get('version', 'n.n.n');
		// and get other generally needed info
		$this->user = Factory::getUser();
		$calid = UserSchedHelper::uState('calid');
		list($this->cal_type, $this->auth) = explode(':',$calid?$calid:'-1:');
		// get the calendar instance params
		$this->params = Factory::getApplication()->getParams();
	}

}
