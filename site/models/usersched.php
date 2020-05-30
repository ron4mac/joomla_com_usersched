<?php
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/helpers/usersched.php';

class UserSchedModelUserSched extends JModelLegacy
{
	protected $dbinit = false;

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

	public function __construct ($config = array())
	{
		if (array_key_exists('dbo',$config)) {
			parent::__construct($config);
			return;
		}

		$dbFile = '/sched.sql3';
		$udbPath = UschedHelper::userDataPath();
		if (!file_exists($udbPath.$dbFile)) {
			$this->dbinit = true;
			@mkdir($udbPath, 0777, true);
		}
		
		$db = JDatabaseDriver::getInstance(array('driver'=>'sqlite','database'=>$udbPath.$dbFile));
		$db->setDebug(7);
		$db->connect();
		$db->getConnection()->sqliteCreateFunction('strtotime', 'strtotime', 1);

		$config['dbo'] = $db;
		parent::__construct($config);

		if ($this->dbinit) {
			$this->buildDb($db);
		}
	}

	public function getUdTable ($table, $where=false , $all=true, $values='*')
	{
		$db = $this->getDbo();
		$db->setQuery('SELECT '.$values.' FROM ' . $table . ($where ? (' WHERE '.$where) : ''));
		if ($all) {
			return $db->loadAssocList();
		} else {
			return $db->loadAssoc();
		}
		return null;
	}

	public function getDefaultConfig ()
	{
		return $this->default_config;
	}

	public function getConfig ()
	{
		return $this->default_cfg;
	}

	public function saveConfig ($data)
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
			'lang_tag' => 'en-GB'
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

		foreach ($cbxs as $cbx) {
			if ($data->get($cbx)) $blank[$cbx] = true;
		}
		foreach ($vals as $val) {
			if ($data->get($val)) $blank[$val] = $data->getString($val);
		}
		foreach ($ints as $int) {
			if ($data->get($int)) $blank[$int] = $data->getInt($int);
		}

		$db = $this->getDbo();
		$cfg = $db->quote(serialize($blank));
		if ($this->dbinit) {
			$db->setQuery('INSERT INTO `options` (`name`,`value`) VALUES ("config",'.$cfg.')');
		} else {
			$db->setQuery('UPDATE `options` SET `value` = '.$cfg.' WHERE `name`="config"');
		}
		$db->execute();
		$this->manageAlertees($data,$db);
		$this->manageCategories($data,$db);
	}

	public function importical ()
	{
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

	public function export2ical ()
	{
		require_once JPATH_COMPONENT . '/helpers/ical.php';
		$exporter = new ICalExporter();
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

	private function manageAlertees ($data, $db)
	{
		// delete/update/add alertees
		$aids = $data->get('alertee_id',[],'array');
		if (!count($aids)) return;
		//echo'<xmp>';var_dump($aids);jexit();
		//- for each dele
		foreach ($data->get('alertee_dele',[],'array') as $did) {
			//-- remove from db
			$db->setQuery('DELETE FROM alertees WHERE id = '.$did)->execute();
			//-- remove from id list
			if (($key = array_search($did, $aids)) !== false) { unset($aids[$key]); };
			//echo'<xmp>';var_dump($q,$aids);jexit();
		}
		//- for each id list
		if (count($aids)) {
			$names = $data->get('alertee_name',[],'array');
			$emails = $data->get('alertee_email',[],'array');
			$smss = $data->get('alertee_sms',[],'array');
			foreach ($aids as $k=>$id) {
				$q = $db->getQuery(true);
				if ($id<0) {	//-- add if neg id
					$q->insert('alertees')
						->columns(array('name','email','sms'))
						->values($db->quote($names[$k]).','.$db->quote($emails[$k]).','.$db->quote($smss[$k]));
				} else {	//-- upddate if pos id
					$q->update('alertees')
						->set('name='.$db->quote($names[$k]))
						->set('email='.$db->quote($emails[$k]))
						->set('sms='.$db->quote($smss[$k]))
						->where('id='.$id);
				}
				$db->setQuery($q)->execute();
			}
		}
	}

	private function manageCategories ($data, $db)
	{
		// delete/update/add categories
		$cids = $data->get('category_id',[],'array');
		if (!count($cids)) return;
		//echo'<xmp>';var_dump($cids);jexit();
		//- for each dele
		foreach ($data->get('category_dele',[],'array') as $did) {
			//-- remove from db
			$db->setQuery('DELETE FROM categories WHERE id = '.$did)->execute();
			//-- remove from id list
			if (($key = array_search($did, $cids)) !== false) { unset($cids[$key]); };
			//echo'<xmp>';var_dump($q,$cids);jexit();
		}
		//- for each id list
		if (count($cids)) {
			$names = $data->get('category_name',[],'array');
			$tcolrs = $data->get('category_txcolor',[],'array');
			$bcolrs = $data->get('category_bgcolor',[],'array');
			foreach ($cids as $k=>$id) {
				$q = $db->getQuery(true);
				if ($id<0) {	//-- add if neg id
					$q->insert('categories')
						->columns(array('name','txcolor','bgcolor'))
						->values($db->quote($names[$k]).','.$db->quote($tcolrs[$k]).','.$db->quote($bcolrs[$k]));
				} else {	//-- upddate if pos id
					$q->update('categories')
						->set('name='.$db->quote($names[$k]))
						->set('txcolor='.$db->quote($tcolrs[$k]))
						->set('bgcolor='.$db->quote($bcolrs[$k]))
						->where('id='.$id);
				}
				$db->setQuery($q)->execute();
			}
		}
	}

	private function buildDB ($db, $cfg=false)
	{
		$sql = explode(';',file_get_contents(JPATH_COMPONENT_ADMINISTRATOR.'/models/sched.sql'));
		$db = $this->getDbo();
		foreach ($sql as $x) {
			$db->setQuery($x)->execute();
		}
		if ($cfg) {
			$cfg = $db->quote($cfg);
			$db->setQuery('INSERT INTO options (`name`,`value`) VALUES ("config",'.$cfg.')')->execute();
		}
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