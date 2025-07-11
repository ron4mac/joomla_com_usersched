<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.2.0
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// get the cdn version of dhtmlx scheduler that will be used
$dhxver = $this->params->get('dhtmlx_version', '6.0');

// determine needed CSS files and add them to head
$is_terrace = true;
$skin = $this->params->get('default_skin');
if ($this->settings['settings_skin']) $skin = $this->settings['settings_skin'];
$skinpath = 'components/com_usersched/' . ($skin ? 'skins/' : 'static/');
$skinopts = ['version' => 'auto'];
if ($skin) {
	$skinpath .= $skin.'/';
	$cssfiles = JFolder::files($skinpath, '^dhtmlxscheduler.+\.css$');
	if ($cssfiles) {
		$is_terrace = false;
		foreach ($cssfiles as $cssfile) {
			$this->document->addStylesheet($skinpath.$cssfile, $skinopts);
		}
	} else {
	//	$this->document->addStylesheet('components/com_usersched/scheduler/codebase/dhtmlxscheduler_'.$skin.'.css', $skinopts);
		$this->document->addStylesheet('https://cdn.dhtmlx.com/scheduler/'.$dhxver.'/dhtmlxscheduler_'.$skin.'.css');
		if (!in_array($skin, ['material','flat','contrast_black','contrast_white'])) $is_terrace = false;
	}
} else {
//	$this->document->addStylesheet('components/com_usersched/scheduler/codebase/dhtmlxscheduler.css', $skinopts);
	$this->document->addStylesheet('https://cdn.dhtmlx.com/scheduler/'.$dhxver.'/dhtmlxscheduler.css');
}
			$this->document->addStylesheet('components/com_usersched/static/usersched.'.$dhxver.'.css', $skinopts);
			//$this->document->addStylesheet($skinpath.'skin.css', $skinopts);
			if (JFile::exists($skinpath.'skin.css')) {
				$this->document->addStylesheet($skinpath.'skin.css');
			}

// get the calendar ID
$calID = base64_encode(UserSchedHelper::uState('calid'));

// gather and inline needed javascript variables
$script = '';
//$script .= 'var usched_calid = "'.$calID.'";';
$script .= 'USched.mode = "'.$this->settings['settings_defaultmode'].'";';
$script .= 'USched.base = "'.Uri::base(true).'";';

// setup config button
if ($this->canCfg) {
	$cfgurl = Route::_('index.php?option=com_usersched&task=doConfig&Itemid='.$this->mnuItm, false);
	$script .= 'USched.cfgBTN = \'<img src="components/com_usersched/static/cfg16-4.png" title="Configure calendar" class="usched_act" alt="" onclick="window.location=\\\''.$cfgurl.'\\\'" />\';';
}
// setup print button
$script .= 'USched.prnBTN = \'<img src="components/com_usersched/static/printer-2.png" title="Print Calendar" class="usched_act" style="left:20px" alt="" onclick="USched.printView()" />\';';

//$script .= 'var userschedlurl = "' . JURI::base() . 'index.php?option=com_usersched&view=usersched&task=calXML&calid=' . $calID .'";';
//$script .= 'USched.URL = "' . Uri::base() . 'index.php?option=com_usersched&Itemid='.$this->instObj->menuid.'&format=raw&task=calXML&calid=' . urlencode($calID) .'";';

$script .= 'USched.mobile = '.($this->mobile?'true;':'false;');

$script .= 'USched.URL = "' . Uri::base() . 'index.php?option=com_usersched&Itemid='.$this->instObj->menuid.'&format=raw&task=Raw.calJ6&calid=' . urlencode($calID) .'";';

$script .= 'scheduler.cfg_cfg = '.$this->cfgcfg.';';
$script .= 'scheduler.__categories = ['.implode(',',$this->categoriesJSON()).'];';
if ($this->alertees) {
	$script .= 'scheduler.alertWho = \'';
	foreach ($this->alertees as $a) {
		$script .= '<option value="'.$a['id'].'">'.$a['name'].'</option>';
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

$jawc = new JApplicationWebClient();
if ($jawc->mobile) $jscodes .= 'M';
$this->document->addScript('https://cdn.dhtmlx.com/scheduler/'.$dhxver.'/dhtmlxscheduler.js');
$this->document->addScript('components/com_usersched/js.php?'.$jscodes, $skinopts);


$this->document->addScript('components/com_usersched/scheduler/export.js', $skinopts);


$this->document->addScriptDeclaration($script);
if (JFile::exists($skinpath.'skin.js')) {
	$this->document->addScript($skinpath.'skin.js', $skinopts);
}
$this->document->addStyleDeclaration($this->categoriesCSS());

$this->document->addScript('https://printjs-4de6.kxcdn.com/print.min.js');

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
<div id="scheduler_here" class="dhx_cal_container" style='width:auto; /*height:800px;*/'>
<?php if ($this->canCfg) :?>
	<img src="components/com_usersched/static/cfg16-4.png" title="Configure calendar" class="usched_act" alt="" style="left:0px;" onclick="window.location='<?php echo Route::_('index.php?option=com_usersched&task=doConfig&Itemid='.$this->mnuItm, false); ?>'" />
<?php endif; ?>
<?php if (true) :?>
	<!-- <img src="components/com_usersched/static/printer-2.png" title="Print calendar" class="usched_act" alt="" style="left:<?=$icns_left+=$icns_leftx?>px;" onclick="scheduler.toPDF('<?=Uri::base()?>components/com_usersched/pdf/generate.php','fullcolor')" /> -->
	<img src="components/com_usersched/static/printer-2.png" title="Print calendar" class="usched_act" alt="" style="left:<?=$icns_left+=$icns_leftx?>px;" onclick="printJS('scheduler_here','html')" />
<?php endif; ?>
	<div class="dhx_cal_navline">
	<?php echo $this->loadTemplate('material'); ?>
	</div>
	<div class="dhx_cal_header"></div>
	<div class="dhx_cal_data"></div>
</div>
<?php if ($this->show_versions) :?>
<div id="versionbar" class="userschedver">UserSched <span id="userschedver"><?php echo $this->version.' :: '.date('F j, Y, g:i a') ?></span></div><div class="schedulerver">Scheduler <span id="schedulerver">x.x.x</span></div>
<?php else: ?>
<!-- UserSched <?php echo $this->version ?> -->
<?php endif; ?>
<script type="text/javascript">
	// set this here so it is last in the chain
	USched.tabs = <?=json_encode($this->tabs)?>;
	USched.plugs = <?=json_encode($this->plugs)?>;
	document.addEventListener('DOMContentLoaded', function() {USched.init();});
</script>
