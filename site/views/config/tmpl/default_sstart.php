<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

echo '<p>'.Text::_('COM_USERSCHED_NEWSITECAL').'</p>';
echo $this->loadTemplate('tform');
