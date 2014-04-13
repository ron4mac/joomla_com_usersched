<?php
defined('_JEXEC') or die;

/**
 * Events list controller class.
 */
class UserschedControllerEvents extends JControllerLegacy
{

/*
	public function __construct($config = array())
	{
		var_dump($config,'hhjjkk');jexit();
		parent::__construct($config);
	}
*/

	public function delete ()
	{
		$jinput = JFactory::getApplication()->input;
		$dels = $jinput->get('cid',array(),'array');
		$view = $jinput->get('view');
		$uid = $jinput->getInt('uid',-1);
		$isGrp = $jinput->getBool('isGrp',false);

		$model = $this->getModel('events');
		$model->deleteEvents($dels, $uid, $isGrp);

		$this->setRedirect('index.php?option=com_usersched&view='.$view.'&uid='.$uid.($isGrp?('&isGrp='.$isGrp):''),JText::_('COM_USERSCHED_MSG_COMPLETE'));
	}

}
