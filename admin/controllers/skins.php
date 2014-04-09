<?php
defined('_JEXEC') or die;

class UserschedControllerSkins extends JControllerLegacy
{
	

	public function display ($cachable=false, $urlparams=false)
	{
		require_once JPATH_COMPONENT.'/helpers/usersched.php';
echo '@@@@@@@@@@@@@@@++++++++++++++++++++++++@@@@@@@@@@@@@@';exit();
		// Load the submenu.
		$jinput = JFactory::getApplication()->input;
		UserschedHelper::addSubmenu($jinput->get('view', 'usersched'));

		parent::display();
	}


	public function makeDfltU ()
	{
		$this->setDefaultSkin('default_skin');
	}
	public function makeDfltG ()
	{
		$this->setDefaultSkin('group_default_skin');
	}
	public function makeDfltS ()
	{
		$this->setDefaultSkin('site_default_skin');
	}

	private function setDefaultSkin ($which)
	{
		$jinput = JFactory::getApplication()->input;
		$rows = $jinput->get('cid',array(),'array');
		$view = $jinput->get('view','skins');
		$this->setCompParam($which, $rows[0]);
		$this->setRedirect('index.php?option=com_usersched&view='.$view);
	}

	private function setCompParam ($param, $val)
	{
		// To access the extensions table we need the id of the component
		$compomentId = JComponentHelper::getComponent('com_usersched')->id;
		assert($compomentId != 0); // make sure that no error will cause the creation of a new entry in the extenions table
		
		// set the new value using set()
		$params = JComponentHelper::getParams('com_usersched');
		$params->set($param, $params->get($param)==$val?'':$val);
		
		// get an instance of the table class, load the component, overwrite the param-string with the new parameter values
		$table = JTable::getInstance('extension');
		$table->load($compomentId);
		$table->bind(array('params' => $params->toString()));
		
		// check and store with some simple error handling
		if (!$table->check()) {
			$this->setError("set:{$param} check: ".$table->getError());
			return false;
		}
		if (!$table->store()) {
			$this->setError("set:{$param} store: ".$table->getError());
			return false;
		}
		return true;
	}

}
