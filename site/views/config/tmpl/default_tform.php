<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$fact = Route::_('index.php?option=com_usersched&view=usersched&Itemid='.$this->mnuItm, false);
?>
<form id="configform" action="<?=$fact?>" method="post">
<input class="btn btn-primary" type="submit" name="saves" value="<?=Text::_('COM_USERSCHED_CFG_SAVE')?>" onclick="this.form.task.value='setcfg'" />
<div id="formtabs" class="stabber">
<div class="stabbertab" title="<?=Text::_('COM_USERSCHED_CFG_GENERAL')?>">
	<input type="checkbox" name="settings_repeat" id="settings_repeat"<?=$this->config['settings_repeat']?' checked="checked"':''?> />
	<label for="settings_repeat"><?= Text::_('COM_USERSCHED_CFG_RECURR') ?></label>
	<input type="checkbox" name="settings_firstday" id="settings_firstday"<?=$this->config['settings_firstday']?' checked="checked"':''?> />
	<label for="settings_firstday"><?= Text::_('COM_USERSCHED_CFG_WKSTART') ?></label>
	<input type="checkbox" name="settings_multi_day" id="settings_multi_day"<?=$this->config['settings_multi_day']?' checked="checked"':''?> />
	<label for="settings_multi_day"><?= Text::_('COM_USERSCHED_CFG_MULTIDAY') ?></label>
	<input type="checkbox" name="settings_fullday" id="settings_fullday"<?=$this->config['settings_fullday']?' checked="checked"':''?> />
	<label for="settings_fullday"><?= Text::_('COM_USERSCHED_CFG_FULLDAY') ?></label>
<?php if (false): ?>
	<input type="checkbox" name="settings_marknow" id="settings_marknow"<?=$this->config['settings_marknow']?' checked="checked"':''?> />
	<label for="settings_marknow">Mark now</label>
	<input type="checkbox" name="settings_singleclick" id="settings_singleclick"<?=$this->config['settings_singleclick']?' checked="checked"':''?> />
	<label for="settings_singleclick">Create events by single-click</label>
<?php endif; ?>
	<input type="checkbox" name="settings_collision" id="settings_collision"<?=$this->config['settings_collision']?' checked="checked"':''?> />
	<label for="settings_collision"><?= Text::_('COM_USERSCHED_CFG_OVERLAP') ?></label>
	<input type="checkbox" name="settings_expand" id="settings_expand"<?=$this->config['settings_expand']?' checked="checked"':''?> />
	<label for="settings_expand">Expand button</label>
<?php if (false): ?>
	<input type="checkbox" name="settings_pdf" id="settings_pdf"<?=$this->config['settings_pdf']?' checked="checked"':''?> />
	<label for="settings_pdf">Print to PDF</label>
	<input type="checkbox" name="settings_ical" id="settings_ical"<?=$this->config['settings_ical']?' checked="checked"':''?> />
	<label for="settings_ical">Export to iCal</label>
<?php endif; ?>
	<input type="checkbox" name="settings_minical" id="settings_minical"<?=$this->config['settings_minical']?' checked="checked"':''?> />
	<label for="settings_minical"><?= Text::_('COM_USERSCHED_CFG_MINICAL') ?></label>
	<label for="settings_eventnumber"><?=Text::_('COM_USERSCHED_CFG_MAXEVENT')?></label>
	<input type="number" name="settings_eventnumber" id="settings_eventnumber" class="numer" value="<?= $this->config['settings_eventnumber'] ?>" />
</div>
<div class="stabbertab" title="<?=Text::_('COM_USERSCHED_CFG_MODES')?>">
	<div class="twocol">
	<input type="checkbox" name="settings_day" id="settings_day"<?=$this->config['settings_day']?' checked="checked"':''?> />
	<label for="settings_day"><?= Text::_('COM_USERSCHED_CFG_DAY') ?></label>
	<input type="checkbox" name="settings_week" id="settings_week"<?=$this->config['settings_week']?' checked="checked"':''?> />
	<label for="settings_week"><?= Text::_('COM_USERSCHED_CFG_WEEK') ?></label>
	<input type="checkbox" name="settings_month" id="settings_month"<?=$this->config['settings_month']?' checked="checked"':''?> />
	<label for="settings_month"><?= Text::_('COM_USERSCHED_CFG_MONTH') ?></label>
	<input type="checkbox" name="settings_year" id="settings_year"<?=$this->config['settings_year']?' checked="checked"':''?> />
	<label for="settings_year"><?= Text::_('COM_USERSCHED_CFG_YEAR') ?></label>
	<input type="checkbox" name="settings_agenda" id="settings_agenda"<?=$this->config['settings_agenda']?' checked="checked"':''?> />
	<label for="settings_agenda"><?= Text::_('COM_USERSCHED_CFG_AGENDA') ?></label>
<?php if (false): ?>
	<input type="checkbox" name="settings_week_agenda" id="settings_week_agenda"<?=$this->config['settings_week_agenda']?' checked="checked"':''?> />
	<label for="settings_week_agenda"><?= Text::_('COM_USERSCHED_CFG_WAGENDA') ?></label>
	<input type="checkbox" name="settings_map" id="settings_map"<?=$this->config['settings_map']?' checked="checked"':''?> />
	<label for="settings_map"><?= Text::_('COM_USERSCHED_CFG_MAP') ?></label>
<?php endif; ?>
	<div class="clr">&nbsp;</div>
	<?php $defaultmode = $this->config['settings_defaultmode'];?>
	<label for="settings_defaultmode"><?= Text::_('COM_USERSCHED_CFG_DFLTMODE') ?></label>
	<select name="settings_defaultmode" id="settings_defaultmode">
		<option value="day" locale="day"<?=$defaultmode=='day'?' selected="selected"':''?>><?= Text::_('COM_USERSCHED_CFG_DAY') ?></option>
		<option value="week" locale="week"<?=$defaultmode=='week'?' selected="selected"':''?>><?= Text::_('COM_USERSCHED_CFG_WEEK') ?></option>
		<option value="month" locale="month"<?=$defaultmode=='month'?' selected="selected"':''?>><?= Text::_('COM_USERSCHED_CFG_MONTH') ?></option>
		<option value="year" locale="year"<?=$defaultmode=='year'?' selected="selected"':''?>><?= Text::_('COM_USERSCHED_CFG_YEAR') ?></option>
		<option value="agenda" locale="agenda"<?=$defaultmode=='agenda'?' selected="selected"':''?>><?= Text::_('COM_USERSCHED_CFG_AGENDA') ?></option>
	<!--	<option value="week_agenda" locale="week_agenda"<?=$defaultmode=='week_agenda'?' selected="selected"':''?>><?= Text::_('COM_USERSCHED_CFG_WAGENDA') ?></option>
		<option value="map" locale="map"<?=$defaultmode=='map'?' selected="selected"':''?>><?= Text::_('COM_USERSCHED_CFG_MAP') ?></option> -->
	</select>
<?php if ($this->canSkin): ?>
	<div class="clr">&nbsp;</div>
	<label for="settings_skin"><?= Text::_('COM_USERSCHED_CFG_CALSKIN') ?></label>
	<?=HTMLHelper::_('select.genericlist', $this->skinOptions, 'settings_skin', '', 'value', 'text', $this->config['settings_skin'], 'settings_skin'); ?>
<?php endif; ?>
	</div>
	<div class="twocol">
		<p class="subtitl"><?= Text::_('COM_USERSCHED_CFG_EXTEVTS') ?></p>
		<div style="padding-left:.5em">
		<input type="checkbox" name="settings_ushol" id="settings_ushol"<?=(isset($this->config['settings_ushol'])&&$this->config['settings_ushol'])?' checked="checked"':''?> />
		<label for="settings_ushol"><?= Text::_('COM_USERSCHED_CFG_USHOL') ?></label>
		<input type="checkbox" name="settings_bday" id="settings_bday"<?=(isset($this->config['settings_bday'])&&$this->config['settings_bday'])?' checked="checked"':''?> />
		<label for="settings_bday"><?= Text::_('COM_USERSCHED_CFG_BDAY') ?></label>
		</div>
	</div>
</div>
<div class="stabbertab" title="<?=Text::_('COM_USERSCHED_CFG_SCALES')?>">
	<label for="templates_minmin"><?=Text::_('COM_USERSCHED_CFG_MINSTEP')?></label>
	<input type="number" min="5" max="60" name="templates_minmin" id="templates_minmin" class="numer" value="<?=$this->config['templates_minmin']?>" />
	<label for="templates_starthour"><?=Text::_('COM_USERSCHED_CFG_TIMSTART')?></label>
	<input type="number" min="0" max="23" name="templates_starthour" id="templates_starthour" class="numer" value="<?=$this->config['templates_starthour']?>" />
	<label for="templates_endhour"><?=Text::_('COM_USERSCHED_CFG_TIMEND')?></label>
	<input type="number" min="1" max="24" name="templates_endhour" id="templates_endhour" class="numer" value="<?=$this->config['templates_endhour']?>" />
	<label for="templates_agendatime"><?=Text::_('COM_USERSCHED_CFG_TPERIOD')?></label>
	<input type="number" min="1" name="templates_agendatime" id="templates_agendatime" class="numer" value="<?=$this->config['templates_agendatime']?>" />
</div>
<div class="stabbertab" title="<?=Text::_('COM_USERSCHED_CFG_DATEFORM')?>">
	<a href="http://php.net/manual/function.date.php" target="_blank">php.net date format manual</a>
	<label for="templates_defaultdate"><?=Text::_('COM_USERSCHED_CFG_DFLTDATE')?></label>
	<input type="text" name="templates_defaultdate" id="templates_defaultdate" value="<?=$this->config['templates_defaultdate']?>" />
	<label for="templates_monthdate"><?=Text::_('COM_USERSCHED_CFG_MONTHDATE')?></label>
	<input type="text" name="templates_monthdate" id="templates_monthdate" value="<?=$this->config['templates_monthdate']?>" />
	<label for="templates_weekdate"><?=Text::_('COM_USERSCHED_CFG_WEEKDATE')?></label>
	<input type="text" name="templates_weekdate" id="templates_weekdate" value="<?=$this->config['templates_weekdate']?>" />
	<label for="templates_daydate"><?=Text::_('COM_USERSCHED_CFG_DAYDATE')?></label>
	<input type="text" name="templates_daydate" id="templates_daydate" value="<?=$this->config['templates_daydate']?>" />
	<label for="templates_hourdate"><?=Text::_('COM_USERSCHED_CFG_HOURDATE')?></label>
	<input type="text" name="templates_hourdate" id="templates_hourdate" value="<?=$this->config['templates_hourdate']?>" />
	<label for="templates_monthday"><?=Text::_('COM_USERSCHED_CFG_MONTHDAY')?></label>
	<input type="text" name="templates_monthday" id="templates_monthday" value="<?=$this->config['templates_monthday']?>" />
</div>
<div class="stabbertab ectable" title="<?=Text::_('COM_USERSCHED_CFG_CATEGORY')?>">
	<p><span class="col1"><?=Text::_('COM_USERSCHED_CFG_CATNAME')?></span><span class="col2"><?=Text::_('COM_USERSCHED_CFG_TXTCOLOR')?></span><span class="col3"><?=Text::_('COM_USERSCHED_CFG_BGCOLOR')?></span><span class="col4">&nbsp;<?=Text::_('COM_USERSCHED_CFG_DELETE')?></span></p>
	<?php foreach ($this->categories as $cat) :?>
	<p>
		<input type="hidden" name="category_id[]" value="<?=$cat['id']?>" />
		<span class="col1"><input type="text" name="category_name[]" value="<?=$cat['name']?>" class="ecname" /></span>
	<!--	<span class="col2"><input type="text" name="category_txcolor[]" data-cid="t.<?=$cat['id']?>" value="<?=$cat['txcolor']?>" class="minicolors" /></span> 
		<span class="col3"><input type="text" name="category_bgcolor[]" data-cid="b.<?=$cat['id']?>" value="<?=$cat['bgcolor']?>" class="minicolors" /></span> -->
		<span class="col2"><?=HTMLHelper::_('usersched.colorPicker',$cat['id'],'tx',$cat['txcolor'])?></span>
		<span class="col3"><?=HTMLHelper::_('usersched.colorPicker',$cat['id'],'bg',$cat['bgcolor'])?></span>
		<span class="col4"><input type="checkbox" name="category_dele[]" value="<?=$cat['id']?>" class="ecdele" /></span>
		<span class="catsamp" id="catsamp_<?=$cat['id']?>" style="color:<?=$cat['txcolor']?>;background-color:<?=$cat['bgcolor']?>"><?=$cat['name']?></span>
	</p>
	<?php endforeach;?>
	<!-- <div class="clr">&nbsp;</div> -->
	<div class="addicon clr" title="<?=Text::_('COM_USERSCHED_CFG_ADDCAT')?>" onclick="addCategory(this)"></div>
</div>
<?php if ($this->canAlert): ?>
<div class="stabbertab aetable" title="<?=Text::_('COM_USERSCHED_CFG_ALERTEE')?>">
	<p><span class="col1"><?=Text::_('COM_USERSCHED_CFG_ALRTNAME')?></span><span class="col2"><?=Text::_('COM_USERSCHED_CFG_EMAIL')?></span><span class="col3"><?=Text::_('COM_USERSCHED_CFG_SMS')?></span><span class="col4">&nbsp;<?=Text::_('COM_USERSCHED_CFG_DELETE')?></span></p>
	<?php foreach ($this->alertees as $ae) :?>
	<p>
		<input type="hidden" name="alertee_id[]" value="<?=$ae['id']?>" />
		<span class="col1"><input type="text" name="alertee_name[]" value="<?=$ae['name']?>" class="aename" /></span>
		<span class="col2"><input type="text" name="alertee_email[]" value="<?=$ae['email']?>" class="aeemail" /></span>
		<span class="col3"><input type="text" name="alertee_sms[]" value="<?=$ae['sms']?>" class="aesms" /></span>
		<span class="col4"><input type="checkbox" name="alertee_dele[]" value="<?=$ae['id']?>" class="aedele" /></span>
	</p>
	<?php endforeach;?>
	<!-- <div class="clr">&nbsp;</div> -->
	<div class="addicon clr" title="<?=Text::_('COM_USERSCHED_CFG_ADDALRT')?>" onclick="addAlertee(this)" style></div>
</div>
<?php endif; ?>
</div>
<input type="hidden" name="cal_type" value="<?=$this->cal_type?>" />
<input type="hidden" name="task" value="setcfg" />
<input type="hidden" name="<?php echo JSession::getFormToken() ?>" value="1">
</form>
<?php if ($this->show_versions) :?>
<div id="versionbar" class="userschedver">UserSched <span id="userschedver"><?php echo $this->version ?></span></div>
<?php else: ?>
<!-- UserSched <?php echo $this->version ?> -->
<?php endif; ?>

<?php
//	$testabs = ['firstGroupOfFields'=>'firstOne','secondGroupOfFields'=>'secondOne'];
?>
<!-- normal fieldsets -->
<div class="width-60 fltlft stabber" title="GooGoo">
<?php
//	foreach ($testabs as $fgroup => $ftitle) $this->renderFormSection($fgroup,$ftitle);
?>
</div>
