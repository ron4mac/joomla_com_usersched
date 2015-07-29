<?php
defined('_JEXEC') or die;

$gtitle = UserSchedHelper::groupTitle($this->grpId);
echo '<p>'.JText::sprintf('COM_USERSCHED_NEWGRPCAL',$gtitle).'</p>';
echo $this->loadTemplate('tform');
?>
