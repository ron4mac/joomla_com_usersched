<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

define('RJC_DEV', JDEBUG && file_exists(JPATH_ROOT.'/rjcdev.php'));

JLoader::register('RJUserCom', JPATH_LIBRARIES . '/rjuser/com.php');

// Create the controller
$controller = BaseController::getInstance('UserSched');

// Perform the Request task
$controller->execute(Factory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();
