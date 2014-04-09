<?php
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_usersched')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Shared scripts for all views
$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::root() . 'administrator/components/com_usersched/static/admin.css');

$controller = JControllerLegacy::getInstance('UserSched');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
