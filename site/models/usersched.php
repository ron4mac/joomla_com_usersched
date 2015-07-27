<?php
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/helpers/usersched.php';
jimport('rjuserdata.userdata');

class UserSchedModelUserSched extends JModelLegacy
{
	protected $default_config = array(
	'default_date' => '%j %M %Y',
	'month_date' => '%F %Y',
	'load_date' => '%Y-%m-%d',
	'week_date' => '%l',
	'day_date' => '%D, %F %j',
	'hour_date' => '%H:%i',
	'month_day' => '%d',

	'hour_size_px' => 42,
	'time_step' => 15,

	'start_on_monday' => 1,
	'first_hour' => 0,
	'last_hour' => 24,
	'readonly' => true,
	'drag_resize' => 1,
	'drag_move' => 1,
	'drag_create' => 1,
	'dblclick_create' => 1,
	'edit_on_create' => 1,
	'details_on_create' => 0,
	'click_form_details' => 0,

	'cascade_event_display' => false,
	'cascade_event_count' => 4,
	'cascade_event_margin' => 30,

	'multi_day' => false,
	'multi_day_height_limit' => 0,

	'drag_lightbox' => true,
	'preserve_scroll' => true,
	'select' => true,

	'server_utc' => false,
	'touch' => true,
	'touch_tip' => true,
	'touch_drag' => 500,
	'quick_info_detached' => true,

	'positive_closing' => false,

	'icons_edit' => array('icon_save', 'icon_cancel'),
	'icons_select' => array('icon_details', 'icon_edit', 'icon_delete'),
	'buttons_left' => array('dhx_save_btn', 'dhx_cancel_btn'),
	'buttons_right' => array('dhx_delete_btn'),
	'highlight_displayed_event' => true,
	'displayed_event_color' => '#ffc5ab',
	'displayed_event_text_color' => '#7e2727',

	'left_border' => true,

	'lang_tag' => 'en-GB'
	);

	function getDefaultConfig ()
	{
		return $this->default_config;
	}

	function getConfig ()
	{
		return $this->default_cfg;
	}

	function saveConfig ($data)
	{	//echo'<xmp>';var_dump($data);jexit();
		$blank = array(
			'settings_width' => '',
			'settings_height' => '',
			'settings_eventnumber' => 5,
			'settings_posts' => false,
			'settings_repeat' => false,
			'settings_firstday' => false,
			'settings_multi_day' => false,
			'settings_fullday' => false,
			'settings_marknow' => false,
			'settings_singleclick' => false,
			'settings_day' => false,
			'settings_week' => false,
			'settings_month' => false,
			'settings_agenda' => false,
			'settings_week_agenda' => false,
			'settings_year' => false,
			'settings_map' => false,
			'settings_ushol' => false,
			'settings_bday' => false,
			'settings_defaultmode' => 'month',
			'settings_skin' => '',
			'settings_debug' => false,
			'settings_collision' => false,
			'settings_expand' => false,
			'settings_pdf' => false,
			'settings_ical' => false,
			'settings_minical' => false,
			'templates_defaultdate' => '',
			'templates_monthdate' => '',
			'templates_weekdate' => '',
			'templates_daydate' => '',
			'templates_hourdate' => '',
			'templates_monthday' => '',
			'templates_minmin' => 5,
			'templates_hourheight' => 42,
			'templates_starthour' => 0,
			'templates_endhour' => 24,
			'templates_agendatime' => 30,
			'templates_eventtext' => 'return event.text;',
			'templates_eventheader' => 'return scheduler.templates.hour_scale(start) + " - " + scheduler.templates.hour_scale(end);',
			'templates_eventbartext>' => 'return "<span title=\'"+event.text+"\'>" + event.text + "</span>";',
			'templates_username' => false,
			'alert_lang' => 'en-GB'
			);
		$cbxs = array(
			'settings_posts',
			'settings_repeat',
			'settings_firstday',
			'settings_multi_day',
			'settings_fullday',
			'settings_marknow',
			'settings_singleclick',
			'settings_day',
			'settings_week',
			'settings_month',
			'settings_agenda',
			'settings_week_agenda',
			'settings_year',
			'settings_map',
			'settings_ushol',
			'settings_bday',
			'settings_debug',
			'settings_collision',
			'settings_expand',
			'settings_pdf',
			'settings_ical',
			'settings_minical',
			'templates_username'
			);
		$vals = array(
			'settings_width',
			'settings_height',
			'settings_defaultmode',
			'settings_skin',
			'templates_defaultdate',
			'templates_monthdate',
			'templates_weekdate',
			'templates_daydate',
			'templates_hourdate',
			'templates_monthday',
			'templates_eventtext',
			'templates_eventheader',
			'templates_eventbartext>'
			);
		$ints = array(
			'settings_eventnumber',
			'templates_minmin',
			'templates_hourheight',
			'templates_starthour',
			'templates_endhour',
			'templates_agendatime'
			);
//		$params = $this->state->get('parameters.menu');
		foreach ($cbxs as $cbx) {
			if ($data->get($cbx)) $blank[$cbx] = true;
		}
		foreach ($vals as $val) {
			if ($data->get($val)) $blank[$val] = $data->getString($val);
		}
		foreach ($ints as $int) {
			if ($data->get($int)) $blank[$int] = $data->getInt($int);
		}
		//echo'<xmp>';var_dump($params,$blank);jexit();
		$calid = UserSchedHelper::uState('calid');
		$db = $this->getUserDatabase($calid);
		if ($db->dataExists()) {
			$db->getDbase()->db_connect();
			$q = $db->getDbase()->_update('options',array('value'=>"'".$db->getDbase()->escape_str(serialize($blank))."'"),array('name = "config"'));
			//echo $q; jexit();
			$r = $db->getDbase()->execute($q);
		} else {
			$this->buildDB($db, serialize($blank));
		}

		$this->manageAlertees($data,$db);
		$this->manageCategories($data,$db);
	}

	function importical ()
	{
//		$params = $this->state->get('parameters.menu');
		$calid = UserSchedHelper::uState('calid');
		$db = $this->getUserDatabase($calid);
		if (!$db->dataExists()) return false;
		$db->getDbase()->db_connect();	// cause it to open read/write
		if ($icalfile = JFactory::getApplication()->input->files->get('ical_file', null)) {
			if ($icalfile['tmp_name']) {
				$icaldata = file_get_contents($icalfile['tmp_name']);
				$this->importIcalendar($icaldata, $db);
			} else return false;
		} else return false;
		return true;
	}

	function export2ical ()
	{
		require_once JPATH_COMPONENT . '/helpers/ical.php';
		$exporter = new ICalExporter();
//		$params = $this->state->get('parameters.menu');
		$calid = UserSchedHelper::uState('calid');
		$db = $this->getUserDatabase($calid);
		if ($db->dataExists()) {
			$evts = $db->getTable('events','',true);
			$ical = $exporter->toICal($evts);
			header('Content-type: text/calendar');
			header('Content-Disposition: attachment; filename="schedule.ics"');
			echo $ical;
		}
	}

	private function getUserDatabase ($calid)
	{
		list($calType,$ids) = explode(':',$calid);
		$db = null;
		switch ($calType) {
			case 0:
				$db = new RJUserData('sched');
				break;
			case 1:
				$db = new RJUserData('sched',false,$ids,true);
				break;
			case 2:
				$db = new RJUserData('sched',false,0,true);
				break;
		}
		return $db;
	}

	private function manageAlertees ($data, $db)
	{
		// delete/update/add alertees
		$aids = $data->get('alertee_id',null,'array');
		if (!count($aids)) return;
		//echo'<xmp>';var_dump($aids);jexit();
		//- for each dele
		foreach ($data->get('alertee_dele',null,'array') as $did) {
			//-- remove from db
			$q = 'DELETE FROM alertees WHERE id = '.$did;
			$r = $db->getDbase()->execute($q);
			//-- remove from id list
			if (($key = array_search($did, $aids)) !== false) { unset($aids[$key]); };
			//echo'<xmp>';var_dump($q,$aids);jexit();
		}
		//- for each id list
		if (count($aids)) {
			$names = $data->get('alertee_name',null,'array');
			$emails = $data->get('alertee_email',null,'array');
			$smss = $data->get('alertee_sms',null,'array');
			foreach ($aids as $k=>$id) {
				if ($id<0) {	//-- add if neg id
					$q = $db->getDbase()->_insert('alertees',array('name','email','sms'),array("'{$names[$k]}'","'{$emails[$k]}'","'{$smss[$k]}'"));
					$r = $db->getDbase()->execute($q);
				} else {	//-- upddate if pos id
					$q = $db->getDbase()->_update('alertees',array('name'=>"'{$names[$k]}'",'email'=>"'{$emails[$k]}'",'sms'=>"'{$smss[$k]}'"),array('id = '.$id));
					$r = $db->getDbase()->execute($q);
				}
			}
		}
	}

	private function manageCategories ($data, $db)
	{
		// delete/update/add categories
		$cids = $data->get('category_id',null,'array');
		if (!count($cids)) return;
		//echo'<xmp>';var_dump($cids);jexit();
		//- for each dele
		foreach ($data->get('category_dele',null,'array') as $did) {
			//-- remove from db
			$q = 'DELETE FROM categories WHERE id = '.$did;
			$r = $db->getDbase()->execute($q);
			//-- remove from id list
			if (($key = array_search($did, $cids)) !== false) { unset($cids[$key]); };
			//echo'<xmp>';var_dump($q,$cids);jexit();
		}
		//- for each id list
		if (count($cids)) {
			$names = $data->get('category_name',null,'array');
			$tcolrs = $data->get('category_txcolor',null,'array');
			$bcolrs = $data->get('category_bgcolor',null,'array');
			foreach ($cids as $k=>$id) {
				if ($id<0) {	//-- add if neg id
					$q = $db->getDbase()->_insert('categories',array('name','txcolor','bgcolor'),array("'{$names[$k]}'","'{$tcolrs[$k]}'","'{$bcolrs[$k]}'"));
					$r = $db->getDbase()->execute($q);
				} else {	//-- upddate if pos id
					$q = $db->getDbase()->_update('categories',array('name'=>"'{$names[$k]}'",'txcolor'=>"'{$tcolrs[$k]}'",'bgcolor'=>"'{$bcolrs[$k]}'"),array('id = '.$id));
					$r = $db->getDbase()->execute($q);
				}
			}
		}
	}

	private function buildDB ($db, $cfg)
	{
		$sql = 'CREATE TABLE `events` (
				`event_id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
				`start_date` datetime NOT NULL,
				`end_date` datetime NOT NULL,
				`text` varchar(255) NOT NULL,
				`category` int(11),
				`rec_type` varchar(64) NOT NULL,
				`event_pid` int(11) NOT NULL,
				`event_length` int(11) NOT NULL,
				`user` int(11) NOT NULL,
				`lat` float(10,6) DEFAULT 0,
				`lng` float(10,6) DEFAULT 0,
				`alert_lead` INTEGER,
				`alert_user` BLOB,
				`alert_meth` TEXT)'
				.'; CREATE TABLE `options` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, `name` varchar(255) NOT NULL, `value` text NOT NULL )'
				.'; CREATE TABLE `categories` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, `name` varchar(255) NOT NULL, `txcolor` varchar(15), `bgcolor` varchar(15) )'
				.'; CREATE TABLE `alertees` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, `name` TEXT NOT NULL, `email` TEXT, `sms` TEXT )'
				.'; CREATE TABLE `alerted` ( `eid` INTEGER NOT NULL, `atime` INTEGER NOT NULL, `lead` INTEGER NOT NULL )';
		$db->createDatabase($sql);
		$cfg = $db->getDbase()->escape_str($cfg);
		$q = $db->getDbase()->_insert('options',array('name','value'),array('\'config\'',"'$cfg'"));
		//echo $q; jexit();
		$r = $db->getDbase()->execute($q);
	}

	private function importIcalendar ($data, $db)
	{
		require_once JPATH_COMPONENT . '/helpers/ical.php';
		if (!db || !data) return;
		$exporter = new ICalExporter();
		$events = $exporter->toHash($data);
		//echo'<pre>';var_dump($events);echo'</pre>';jexit();
		$db3 = $db->getDbase();
		foreach ($events as $evt) {
			$this->addEvent($evt, $db3);
		}
	}

	private function addEvent ($evt, $db)
	{
		//echo'<pre>';var_dump($evt);echo'</pre>';return;
		foreach ($evt as $k=>$v) {
			switch (gettype($v)) {
				case 'string':
					$evt[$k] = '\''.$db->escape_str($v).'\'';
					break;
				default:
					if ($k == 'event_id') unset($evt[$k]);
					//$evt[$k] = $db->escape_str($v);
					break;
			}
		}
		if (!isset($evt['user'])) $evt['user'] = JFactory::getUser()->id;
		$q = $db->_insert('events', array_keys($evt), array_values($evt));
		//echo'<pre>';var_dump($q);echo'</pre>';
		$db->execute($q);
	}
}