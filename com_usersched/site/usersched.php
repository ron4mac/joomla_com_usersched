<?php
defined('_JEXEC') or die;

// Create the controller
$controller = JControllerLegacy::getInstance('UserSched');

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();
