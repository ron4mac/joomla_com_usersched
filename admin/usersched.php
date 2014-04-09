<?php
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_usersched')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$j_version = preg_replace('#[^0-9\.]#i','',JVERSION);
define('USERSCHED_J30', version_compare($j_version,'3.0.0','>=') ? true : false);

// Shared scripts for all views
$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::root() . 'administrator/components/com_usersched/static/admin.css');

$controller = JControllerLegacy::getInstance('Usersched');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
