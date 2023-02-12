<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

echo '<p>'.Text::_('COM_USERSCHED_NEWCAL').'</p>';
echo $this->loadTemplate('tform');
