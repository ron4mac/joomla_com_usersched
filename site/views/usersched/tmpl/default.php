<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

//echo'<xmp>';var_dump($this);echo'</xmp>';jexit();

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
		$this->document->addStylesheet('components/com_usersched/scheduler/codebase/dhtmlxscheduler_'.$skin.'.css', $skinopts);
		if (!in_array($skin, ['material','flat','contrast_black','contrast_white'])) $is_terrace = false;
	}
} else {
	$this->document->addStylesheet('components/com_usersched/scheduler/codebase/dhtmlxscheduler.css', $skinopts);
}
$this->document->addStylesheet('components/com_usersched/static/usersched.css', $skinopts);
$this->document->addStylesheet($skinpath.'skin.css', $skinopts);

// get the calendar ID
$calID = base64_encode(UserSchedHelper::uState('calid'));

// gather and inline needed javascript variables
$script = 'var usched_calid = "'.$calID.'";';
$script .= 'var usched_mode = "'.$this->settings['settings_defaultmode'].'";';
$script .= 'var usched_base = "'.Uri::base(true).'";';
//$script .= 'var userschedlurl = "' . JURI::base() . 'index.php?option=com_usersched&view=usersched&task=calXML&calid=' . $calID .'";';
$script .= 'var userschedlurl = "' . Uri::base() . 'index.php?option=com_usersched&Itemid='.$this->instObj->menuid.'&format=raw&task=calXML&calid=' . urlencode($calID) .'";';
////////$script .= 'scheduler.cfg_cfg = '.$this->cfgcfg.';';
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
$this->document->addScript('components/com_usersched/js.php?'.$jscodes, $skinopts);
$this->document->addScriptDeclaration($script);
if (JFile::exists($skinpath.'skin.js')) {
	$this->document->addScript($skinpath.'skin.js');
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
<div id="scheduler_here" class="dhx_cal_container" style='width:auto; height:800px;'>
<?php if ($this->canCfg) :?>
	<img src="components/com_usersched/static/cfg16-4.png" title="Configure calendar" class="usched_act" alt="" style="left:<?=$icns_left+=$icns_leftx?>px;" onclick="window.location='<?php echo Route::_('index.php?option=com_usersched&task=doConfig&Itemid='.$this->mnuItm, false); ?>'" />
<?php endif; ?>
	<!-- <img src="components/com_usersched/static/printer-2.png" title="Print calendar" class="usched_act" alt="" style="left:<?=$icns_left+=$icns_leftx?>px;" onclick="scheduler.toPDF('<?=Uri::base()?>components/com_usersched/pdf/generate.php','fullcolor')" /> -->
	<!-- <img src="components/com_usersched/static/printer-2.png" title="Print calendar" class="usched_act" alt="" style="left:<?=$icns_left+=$icns_leftx?>px;" onclick="printJS('scheduler_here','html')" /> -->
	<div class="dhx_cal_navline">
<?php if (false && $is_terrace) :?>
		<div class="dhx_cal_prev_button" style="left:50px">&nbsp;</div>
		<div class="dhx_cal_next_button" style="left:97px">&nbsp;</div>
		<div class="dhx_cal_today_button" style="left:148px"></div>
<?php elseif (false): ?>
		<div class="dhx_cal_prev_button">&nbsp;</div>
		<div class="dhx_cal_next_button">&nbsp;</div>
		<div class="dhx_cal_today_button"></div>
<?php endif; ?>
<?php if ($is_terrace) :?>
		<?php
		echo $this->loadTemplate('material');
		?>
		<div class="dhx_cal_date"></div>
		<?php if (false && $this->settings['settings_agenda']): ?>
		<div class="dhx_cal_tab" name="agenda_tab" style="left:<?=$tabs_left+=$tabs_leftx?>px;"></div>
		<?php endif; ?>
		<?php if (false && $this->settings['settings_day']): ?>
		<div class="dhx_cal_tab" name="day_tab" style="left:<?=$tabs_left+=$tabs_leftx?>px;"></div>
		<?php endif; ?>
		<?php if (false && $this->settings['settings_week']): ?>
		<div class="dhx_cal_tab" name="week_tab" style="left:<?=$tabs_left+=$tabs_leftx?>px;"></div>
		<?php endif; ?>
		<?php if (false && $this->settings['settings_month']): ?>
		<div class="dhx_cal_tab" name="month_tab" style="left:<?=$tabs_left+=$tabs_leftx?>px;"></div>
		<?php endif; ?>
		<?php if (false && $this->settings['settings_year']): ?>
		<div class="dhx_cal_tab" name="year_tab" style="left:<?=$tabs_left+=$tabs_leftx?>px;"></div>
		<?php endif; ?>
<?php else: ?>
		<div class="dhx_cal_date"></div>
		<?php if ($this->settings['settings_year']): ?>
		<div class="dhx_cal_tab" name="year_tab" style="right:<?=$tabs_right+=$tabs_rightx?>px;"></div>
		<?php endif; ?>
		<?php if ($this->settings['settings_month']): ?>
		<div class="dhx_cal_tab" name="month_tab" style="right:<?=$tabs_right+=$tabs_rightx?>px;"></div>
		<?php endif; ?>
		<?php if ($this->settings['settings_week']): ?>
		<div class="dhx_cal_tab" name="week_tab" style="right:<?=$tabs_right+=$tabs_rightx?>px;"></div>
		<?php endif; ?>
		<?php if ($this->settings['settings_day']): ?>
		<div class="dhx_cal_tab" name="day_tab" style="right:<?=$tabs_right+=$tabs_rightx?>px;"></div>
		<?php endif; ?>
		<?php if ($this->settings['settings_agenda']): ?>
		<div class="dhx_cal_tab" name="agenda_tab" style="right:<?=$tabs_right+=$tabs_rightx?>px;"></div>
		<?php endif; ?>
<?php endif; ?>
	</div>
	<div class="dhx_cal_header"></div>
	<div class="dhx_cal_data"></div>
</div>
<?php if ($this->show_versions) :?>
<div id="versionbar" class="userschedver">UserSched <span id="userschedver"><?php echo $this->version ?></span></div><div class="schedulerver">Scheduler <span id="schedulerver">x.x.x</span></div>
<?php else: ?>
<!-- UserSched <?php echo $this->version ?> -->
<?php endif; ?>
<?php
	//echo'<xmp>';var_dump($this);echo'</xmp>';
?>
<script type="text/javascript">
	// set this here so it is last in the chain
	document.addEventListener('DOMContentLoaded', function() {usersched_init();});
</script>
