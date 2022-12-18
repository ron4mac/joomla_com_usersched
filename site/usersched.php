<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

JLoader::register('RJUserCom', JPATH_LIBRARIES . '/rjuser/com.php');

// Create the controller
$controller = BaseController::getInstance('UserSched');

// Perform the Request task
$controller->execute(Factory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();
