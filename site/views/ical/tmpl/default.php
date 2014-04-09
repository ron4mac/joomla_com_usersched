<?php 
defined('_JEXEC') or die('Restricted access');
$data = $this->data;	//echo'<pre>';var_dump($data); //jexit();
JHtml::script('components/com_usersched/static/config.js',true);
JHtml::stylesheet('components/com_usersched/static/config.css');
?>
<form id="configform" method="post" enctype="multipart/form-data">
<div class="stabbertab" title="iCalendar">
	<fieldset>
		<legend>Import from iCalender</legend>
		<span>iCalendar file: </span>
		<input type="file" class="file-input" id="ical-input" accept="text/calendar,.ics" name="ical_file" />
		<br /><input type="checkbox" name="del_current" id="del_current" />
		<label for="del_current">Delete all current calendar events</label>
		<input type="submit" name="impical" value="Import" onclick="this.form.task.value='impical'" />
	</fieldset>
	<fieldset>
		<legend>Export to iCalender</legend>
		<input type="submit" name="ex2ical" value="Export" onclick="this.form.task.value='exp2ical'" />
	</fieldset>
</div>
<input type="hidden" name="cal_type" value="<?=$this->cal_type?>" />
<input type="hidden" name="jID" value="<?=$this->jID?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="<?php echo JSession::getFormToken() ?>" value="1" />
</form>
