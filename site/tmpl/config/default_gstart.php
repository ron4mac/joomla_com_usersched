<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
/** @var HtmlView $this */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use RJCreations\Component\Usersched\Site\View\Config\HtmlView;
use RJCreations\Component\Usersched\Site\Helper\UserschedHelper;

$gtitle = UserschedHelper::groupTitle($this->grpId);
echo '<p>'.Text::sprintf('COM_USERSCHED_NEWGRPCAL',$gtitle).'</p>';
echo $this->loadTemplate('tform');
