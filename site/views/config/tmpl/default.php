<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;
//echo'<xmp>';var_dump($this);echo'</xmp>';jexit();
if ($this->canCfg) {
	$this->config = $this->settings;
	if ($this->params->get('show_page_heading', 1)) {
		echo '<div class="page-header"><h3>'.$this->escape($this->params->get('page_heading')).'</h3></div>';
	}
	echo '<div id="ical-inout"><a href="index.php?option=com_usersched&view=ical">Import/Export iCalendar events</a> (experimental)</div>';
	echo $this->loadTemplate('tform');
} else {
	echo 'NOT ALLOWED';
}
