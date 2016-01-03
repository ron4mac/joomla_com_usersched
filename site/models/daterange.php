<?php
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/models/usersched.php';

class UserSchedModelDaterange extends UserSchedModelUserSched
{

	public function __construct ($config = array())
	{
		$dbFile = UschedHelper::userDataPath() . '/sched.sql3';
		// if no calendar yet exists, dont let the model parent create one
		if (!file_exists($dbFile)) {
			$config['dbo'] = 0;	// signal to NOT create db file
		}
		parent::__construct($config);
	}

	public function hasData ()
	{
		return (bool) $this->getDbo();
	}

}