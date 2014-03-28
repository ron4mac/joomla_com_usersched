<?php
defined('_JEXEC') or die;
//echo '<pre>';var_dump($this);echo '</pre>';jexit();
//JHtml::_('behavior.keepalive');
//JHtml::_('behavior.formvalidation');
//JHtml::_('behavior.tooltip');
?>
<form id="configform" method="post">
<div class="dhtmlxLeftDiv">
	<div class="dhtmlxSettingPanel">
		<div class="dhtmlxSettingPanelLabel" id="settings_modes_panel" locale="modes">Modes</div>
		<div class="clr_label">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_day" id="settings_day" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_day']?' checked="checked"':''?>>
		</div>
		<label for="settings_day" class="dhtmlxInputLabel" locale="day" style="float: left; width: 84%;">Day</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_week" id="settings_week" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_week']?' checked="checked"':''?>>
		</div>
		<label for="settings_week" class="dhtmlxInputLabel" locale="week" style="float: left; width: 84%;">Week</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_month" id="settings_month" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_month']?' checked="checked"':''?>>
		</div>
		<label for="settings_month" class="dhtmlxInputLabel" locale="month" style="float: left; width: 84%;">Month</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_agenda" id="settings_agenda" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_agenda']?' checked="checked"':''?>>
		</div>
		<label for="settings_agenda" class="dhtmlxInputLabel" locale="agenda" style="float: left; width: 84%;">Agenda</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_week_agenda" id="settings_week_agenda" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_week_agenda']?' checked="checked"':''?>>
		</div>
		<label for="settings_week_agenda" class="dhtmlxInputLabel" locale="week_agenda" style="float: left; width: 84%;">Week agenda</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_year" id="settings_year" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_year']?' checked="checked"':''?>>
		</div>
		<label for="settings_year" class="dhtmlxInputLabel" locale="year" style="float: left; width: 84%;">Year</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_map" id="settings_map" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_map']?' checked="checked"':''?>>
		</div>
		<label for="settings_map" class="dhtmlxInputLabel" locale="map" style="float: left; width: 84%;">Map</label>
		<div class="clr">&nbsp;</div>
		<?$defaultmode = $this->config['settings_defaultmode'];?>
		<label for="settings_defaultmode" class="dhtmlxInputLabel" locale="defaultmode" style="width: 50%;">Default mode</label>
		<select name="settings_defaultmode" id="settings_defaultmode" class="dhtmlxInputSelect" serialize="true">
			<option value="day" locale="day"<?=$defaultmode=='day'?' selected="selected"':''?>>Day</option>
			<option value="week" locale="week"<?=$defaultmode=='week'?' selected="selected"':''?>>Week</option>
			<option value="month" locale="month"<?=$defaultmode=='month'?' selected="selected"':''?>>Month</option>
			<option value="agenda" locale="agenda"<?=$defaultmode=='agenda'?' selected="selected"':''?>>Agenda</option>
			<option value="week_agenda" locale="week_agenda"<?=$defaultmode=='week_agenda'?' selected="selected"':''?>>Week agenda</option>
			<option value="year" locale="year"<?=$defaultmode=='year'?' selected="selected"':''?>>Year</option>
			<option value="map" locale="map"<?=$defaultmode=='map'?' selected="selected"':''?>>Map</option>
		</select>
		<div class="clr">&nbsp;</div>
	</div>
</div>
<div class="dhtmlxRightDiv">
	<div class="dhtmlxSettingPanel">
		<div class="dhtmlxSettingPanelLabel" locale="global">General settings</div>
		<div class="clr_label">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_repeat" id="settings_repeat" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_repeat']?' checked="checked"':''?>>
		</div>
		<label for="settings_repeat" class="dhtmlxInputLabel" locale="repeat" style="float: left; width: 84%;">Repeat events</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_firstday" id="settings_firstday" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_firstday']?' checked="checked"':''?>>
		</div>
		<label for="settings_firstday" class="dhtmlxInputLabel" locale="firstday" style="float: left; width: 84%;">Sunday is the first day of the week</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_multiday" id="settings_multiday" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_multiday']?' checked="checked"':''?>>
		</div>
		<label for="settings_multiday" class="dhtmlxInputLabel" locale="multiday" style="float: left; width: 84%;">Multiday events in day and week views</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_fullday" id="settings_fullday" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_fullday']?' checked="checked"':''?>>
		</div>
		<label for="settings_fullday" class="dhtmlxInputLabel" locale="fullday" style="float: left; width: 84%;">Full day events</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_marknow" id="settings_marknow" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_marknow']?' checked="checked"':''?>>
		</div>
		<label for="settings_marknow" class="dhtmlxInputLabel" locale="marknow" style="float: left; width: 84%;">Mark now</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_singleclick" id="settings_singleclick" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_singleclick']?' checked="checked"':''?>>
		</div>
		<label for="settings_singleclick" class="dhtmlxInputLabel" locale="singleclick" style="float: left; width: 84%;">Create events by single-click</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_collision" id="settings_collision" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_collision']?' checked="checked"':''?>>
		</div>
		<label for="settings_collision" class="dhtmlxInputLabel" locale="collision" style="float: left; width: 84%;">Prevent events overlapping</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_expand" id="settings_expand" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_expand']?' checked="checked"':''?>>
		</div>
		<label for="settings_expand" class="dhtmlxInputLabel" locale="expand" style="float: left; width: 84%;">Expand button</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_pdf" id="settings_pdf" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_pdf']?' checked="checked"':''?>>
		</div>
		<label for="settings_pdf" class="dhtmlxInputLabel" locale="print" style="float: left; width: 84%;">Print to PDF</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_ical" id="settings_ical" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_ical']?' checked="checked"':''?>>
		</div>
		<label for="settings_ical" class="dhtmlxInputLabel" locale="ical" style="float: left; width: 84%;">Export to iCal</label>
		<div class="clr_ch">&nbsp;</div>
		<div class="dhtmlxInputCheckbox">
			<input type="checkbox" name="settings_minical" id="settings_minical" class="dhtmlxInputCheckbox" serialize="true"<?=$this->config['settings_minical']?' checked="checked"':''?>>
		</div>
		<label for="settings_minical" class="dhtmlxInputLabel" locale="minical" style="float: left; width: 84%;">Mini-calendar navigation</label>
		<div class="clr_ch">&nbsp;</div>
		<label for="settings_eventnumber" class="dhtmlxInputLabel" locale="events_number">Number of events in widget</label><input type="text" name="settings_eventnumber" id="settings_eventnumber" value="" class="dhtmlxInputText" serialize="true">
		<div class="clr">&nbsp;</div>
	</div>
</div>
<div class="dhtmlxLeftDiv" style="height: 66%;">
	<div class="dhtmlxSettingPanel">
		<div class="dhtmlxSettingPanelLabel" locale="scales">Scales</div>
		<div class="clr_label">&nbsp;</div>
		<label for="templates_minmin" class="dhtmlxInputLabel" locale="minmin" style="width: 70%;">Minimal step of event duration (in minutes)</label>
		<input type="text" name="templates_minmin" id="templates_minmin" value="<?=$this->config['templates_minmin']?>" class="dhtmlxInputText" style="width: 20%;" serialize="true">
		<div class="clr">&nbsp;</div>
		<label for="templates_hourheight" class="dhtmlxInputLabel" locale="hourheight" style="width: 70%;">Height of 1 hour in pixels (day view)</label>
		<input type="text" name="templates_hourheight" id="templates_hourheight" value="<?=$this->config['templates_hourheight']?>" class="dhtmlxInputText" style="width: 20%;" serialize="true">
		<div class="clr">&nbsp;</div>
		<label for="templates_starthour" class="dhtmlxInputLabel" locale="starthour" style="width: 70%;">Starting time (in hours) for time scale in day and week views</label>
		<input type="text" name="templates_starthour" id="templates_starthour" value="<?=$this->config['templates_starthour']?>" class="dhtmlxInputText" style="width: 20%;" serialize="true">
		<div class="clr">&nbsp;</div>
		<label for="templates_endhour" class="dhtmlxInputLabel" locale="endhour" style="width: 70%;">Ending time (in hours) for time scale in day and week views</label>
		<input type="text" name="templates_endhour" id="templates_endhour" value="<?=$this->config['templates_endhour']?>" class="dhtmlxInputText" style="width: 20%;" serialize="true">
		<div class="clr">&nbsp;</div>
		<label for="templates_agendatime" class="dhtmlxInputLabel" locale="agendatime" style="width: 70%;">Time period for Agenda and Map views (in days)</label>
		<input type="text" name="templates_agendatime" id="templates_agendatime" value="<?=$this->config['templates_agendatime']?>" class="dhtmlxInputText" style="width: 20%;" serialize="true">
		<div class="clr">&nbsp;</div>
	</div>
</div>
<div class="dhtmlxRightDiv" style="height: 50%;">
	<div class="dhtmlxSettingPanel">
		<div class="dhtmlxSettingPanelLabel" locale="dateformats">Date formats</div>
		<div class="clr_label">&nbsp;</div>
		<label for="templates_defaultdate" class="dhtmlxInputLabel" locale="default_date">Default date</label>
		<input type="text" name="templates_defaultdate" id="templates_defaultdate" value="<?=$this->config['templates_defaultdate']?>" class="dhtmlxInputText" serialize="true">
		<div class="clr">&nbsp;</div>
		<label for="templates_monthdate" class="dhtmlxInputLabel" locale="month_date">Month date</label>
		<input type="text" name="templates_monthdate" id="templates_monthdate" value="<?=$this->config['templates_monthdate']?>" class="dhtmlxInputText" serialize="true">
		<div class="clr">&nbsp;</div>
		<label for="templates_weekdate" class="dhtmlxInputLabel" locale="week_date">Week date</label>
		<input type="text" name="templates_weekdate" id="templates_weekdate" value="<?=$this->config['templates_weekdate']?>" class="dhtmlxInputText" serialize="true">
		<div class="clr">&nbsp;</div>
		<label for="templates_daydate" class="dhtmlxInputLabel" locale="day_date">Day date</label>
		<input type="text" name="templates_daydate" id="templates_daydate" value="<?=$this->config['templates_daydate']?>" class="dhtmlxInputText" serialize="true">
		<div class="clr">&nbsp;</div>
		<label for="templates_hourdate" class="dhtmlxInputLabel" locale="hour_date">Hour date</label>
		<input type="text" name="templates_hourdate" id="templates_hourdate" value="<?=$this->config['templates_hourdate']?>" class="dhtmlxInputText" serialize="true">
		<div class="clr">&nbsp;</div>
		<label for="templates_monthday" class="dhtmlxInputLabel" locale="month_day">Month day</label>
		<input type="text" name="templates_monthday" id="templates_monthday" value="<?=$this->config['templates_monthday']?>" class="dhtmlxInputText" serialize="true">
		<div class="clr">&nbsp;</div>
	</div>
</div>
<input type="hidden" name="cal_type" value="<?=$this->cal_type?>" />
<input type="hidden" name="jID" value="<?=$this->jID?>" />
<input type="hidden" name="task" value="setcfg" />
<input type="hidden" name="<?php echo JSession::getFormToken() ?>" value="1">
<input type="submit" name="saves" value="Save settings" />
</form>
<div class="clr">&nbsp;</div>