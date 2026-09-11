<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
/** @var HtmlView $this */

use Joomla\CMS\Language\Text;
use RJCreations\Component\Usersched\Site\View\Daterange\HtmlView;

//echo'<xmp>';var_dump($this->user->groups,$this->params->get('site_auth'));echo'</xmp>';
echo Text::_('COM_USERSCHED_NO_ACCESS');
