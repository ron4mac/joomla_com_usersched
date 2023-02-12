<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$gtitle = UserSchedHelper::groupTitle($this->grpId);
echo '<p>'.Text::sprintf('COM_USERSCHED_NEWGRPCAL',$gtitle).'</p>';
echo $this->loadTemplate('tform');
