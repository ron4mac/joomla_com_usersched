<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.0
*/
namespace RJCreations\Component\Usersched\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

\JLoader::register('UschedHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usched.php');

define('RJC_DEV', (JDEBUG) && file_exists(JPATH_ROOT.'/rjcdev.php'));

class DisplayController extends \Joomla\CMS\MVC\Controller\BaseController
{
	protected $default_view = 'usersched';
	protected $instanceObj;
	protected $theView;
	protected $userid = 0;
	protected $mnuItm = 0;

	function __construct ($default=[], $factory = null, $app = null, $input = null)
	{
		parent::__construct($default, $factory, $app, $input);
		$this->mnuItm = $input->getInt('Itemid', 0);
		$this->theView = $input->get('view','none');
		if ($this->theView == 'daterange') {
			$drmi = $app->getParams()->get('cal_menu');
			$this->instanceObj = \UschedHelper::getInstanceObject($drmi);
		} else {
			$this->instanceObj = \UschedHelper::getInstanceObject();
		}
		$this->userid = Factory::getUser()->id;
	}

	public function display ($cachable = false, $urlparams = false)
	{
		// need any alternate layout because 'getView()' will lose it otherwise
		$viewLayout = $this->input->get('layout', 'default', 'string');
		$inv = $this->input->get('view','none');
//		echo \UschedHelper::userDataPath();jexit();
		if ($inv == 'usersched' && $this->userid && !file_exists(\UschedHelper::userDataPath().'/usersched.db3')) {
			$this->input->set('view', 'config');
			$iview = $this->getView('config','html','site');	//,null,['layout'=>$viewLayout]);
			$iview->instObj = $this->instanceObj;
		}
//		$iview = $this->getView($inv,$this->input->get('dev',0,'integer')?'dev':'html','site');	//,'',['layout'=>$viewLayout]);
		$iview = $this->getView($inv,'html','site');	//,'',['layout'=>$viewLayout]);
		$iview->instObj = $this->instanceObj;

		if ($this->input->get('dev',0,'integer')) {
		//	Factory::getApplication()->getDocument()->setType('Dev');
		}
		parent::display($cachable, $urlparams);

		return $this;
	}

	public function doConfig ()
	{
//		$calid = $this->input->getBase64('calid');
		$m = $this->getModel('usersched');
//		$m->setState('calid', base64_decode($calid));
		$view = $this->getView('config','html');
		$view->instObj = $this->instanceObj;
		$view->setModel($m, true);
		$view->display();
	}

	public function setcfg ()
	{
		Session::checkToken();
		$m = $this->getModel('usersched');
		$m->saveConfig($this->input->post);
		$this->setRedirect(Route::_('index.php?option=com_usersched&view=usersched&Itemid='.$this->mnuItm, false), Text::_('COM_USERSCHED_CFG_SAVED'));
	}

	public function printView ()
	{
		$this->input->set('tmpl','component');
		$view = $this->getView('print', 'html');
		$view->setModel($this->getModel('usersched'), true);
		$view->post = $this->input->post;
		$view->display();
	}

	public function impical ()
	{
		Session::checkToken();
		$m = &$this->getModel();
		$r = $m->importical();
		if ($r) {
			$msg = 'iCalendar events imported';
			$fbk = 'success';
		} else {
			$msg = 'Failed to import events';
			$fbk = 'error';
		}
		Factory::getApplication()->enqueueMessage($msg, $fbk);
		$this->setRedirect(Route::_('index.php?option=com_usersched&view=config&Itemid='.$this->mnuItm, false));
	}

	public function exp2ical ()
	{
		Session::checkToken();
		$m = $this->getModel();
		$m->export2ical();
	//	jexit();
	}

/*	ajax call from client scheduler js (setup in view default) */

	public function calXML ()
	{
		require_once 'scheduler/codebase/connector/scheduler_connector.php';
		require_once 'scheduler/codebase/connector/db_sqlite3.php';

		if (RJC_DEV) {
			Log::addLogger(['text_file' => 'usersched.log.php'], Log::INFO, 'usersched');
			$l = print_r($_GET,true).print_r($_POST,true);
			Log::add($l, Log::INFO, 'usersched');
		}

		$dbpath = \UschedHelper::userDataPath().'/sched.sql3';
		$res = new SQLite3($dbpath);

		$this->scheduler = new schedulerConnector($res, 'SQLite3');
		if (RJC_DEV) $this->scheduler->enable_log(JPATH_SITE.'/tmp/userschedconnlog.txt');
		$this->scheduler->event->attach('beforeProcessing', [$this,'delete_related']);
		$this->scheduler->event->attach('afterProcessing', [$this,'insert_related']);
		$this->scheduler->event->attach('beforeProcessing', [$this,'set_event_user']);
		$this->scheduler->event->attach('afterProcessing', [$this,'after_set_event_user']);
		$this->scheduler->render_table('events','event_id','start_date,end_date,text,category,rec_type,event_pid,event_length,user,alert_lead,alert_user,alert_meth');
	}

/*	below are callbacks for the scheduler connector */
	public function delete_related ($action)
	{
		$status = $action->get_status();
		$type =$action->get_value('rec_type');
		$pid =$action->get_value('event_pid');
		//when series is changed or deleted we need to remove all linked events
		if (($status == 'deleted' || $status == 'updated') && $type != ''){
			$this->scheduler->sql->query('DELETE FROM events WHERE event_pid=\''.$this->scheduler->sql->escape($action->get_id()).'\'');
		}
		if ($status == 'deleted' && $pid != 0){
			$this->scheduler->sql->query('UPDATE events SET rec_type=\'none\' WHERE event_id=\''.$this->scheduler->sql->escape($action->get_id()).'\'');
			$action->success();
		}
	}

	public function insert_related ($action)
	{
		$status = $action->get_status();
		$type = $action->get_value('rec_type');

		if ($status == 'inserted' && $type == 'none')
			$action->set_status('deleted');
	}

	public function set_event_user ($action)
	{
		//if ($this->settings['templates_username'] == 'true') $action->remove_field('1');
		$status = $action->get_status();
		if ($status == 'inserted') {
			$action->set_value("user", $this->userid);
		}/* else {
			if ($this->settings['privatemode'] == 'ext') {
				$user = $action->get_value('user');
				if ($user != $this->userid) {
					$action->error();
				}
			}
		}*/
		if ($action->get_value('event_pid') == '') {
			$action->set_value('event_pid', 0);
		}
		if ($action->get_value('event_length') == '') {
			$action->set_value('event_length', 0);
		}
	}

	public function after_set_event_user ($action)
	{
		$action->set_response_attribute('user', $this->userid);
	}

}
