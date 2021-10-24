<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$gtitle = UserSchedHelper::groupTitle($this->grpId);
echo '<p>'.Text::sprintf('COM_USERSCHED_NEWGRPCAL',$gtitle).'</p>';
echo $this->loadTemplate('tform');
