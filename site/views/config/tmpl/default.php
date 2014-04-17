<?php
defined('_JEXEC') or die;
//echo'<xmp>';var_dump($this);echo'</xmp>';jexit();
if ($this->canCfg) {
	JHtml::script('components/com_usersched/static/config.js',true);
	JHtml::script('components/com_usersched/static/color-picker.js');
	JHtml::stylesheet('components/com_usersched/static/config.css');
	$this->config = $this->settings;
	if ($this->params->get('show_page_heading', 1)) {
		echo '<div class="page-header"><h3>'.$this->escape($this->params->get('page_heading')).'</h3></div>';
	}
	echo '<div id="ical-inout"><a href="index.php?option=com_usersched&view=ical">Import/Export iCalendar events</a> (experimental)</div>';
	echo $this->loadTemplate('tform');
} else {
	echo 'NOT ALLOWED';
}
?>