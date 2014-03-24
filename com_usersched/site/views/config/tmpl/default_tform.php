<?php
defined('_JEXEC') or die;
?>
<div id="ical-inout">
	<a href="index.php?option=com_usersched&view=ical">Import/Export iCalendar events</a>
	<!-- <a href="index.php?option=com_usersched&view=ical&task=in">Import events from ICAL</a> -->
	<!-- <a href="index.php?option=com_usersched&view=ical&task=out">Export events to ICAL</a> -->
	<!-- <a href="<?php JRoute::_('index.php?view=ical', false); ?>">Import events from ICAL</a> -->
	<!-- <a href="<?php JRoute::_('index.php?view=ical', false); ?>">Export events to ICAL</a> -->
</div>
<form id="configform" method="post" enctype="multipart/form-data">
<input type="submit" name="saves" value="Save settings" onclick="this.form.task.value='setcfg'" />
<div id="formtabs" class="stabber">
<div class="stabbertab" title="General">
	<input type="checkbox" name="settings_repeat" id="settings_repeat"<?=$this->config['settings_repeat']?' checked="checked"':''?> />
	<label for="settings_repeat">Repeat events</label>
	<input type="checkbox" name="settings_firstday" id="settings_firstday"<?=$this->config['settings_firstday']?' checked="checked"':''?> />
	<label for="settings_firstday">Sunday is the first day of the week</label>
	<input type="checkbox" name="settings_multiday" id="settings_multiday"<?=$this->config['settings_multiday']?' checked="checked"':''?> />
	<label for="settings_multiday">Multiday events in day and week views</label>
	<input type="checkbox" name="settings_fullday" id="settings_fullday"<?=$this->config['settings_fullday']?' checked="checked"':''?> />
	<label for="settings_fullday">Full day events</label>
	<input type="checkbox" name="settings_marknow" id="settings_marknow"<?=$this->config['settings_marknow']?' checked="checked"':''?> />
	<label for="settings_marknow">Mark now</label>
	<input type="checkbox" name="settings_singleclick" id="settings_singleclick"<?=$this->config['settings_singleclick']?' checked="checked"':''?> />
	<label for="settings_singleclick">Create events by single-click</label>
	<input type="checkbox" name="settings_collision" id="settings_collision"<?=$this->config['settings_collision']?' checked="checked"':''?> />
	<label for="settings_collision">Prevent events overlapping</label>
	<input type="checkbox" name="settings_expand" id="settings_expand"<?=$this->config['settings_expand']?' checked="checked"':''?> />
	<label for="settings_expand">Expand button</label>
	<input type="checkbox" name="settings_pdf" id="settings_pdf"<?=$this->config['settings_pdf']?' checked="checked"':''?> />
	<label for="settings_pdf">Print to PDF</label>
	<input type="checkbox" name="settings_ical" id="settings_ical"<?=$this->config['settings_ical']?' checked="checked"':''?> />
	<label for="settings_ical">Export to iCal</label>
	<input type="checkbox" name="settings_minical" id="settings_minical"<?=$this->config['settings_minical']?' checked="checked"':''?> />
	<label for="settings_minical">Mini-calendar navigation</label>
	<label for="settings_eventnumber">Number of events in widget</label>
	<input type="text" name="settings_eventnumber" id="settings_eventnumber" value="" />
</div>
<div class="stabbertab" title="Modes">
	<input type="checkbox" name="settings_day" id="settings_day"<?=$this->config['settings_day']?' checked="checked"':''?> />
	<label for="settings_day">Day</label>
	<input type="checkbox" name="settings_week" id="settings_week"<?=$this->config['settings_week']?' checked="checked"':''?> />
	<label for="settings_week">Week</label>
	<input type="checkbox" name="settings_month" id="settings_month"<?=$this->config['settings_month']?' checked="checked"':''?> />
	<label for="settings_month">Month</label>
	<input type="checkbox" name="settings_agenda" id="settings_agenda"<?=$this->config['settings_agenda']?' checked="checked"':''?> />
	<label for="settings_agenda">Agenda</label>
	<input type="checkbox" name="settings_week_agenda" id="settings_week_agenda"<?=$this->config['settings_week_agenda']?' checked="checked"':''?> />
	<label for="settings_week_agenda">Week agenda</label>
	<input type="checkbox" name="settings_year" id="settings_year"<?=$this->config['settings_year']?' checked="checked"':''?> />
	<label for="settings_year">Year</label>
	<input type="checkbox" name="settings_map" id="settings_map"<?=$this->config['settings_map']?' checked="checked"':''?> />
	<label for="settings_map">Map</label>
	<div class="clr">&nbsp;</div>
	<?$defaultmode = $this->config['settings_defaultmode'];?>
	<label for="settings_defaultmode">Default mode</label>
	<select name="settings_defaultmode" id="settings_defaultmode">
		<option value="day" locale="day"<?=$defaultmode=='day'?' selected="selected"':''?>>Day</option>
		<option value="week" locale="week"<?=$defaultmode=='week'?' selected="selected"':''?>>Week</option>
		<option value="month" locale="month"<?=$defaultmode=='month'?' selected="selected"':''?>>Month</option>
		<option value="agenda" locale="agenda"<?=$defaultmode=='agenda'?' selected="selected"':''?>>Agenda</option>
		<option value="week_agenda" locale="week_agenda"<?=$defaultmode=='week_agenda'?' selected="selected"':''?>>Week agenda</option>
		<option value="year" locale="year"<?=$defaultmode=='year'?' selected="selected"':''?>>Year</option>
		<option value="map" locale="map"<?=$defaultmode=='map'?' selected="selected"':''?>>Map</option>
	</select>
	<div class="clr">&nbsp;</div>
	<label for="settings_skin">Calendar 'skin'</label>
	<?=JHtml::_('select.genericlist', $this->skinOptions, 'settings_skin', '', 'value', 'text', $this->config['settings_skin'], 'settings_skin'); ?>
</div>
<div class="stabbertab" title="Scales">
	<label for="templates_minmin">Minimal step of event duration (in minutes)</label>
	<input type="text" name="templates_minmin" id="templates_minmin" class="numer" value="<?=$this->config['templates_minmin']?>" />
	<label for="templates_hourheight">Height of 1 hour in pixels (day view)</label>
	<input type="text" name="templates_hourheight" id="templates_hourheight" class="numer" value="<?=$this->config['templates_hourheight']?>" />
	<label for="templates_starthour">Starting time (in hours) for time scale in day and week views</label>
	<input type="text" name="templates_starthour" id="templates_starthour" class="numer" value="<?=$this->config['templates_starthour']?>" />
	<label for="templates_endhour">Ending time (in hours) for time scale in day and week views</label>
	<input type="text" name="templates_endhour" id="templates_endhour" class="numer" value="<?=$this->config['templates_endhour']?>" />
	<label for="templates_agendatime">Time period for Agenda and Map views (in days)</label>
	<input type="text" name="templates_agendatime" id="templates_agendatime" class="numer" value="<?=$this->config['templates_agendatime']?>" />
</div>
<div class="stabbertab" title="Date formats">
	<label for="templates_defaultdate">Default date</label>
	<input type="text" name="templates_defaultdate" id="templates_defaultdate" value="<?=$this->config['templates_defaultdate']?>" />
	<label for="templates_monthdate">Month date</label>
	<input type="text" name="templates_monthdate" id="templates_monthdate" value="<?=$this->config['templates_monthdate']?>" />
	<label for="templates_weekdate">Week date</label>
	<input type="text" name="templates_weekdate" id="templates_weekdate" value="<?=$this->config['templates_weekdate']?>" />
	<label for="templates_daydate">Day date</label>
	<input type="text" name="templates_daydate" id="templates_daydate" value="<?=$this->config['templates_daydate']?>" />
	<label for="templates_hourdate">Hour date</label>
	<input type="text" name="templates_hourdate" id="templates_hourdate" value="<?=$this->config['templates_hourdate']?>" />
	<label for="templates_monthday">Month day</label>
	<input type="text" name="templates_monthday" id="templates_monthday" value="<?=$this->config['templates_monthday']?>" />
</div>
<div class="stabbertab ectable" title="Categories">
	<p><span class="col1">Name</span><span class="col2">Text color</span><span class="col3">Background</span><span class="col4">&nbsp;Delete!</span></p>
	<?php foreach ($this->categories as $cat) :?>
	<p>
		<input type="hidden" name="category_id[]" value="<?=$cat[id]?>" />
		<span class="col1"><input type="text" name="category_name[]" value="<?=$cat[name]?>" class="ecname" /></span>
		<span class="col2"><input type="text" name="category_txcolor[]" value="<?=$cat[txcolor]?>" class="ectcolr" /></span>
		<span class="col3"><input type="text" name="category_bgcolor[]" value="<?=$cat[bgcolor]?>" class="ecbcolr" /></span>
		<span class="col4"><input type="checkbox" name="category_dele[]" value="<?=$cat[id]?>" class="ecdele" /></span>
		<span class="catsamp" style="color:<?=$cat[txcolor]?>;background-color:<?=$cat[bgcolor]?>"><?=$cat[name]?></span>
	</p>
	<?php endforeach;?>
	<!-- <div class="clr">&nbsp;</div> -->
	<div class="addicon clr" title="Add a category" onclick="addCategory(this)"></div>
</div>
<div class="stabbertab aetable" title="Alertees">
	<p><span class="col1">Name</span><span class="col2">Email</span><span class="col3">SMS (<a href="http://www.emailtextmessages.com/" target="_blank">gateways</a>)</span><span class="col4">&nbsp;Delete!</span></p>
	<!-- <a href="http://wikipedia.org/wiki/List_of_SMS_gateways" target="_blank" style="float:right">SMS gateways</a> -->
	<?php foreach ($this->alertees as $ae) :?>
	<p>
		<input type="hidden" name="alertee_id[]" value="<?=$ae[id]?>" />
		<span class="col1"><input type="text" name="alertee_name[]" value="<?=$ae[name]?>" class="aename" /></span>
		<span class="col2"><input type="text" name="alertee_email[]" value="<?=$ae[email]?>" class="aeemail" /></span>
		<span class="col3"><input type="text" name="alertee_sms[]" value="<?=$ae[sms]?>" class="aesms" /></span>
		<span class="col4"><input type="checkbox" name="alertee_dele[]" value="<?=$ae[id]?>" class="aedele" /></span>
	</p>
	<?php endforeach;?>
	<!-- <div class="clr">&nbsp;</div> -->
	<div class="addicon clr" title="Add an alertee" onclick="addAlertee(this)" style></div>
</div>
</div>
<input type="hidden" name="cal_type" value="<?=$this->cal_type?>" />
<input type="hidden" name="jID" value="<?=$this->jID?>" />
<input type="hidden" name="task" value="setcfg" />
<input type="hidden" name="<?php echo JSession::getFormToken() ?>" value="1">
</form>
<div class="clr">&nbsp;</div>