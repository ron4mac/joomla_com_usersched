<?php
defined('_JEXEC') or die;
?>
<form id="configform" method="post" enctype="multipart/form-data" style="margin:0">
<input type="submit" name="saves" value="<?=JText::_('COM_USERSCHED_CFG_SAVE')?>" onclick="this.form.task.value='setcfg'" />
<div id="formtabs" class="stabber">
<div class="stabbertab" title="<?=JText::_('COM_USERSCHED_CFG_GENERAL')?>">
	<input type="checkbox" name="settings_repeat" id="settings_repeat"<?=$this->config['settings_repeat']?' checked="checked"':''?> />
	<label for="settings_repeat"><?= JText::_('COM_USERSCHED_CFG_RECURR') ?></label>
	<input type="checkbox" name="settings_firstday" id="settings_firstday"<?=$this->config['settings_firstday']?' checked="checked"':''?> />
	<label for="settings_firstday"><?= JText::_('COM_USERSCHED_CFG_WKSTART') ?></label>
	<input type="checkbox" name="settings_multi_day" id="settings_multi_day"<?=$this->config['settings_multi_day']?' checked="checked"':''?> />
	<label for="settings_multi_day"><?= JText::_('COM_USERSCHED_CFG_MULTIDAY') ?></label>
	<input type="checkbox" name="settings_fullday" id="settings_fullday"<?=$this->config['settings_fullday']?' checked="checked"':''?> />
	<label for="settings_fullday"><?= JText::_('COM_USERSCHED_CFG_FULLDAY') ?></label>
<?php if (false): ?>
	<input type="checkbox" name="settings_marknow" id="settings_marknow"<?=$this->config['settings_marknow']?' checked="checked"':''?> />
	<label for="settings_marknow">Mark now</label>
	<input type="checkbox" name="settings_singleclick" id="settings_singleclick"<?=$this->config['settings_singleclick']?' checked="checked"':''?> />
	<label for="settings_singleclick">Create events by single-click</label>
<?php endif; ?>
	<input type="checkbox" name="settings_collision" id="settings_collision"<?=$this->config['settings_collision']?' checked="checked"':''?> />
	<label for="settings_collision"><?= JText::_('COM_USERSCHED_CFG_OVERLAP') ?></label>
	<input type="checkbox" name="settings_expand" id="settings_expand"<?=$this->config['settings_expand']?' checked="checked"':''?> />
	<label for="settings_expand">Expand button</label>
<?php if (false): ?>
	<input type="checkbox" name="settings_pdf" id="settings_pdf"<?=$this->config['settings_pdf']?' checked="checked"':''?> />
	<label for="settings_pdf">Print to PDF</label>
	<input type="checkbox" name="settings_ical" id="settings_ical"<?=$this->config['settings_ical']?' checked="checked"':''?> />
	<label for="settings_ical">Export to iCal</label>
<?php endif; ?>
	<input type="checkbox" name="settings_minical" id="settings_minical"<?=$this->config['settings_minical']?' checked="checked"':''?> />
	<label for="settings_minical"><?= JText::_('COM_USERSCHED_CFG_MINICAL') ?></label>
	<label for="settings_eventnumber"><?=JText::_('COM_USERSCHED_CFG_MAXEVENT')?></label>
	<input type="number" name="settings_eventnumber" id="settings_eventnumber" class="numer" value="<?= $this->config['settings_eventnumber'] ?>" />
</div>
<div class="stabbertab" title="<?=JText::_('COM_USERSCHED_CFG_MODES')?>">
	<input type="checkbox" name="settings_day" id="settings_day"<?=$this->config['settings_day']?' checked="checked"':''?> />
	<label for="settings_day"><?= JText::_('COM_USERSCHED_CFG_DAY') ?></label>
	<input type="checkbox" name="settings_week" id="settings_week"<?=$this->config['settings_week']?' checked="checked"':''?> />
	<label for="settings_week"><?= JText::_('COM_USERSCHED_CFG_WEEK') ?></label>
	<input type="checkbox" name="settings_month" id="settings_month"<?=$this->config['settings_month']?' checked="checked"':''?> />
	<label for="settings_month"><?= JText::_('COM_USERSCHED_CFG_MONTH') ?></label>
	<input type="checkbox" name="settings_year" id="settings_year"<?=$this->config['settings_year']?' checked="checked"':''?> />
	<label for="settings_year"><?= JText::_('COM_USERSCHED_CFG_YEAR') ?></label>
	<input type="checkbox" name="settings_agenda" id="settings_agenda"<?=$this->config['settings_agenda']?' checked="checked"':''?> />
	<label for="settings_agenda"><?= JText::_('COM_USERSCHED_CFG_AGENDA') ?></label>
<?php if (false): ?>
	<input type="checkbox" name="settings_week_agenda" id="settings_week_agenda"<?=$this->config['settings_week_agenda']?' checked="checked"':''?> />
	<label for="settings_week_agenda"><?= JText::_('COM_USERSCHED_CFG_WAGENDA') ?></label>
	<input type="checkbox" name="settings_map" id="settings_map"<?=$this->config['settings_map']?' checked="checked"':''?> />
	<label for="settings_map"><?= JText::_('COM_USERSCHED_CFG_MAP') ?></label>
<?php endif; ?>
	<div class="clr">&nbsp;</div>
	<?$defaultmode = $this->config['settings_defaultmode'];?>
	<label for="settings_defaultmode"><?= JText::_('COM_USERSCHED_CFG_DFLTMODE') ?></label>
	<select name="settings_defaultmode" id="settings_defaultmode">
		<option value="day" locale="day"<?=$defaultmode=='day'?' selected="selected"':''?>><?= JText::_('COM_USERSCHED_CFG_DAY') ?></option>
		<option value="week" locale="week"<?=$defaultmode=='week'?' selected="selected"':''?>><?= JText::_('COM_USERSCHED_CFG_WEEK') ?></option>
		<option value="month" locale="month"<?=$defaultmode=='month'?' selected="selected"':''?>><?= JText::_('COM_USERSCHED_CFG_MONTH') ?></option>
		<option value="year" locale="year"<?=$defaultmode=='year'?' selected="selected"':''?>><?= JText::_('COM_USERSCHED_CFG_YEAR') ?></option>
		<option value="agenda" locale="agenda"<?=$defaultmode=='agenda'?' selected="selected"':''?>><?= JText::_('COM_USERSCHED_CFG_AGENDA') ?></option>
		<option value="week_agenda" locale="week_agenda"<?=$defaultmode=='week_agenda'?' selected="selected"':''?>><?= JText::_('COM_USERSCHED_CFG_WAGENDA') ?></option>
	<!--	<option value="map" locale="map"<?=$defaultmode=='map'?' selected="selected"':''?>><?= JText::_('COM_USERSCHED_CFG_MAP') ?></option> -->
	</select>
<?php if ($this->canSkin): ?>
	<div class="clr">&nbsp;</div>
	<label for="settings_skin"><?= JText::_('COM_USERSCHED_CFG_CALSKIN') ?></label>
	<?=JHtml::_('select.genericlist', $this->skinOptions, 'settings_skin', '', 'value', 'text', $this->config['settings_skin'], 'settings_skin'); ?>
<?php endif; ?>
</div>
<div class="stabbertab" title="<?=JText::_('COM_USERSCHED_CFG_SCALES')?>">
	<label for="templates_minmin"><?=JText::_('COM_USERSCHED_CFG_MINSTEP')?></label>
	<input type="number" min="5" max="60" name="templates_minmin" id="templates_minmin" class="numer" value="<?=$this->config['templates_minmin']?>" />
	<label for="templates_hourheight"><?=JText::_('COM_USERSCHED_CFG_HIHOUR')?></label>
	<input type="number" min="20" name="templates_hourheight" id="templates_hourheight" class="numer" value="<?=$this->config['templates_hourheight']?>" />
	<label for="templates_starthour"><?=JText::_('COM_USERSCHED_CFG_TIMSTART')?></label>
	<input type="number" min="0" max="23" name="templates_starthour" id="templates_starthour" class="numer" value="<?=$this->config['templates_starthour']?>" />
	<label for="templates_endhour"><?=JText::_('COM_USERSCHED_CFG_TIMEND')?></label>
	<input type="number" min="1" max="24" name="templates_endhour" id="templates_endhour" class="numer" value="<?=$this->config['templates_endhour']?>" />
	<label for="templates_agendatime"><?=JText::_('COM_USERSCHED_CFG_TPERIOD')?></label>
	<input type="number" min="1" name="templates_agendatime" id="templates_agendatime" class="numer" value="<?=$this->config['templates_agendatime']?>" />
</div>
<div class="stabbertab" title="<?=JText::_('COM_USERSCHED_CFG_DATEFORM')?>">
	<a href="http://php.net/manual/function.date.php" target="_blank">php.net date format manual</a>
	<label for="templates_defaultdate"><?=JText::_('COM_USERSCHED_CFG_DFLTDATE')?></label>
	<input type="text" name="templates_defaultdate" id="templates_defaultdate" value="<?=$this->config['templates_defaultdate']?>" />
	<label for="templates_monthdate"><?=JText::_('COM_USERSCHED_CFG_MONTHDATE')?></label>
	<input type="text" name="templates_monthdate" id="templates_monthdate" value="<?=$this->config['templates_monthdate']?>" />
	<label for="templates_weekdate"><?=JText::_('COM_USERSCHED_CFG_WEEKDATE')?></label>
	<input type="text" name="templates_weekdate" id="templates_weekdate" value="<?=$this->config['templates_weekdate']?>" />
	<label for="templates_daydate"><?=JText::_('COM_USERSCHED_CFG_DAYDATE')?></label>
	<input type="text" name="templates_daydate" id="templates_daydate" value="<?=$this->config['templates_daydate']?>" />
	<label for="templates_hourdate"><?=JText::_('COM_USERSCHED_CFG_HOURDATE')?></label>
	<input type="text" name="templates_hourdate" id="templates_hourdate" value="<?=$this->config['templates_hourdate']?>" />
	<label for="templates_monthday"><?=JText::_('COM_USERSCHED_CFG_MONTHDAY')?></label>
	<input type="text" name="templates_monthday" id="templates_monthday" value="<?=$this->config['templates_monthday']?>" />
</div>
<div class="stabbertab ectable" title="<?=JText::_('COM_USERSCHED_CFG_CATEGORY')?>">
	<p><span class="col1"><?=JText::_('COM_USERSCHED_CFG_CATNAME')?></span><span class="col2"><?=JText::_('COM_USERSCHED_CFG_TXTCOLOR')?></span><span class="col3"><?=JText::_('COM_USERSCHED_CFG_BGCOLOR')?></span><span class="col4">&nbsp;<?=JText::_('COM_USERSCHED_CFG_DELETE')?></span></p>
	<?php foreach ($this->categories as $cat) :?>
	<p>
		<input type="hidden" name="category_id[]" value="<?=$cat['id']?>" />
		<span class="col1"><input type="text" name="category_name[]" value="<?=$cat['name']?>" class="ecname" /></span>
		<span class="col2"><input type="text" name="category_txcolor[]" value="<?=$cat['txcolor']?>" class="ectcolr" /></span>
		<span class="col3"><input type="text" name="category_bgcolor[]" value="<?=$cat['bgcolor']?>" class="ecbcolr" /></span>
		<span class="col4"><input type="checkbox" name="category_dele[]" value="<?=$cat['id']?>" class="ecdele" /></span>
		<span class="catsamp" style="color:<?=$cat['txcolor']?>;background-color:<?=$cat['bgcolor']?>"><?=$cat['name']?></span>
	</p>
	<?php endforeach;?>
	<!-- <div class="clr">&nbsp;</div> -->
	<div class="addicon clr" title="<?=JText::_('COM_USERSCHED_CFG_ADDCAT')?>" onclick="addCategory(this)"></div>
</div>
<?php if ($this->canAlert): ?>
<div class="stabbertab aetable" title="<?=JText::_('COM_USERSCHED_CFG_ALERTEE')?>">
	<p><span class="col1"><?=JText::_('COM_USERSCHED_CFG_ALRTNAME')?></span><span class="col2"><?=JText::_('COM_USERSCHED_CFG_EMAIL')?></span><span class="col3"><?=JText::_('COM_USERSCHED_CFG_SMS')?></span><span class="col4">&nbsp;<?=JText::_('COM_USERSCHED_CFG_DELETE')?></span></p>
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
	<div class="addicon clr" title="<?=JText::_('COM_USERSCHED_CFG_ADDALRT')?>" onclick="addAlertee(this)" style></div>
</div>
<?php endif; ?>
</div>
<input type="hidden" name="cal_type" value="<?=$this->cal_type?>" />
<input type="hidden" name="task" value="setcfg" />
<input type="hidden" name="<?php echo JSession::getFormToken() ?>" value="1">
</form>
<?php if ($this->show_versions) :?>
<div id="versionbar" class="userschedver">UserSched <span id="userschedver"><?php echo $this->version ?></span></div>
<?php endif; ?>
