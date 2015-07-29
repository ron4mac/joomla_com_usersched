<?php
defined('_JEXEC') or die;

class UserSchedController extends JControllerLegacy
{
	protected $userid;

	function __construct ($default=array())
	{
		parent::__construct($default);
		if (!isset($this->input)) $this->input = JFactory::getApplication()->input;		//J2.x
		$this->userid = JFactory::getUser()->id;
	}

	function doConfig ()
	{
		$calid = $this->input->getBase64('calid');
		$this->input->set('view','config');
		$m = $this->getModel();
		$m->setState('calid', base64_decode($calid));
		$this->display();
	}

	function setcfg ()
	{
		JSession::checkToken();
		$m = $this->getModel();
		$m->saveConfig($this->input->post);
		$this->setRedirect(JRoute::_('index.php?option=com_usersched', false), 'Configuration saved');
	}

	function impical ()
	{
		JSession::checkToken();
		$m = &$this->getModel();
		$r = $m->importical();
		if ($r) {
			$msg = 'iCalendar events imported';
			$fbk = 'success';
		} else {
			$msg = 'Failed to import events';
			$fbk = 'error';
		}
		JFactory::getApplication()->enqueueMessage($msg, $fbk);
		$this->setRedirect(JRoute::_('index.php?option=com_usersched', false));
	}

	function exp2ical ()
	{
		JSession::checkToken();
		$m = $this->getModel();
		$m->export2ical();
	//	jexit();
	}

/*	ajax call from client scheduler js (setup in view default) */
	function calXML ()
	{
		require_once('scheduler/codebase/connector/scheduler_connector.php');
		require_once('scheduler/codebase/connector/db_sqlite3.php');

		if (defined('RJC_DEV')) {
			JLog::addLogger(array('text_file' => 'usersched.log.php'), JLog::INFO, 'usersched');
			$l = print_r($_GET,true).print_r($_POST,true);
			JLog::add($l, JLog::INFO, 'usersched');
		}

		$dbtype = "SQLite3";
		list($caltyp,$jid) = explode(':',base64_decode($this->input->get('calid')));
		switch ($caltyp) {
			case 0:
				//$usrid = JFactory::getUser()->get('id');
				$dbpath = JPATH_SITE.'/userstor/@'.$this->userid.'/com_usersched/sched.sql3';
				break;
			case 1:
				$dbpath = JPATH_SITE.'/userstor/_'.$jid.'/com_usersched/sched.sql3';
				break;
			case 2:
				$dbpath = JPATH_SITE.'/userstor/_0/com_usersched/sched.sql3';
				break;
		}
		//$res = new SQLite3(dirname(__FILE__)."/database.sqlite");
		$res = new SQLite3($dbpath);

		$this->scheduler = new schedulerConnector($res, $dbtype);
		if (defined('RJC_DEV')) $this->scheduler->enable_log(JPATH_SITE.'/userstor/userschedconnlog.txt');
		$this->scheduler->event->attach('beforeProcessing',array($this,'delete_related'));
		$this->scheduler->event->attach('afterProcessing',array($this,'insert_related'));
		$this->scheduler->event->attach("beforeProcessing",array($this, "set_event_user"));
		$this->scheduler->event->attach("afterProcessing",array($this, "after_set_event_user"));
		$this->scheduler->render_table('events','event_id','start_date,end_date,text,category,rec_type,event_pid,event_length,user,alert_lead,alert_user,alert_meth');
	}

/*	below are callbacks for the scheduler connector */
	function delete_related ($action)
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

	function insert_related ($action)
	{
		$status = $action->get_status();
		$type = $action->get_value('rec_type');

		if ($status == 'inserted' && $type == 'none')
			$action->set_status('deleted');
	}

	function set_event_user ($action)
	{
		//if ($this->settings['templates_username'] == 'true') $action->remove_field('1');
		$status = $action->get_status();
		if ($status == "inserted") {
			$action->set_value("user", $this->userid);
		} else {
			if ($this->settings["privatemode"] == "ext") {
				$user = $action->get_value('user');
				if ($user != $this->userid) {
					$action->error();
				}
			}
		}
		if ($action->get_value('event_pid') == '') {
			$action->set_value('event_pid', 0);
		}
		if ($action->get_value('event_length') == '') {
			$action->set_value('event_length', 0);
		}
	}

	function after_set_event_user ($action)
	{
		$action->set_response_attribute("user", $this->userid);
	}

}
