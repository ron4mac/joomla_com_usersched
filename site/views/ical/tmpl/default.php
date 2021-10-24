<?php 
defined('_JEXEC') or die('Restricted access');
$data = $this->data;	//echo'<pre>';var_dump($data); //jexit();
$this->document->addStyleSheet('components/com_usersched/static/config.css');
$this->document->addScript('components/com_usersched/static/config.js',true);
if ($this->params->get('show_page_heading', 1)) {
	echo '<div class="page-header"><h3>'.$this->escape($this->params->get('page_heading')).'</h3></div>';
}
?>
<form id="configform" method="post" enctype="multipart/form-data" style="margin:0">
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
<input type="hidden" name="task" value="" />
<input type="hidden" name="<?php echo JSession::getFormToken() ?>" value="1" />
</form>
<?php if ($this->show_versions) :?>
<div id="versionbar" class="userschedver">UserSched <span id="userschedver"><?php echo $this->version ?></span></div>
<?php endif; ?>
