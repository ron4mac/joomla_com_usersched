<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
/** @var HtmlView $this */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use RJCreations\Component\Usersched\Site\View\Config\HtmlView;

echo '<p>'.Text::_('COM_USERSCHED_NEWCAL').'</p>';
echo $this->loadTemplate('tform');
