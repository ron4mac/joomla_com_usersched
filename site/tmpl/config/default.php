<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
/** @var HtmlView $this */
defined('_JEXEC') or die;

use RJCreations\Component\Usersched\Site\View\Config\HtmlView;

//echo'<xmp>';var_dump($this);echo'</xmp>';jexit();
if ($this->canCfg) {
	$this->config = $this->settings;
	if ($this->params->get('show_page_heading', 1)) {
		echo '<div class="page-header"><h3>'.$this->escape($this->params->get('page_heading')).'</h3></div>';
	}
	echo $this->loadTemplate('tform');
} else {
	echo 'NOT ALLOWED';
}
