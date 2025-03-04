<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2025 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.0
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

// determine needed CSS files and add them to head
$is_terrace = true;
$skin = $this->params->get('default_skin');
if ($this->settings['settings_skin']) $skin = $this->settings['settings_skin'];
$skinpath = 'components/com_usersched/' . ($skin ? "skins/{$skin}/" : 'static/');
$skinopts = ['version' => 'auto'];

$this->document->addStylesheet('https://cdn.dhtmlx.com/scheduler/7.2/dhtmlxscheduler.css');
$this->document->addStylesheet('components/com_usersched/static/usersched.7.2.css', $skinopts);
if (is_file($skinpath.'skin.css')) {
	$this->document->addStylesheet($skinpath.'skin.css', $skinopts);
}
if (is_file($skinpath.'custom.css')) {
	$this->document->addStylesheet($skinpath.'custom.css', $skinopts);
}

// setup hamburger menuid
$ham_menu_items = '<li><a href="#" onclick="USched.printView(event,this)"><img src="components/com_usersched/static/printer.png" title="Print Calendar"> Print2PDF</a></li>';
if ($this->canCfg) {
	$cfgurl = Route::_('index.php?option=com_usersched&task=doConfig&Itemid='.$this->mnuItm, false);
	$ham_menu_items .= '<li><a href="'.$cfgurl.'"><img src="components/com_usersched/static/cfg16-3.png" title="Configure Calendar"> Config</a></li>';
}

// get the calendar ID
$calID = base64_encode(UserSchedHelper::uState('calid'));

// gather and inline needed javascript variables
$script = '';
if ($skin) $script .= 'scheduler.skin = "'.$skin.'";';

//$script .= 'var usched_calid = "'.$calID.'";';
$script .= 'USched.mode = "'.$this->settings['settings_defaultmode'].'";';
$script .= 'USched.base = "'.Uri::base(true).'";';

$script .= 'USched.mobile = '.($this->mobile?'true;':'false;');

$script .= 'USched.URL = "' . Uri::base() . 'index.php?option=com_usersched&Itemid='.$this->instObj->menuid.'&format=raw&task=Raw.calJ7r&calid=' . urlencode($calID) .'";';

$script .= 'scheduler.cfg_cfg = '.$this->cfgcfg.';';
$script .= 'scheduler.__categories = ['.implode(',',$this->categoriesJSON()).'];';
if ($this->alertees) {
	$script .= 'scheduler.alertWho = \'';
	foreach ($this->alertees as $a) {
	//	$script .= '<option value="'.$a['id'].'">'.$a['name'].'</option>';
		$i = $a['id'];
		$script .= '<input id="us-ae-'.$i.'" type="checkbox" class="us-ckund" value="'.$i.'" onchange="scheduler.form_blocks.alerts_editor.updtae(this)"><label for="us-ae-'.$i.'">'.$a['name'].'</label>';
	}
	$script .= '\';';
}
$locale = Factory::getLanguage()->getLocale();
$jscodes = 'l='.$locale[4];
$jscodes .= '&c=R';
if ($this->params->get('can_alert') && $this->alertees) {
	$jscodes .= 'J';
	$script .= 'scheduler.feature.canAlert = 1;';
}
if ($this->settings['settings_year']) $jscodes .= 'Y';
if ($this->settings['settings_agenda']) $jscodes .= 'G';
if ($this->settings['settings_ushol']) $jscodes .= 'H';
if ($this->settings['settings_bday']) $jscodes .= 'B';
if ($this->canCfg) $jscodes .= 'A';

if ($this->mobile) $jscodes .= 'M';
$this->document->addScript('https://cdn.dhtmlx.com/scheduler/7.2/dhtmlxscheduler.js');
$this->document->addScript('components/com_usersched/js.php?'.$jscodes, $skinopts);

$this->document->addScriptDeclaration($script);
$this->document->addStyleDeclaration($this->categoriesCSS());

$icns_left = -17;
$icns_leftx = 20;
$tabs_right = -42;
$tabs_rightx = 64;
$tabs_left = -47;
$tabs_leftx = 61;

if ($this->params->get('show_page_heading', 1)) {
	echo '<div class="page-header"><h3>'.$this->escape($this->params->get('page_heading')).'</h3></div>';
}
?>
<div id="usched_container">
	<div id="scheduler_here" class="dhx_cal_container" style='width:auto; /*height:100%;*/'></div>
	<div class="uschd-off-menu">
		<ul>
			<?=$ham_menu_items?>
		</ul>
	</div>
	<div class="uschd-ham-menu">
		<span></span>
		<span></span>
		<span></span>
	</div>
	<?php if ($this->show_versions) :?>
	<div id="versionbar" class="userschedver">UserSched <span id="userschedver"><?php echo $this->version.' :: '.date('F j, Y, g:i a') ?></span></div><div class="schedulerver">Scheduler <span id="schedulerver">x.x.x</span></div>
	<?php else: ?>
	<!-- UserSched <?php echo $this->version ?> -->
	<?php endif; ?>
</div>
<script type="text/javascript">
	// set this here so it is last in the chain
	USched.tabs = <?=json_encode($this->tabs)?>;
	USched.plugs = <?=json_encode($this->plugs)?>;
	document.addEventListener('DOMContentLoaded', function() {USched.init();});
	// hamburger menu
	const uschdHamMenu = document.querySelector('.uschd-ham-menu');
	const uschdOffMenu = document.querySelector('.uschd-off-menu');
	uschdHamMenu.addEventListener('click', () => {
		uschdHamMenu.classList.toggle('active');
		uschdOffMenu.classList.toggle('active');
	});
</script>
