<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

JLoader::register('RJUserCom', JPATH_LIBRARIES . '/rjuser/com.php');

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_usersched')) {
	return JError::raiseWarning(404, Text::_('JERROR_ALERTNOAUTHOR'));
}

define('USERSCHED_J30', (int)JVERSION < 4 ? true : false);

// Shared scripts for all views
$doc = Factory::getDocument();
$doc->addStyleSheet(JURI::root() . 'administrator/components/com_usersched/static/admin.css');

$controller = JControllerLegacy::getInstance('Usersched');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
