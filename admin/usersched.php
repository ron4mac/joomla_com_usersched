<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_usersched')) {
	return JError::raiseWarning(404, Text::_('JERROR_ALERTNOAUTHOR'));
}

$j_version = preg_replace('#[^0-9\.]#i','',JVERSION);
define('USERSCHED_J30', version_compare($j_version,'3.0.0','>=') ? true : false);

// Shared scripts for all views
$doc = Factory::getDocument();
$doc->addStyleSheet(JURI::root() . 'administrator/components/com_usersched/static/admin.css');

$controller = JControllerLegacy::getInstance('Usersched');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
