<?php
defined('_JEXEC') or die;
//echo'<xmp>';var_dump($this);echo'</xmp>';jexit();
if ($this->canCfg) {
	JHtml::script('components/com_usersched/static/config.js',true);
	JHtml::script('components/com_usersched/static/color-picker.js');
	JHtml::stylesheet('components/com_usersched/static/config.css');
	$this->config = $this->settings;
	echo '<div id="ical-inout"><a href="index.php?option=com_usersched&view=ical">Import/Export iCalendar events</a> (experimental)</div>';
	echo $this->loadTemplate('tform');
} else {
	echo 'NOT ALLOWED';
}
?>