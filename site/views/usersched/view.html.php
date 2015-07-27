<?php
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/helpers/usersched.php';
require_once JPATH_COMPONENT.'/views/uschedview.php';

class UserschedViewUsersched extends UserschedView
{
	protected $config = array(
	'default_date' => '%j %M %Y',
	'month_date' => '%F %Y',
	'load_date' => '%Y-%m-%d',
	'week_date' => '%l',
	'day_date' => '%D, %F %j',
	'hour_date' => '%g:%i%a',
	'month_day' => '%j',

//	'hour_size_px' => 42,
	'time_step' => 15,

	'start_on_monday' => 1,
	'first_hour' => 8,
	'last_hour' => 22,
	'readonly' => false,
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

	'multi_day' => true,
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

	'left_border' => true
	);

	function display ($tpl=null)
	{
		$this->cal_type = $this->params->get('cal_type');
		$this->canCfg = false;
		$user = JFactory::getUser();

		switch ($this->cal_type) {
			case 0:		// user
				if ($user->id <= 0) return;
				$jID = array($user->id);
				$this->canCfg = true;
				$caldb = new RJUserData('sched');
				break;
			case 1:		// group
				$jID = $this->params->get('group_auth');
				$gauth = $jID;
				if (!is_array($gauth)) $gauth = array($gauth);
				if (array_intersect($user->groups, $gauth)) {
					$this->canCfg = true;
				}
				$caldb = new RJUserData('sched', false, $jID, true);
				break;
			case 2:		// site
				$jID = $this->params->get('site_auth');
				$sauth = $jID;
//				if (!is_array($sauth)) $sauth = array($sauth);
//				if (array_intersect($user->groups, $sauth)) {
				if ($user->authorise('core.edit')) {
					$this->canCfg = true;
				}
				$caldb = new RJUserData('sched', false, 0, true);
				break;
		}

		// store the caltype and user in the session
		if (!is_array($jID)) $jID = array($jID);
		//$this->state('calid', true, $this->cal_type.':'.implode(',', $jID));
		UserSchedHelper::uState('calid', true, $this->cal_type.':'.implode(',', $jID));

		if ($caldb->dataExists()) {
			$this->alertees = $caldb->getTable('alertees','',true); $this->alertees = $this->alertees ?: array();	//if (!$this->alertees) $this->alertees = array();
			$this->categories = $caldb->getTable('categories','',true); $this->categories = $this->categories ?: array();	//if (!$this->categories) $this->categories = array();
			$cfg = $caldb->getTable('options','name = "config"');
			if ($cfg) {
				$this->settings = unserialize($cfg['value']);
				$this->applyCfg($cfg['value']);
				$this->cfgcfg = json_encode($this->config);
				parent::display($tpl);
			}
		} else {
			JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_usersched&view=config', false)); 
		}
	}

	protected function applyCfg ($cfg)
	{
		$s = unserialize($cfg);
		
		$this->config['separate_short_events'] = $s['settings_collision'];
		$this->config['max_month_events'] = $s['settings_eventnumber'];

		$this->config['default_date'] = $s['templates_defaultdate'];
		$this->config['month_date'] = $s['templates_monthdate'];
		$this->config['week_date'] = $s['templates_weekdate'];
		$this->config['day_date'] = $s['templates_daydate'];
		$this->config['hour_date'] = $s['templates_hourdate'];
		$this->config['month_day'] = $s['templates_monthday'];

//		$this->config['hour_size_px'] = $s['templates_hourheight'];
		$this->config['time_step'] = $s['templates_minmin'];

		$this->config['start_on_monday'] = $s['settings_firstday'];
		$this->config['multi_day'] = $s['settings_multi_day'];

		$this->config['first_hour'] = $s['templates_starthour'];
		$this->config['last_hour'] = $s['templates_endhour'];
		$this->config['agenda_end'] = $s['templates_agendatime'];
	}

	protected function categoriesJSON ()
	{
		$jsn = array('{key:"",label:"[ none ]"}');
		if ($this->categories)
		foreach ($this->categories as $cat) {
			$jsn[] = json_encode(array('key'=>$cat['id'], 'label'=>$cat['name']));
		}
		return $jsn;
	}

	protected function categoriesCSS ()
	{
		$css = '';
		if ($this->categories)
		foreach ($this->categories as $cat) {
			$css .= '.dhx_cal_event div.evCat'.$cat['id']
			.',.dhx_cal_event_line.evCat'.$cat['id']
			.',.dhx_cal_event_clear.evCat'.$cat['id']
			.' {background-image:none;';
			if ($cat['bgcolor']) $css .= 'background-color:'.$cat['bgcolor'].' !important;';
			if ($cat['txcolor']) $css .= 'color:'.$cat['txcolor'].' !important;';
			$css .= "}\n";
		}
		return $css;
	}

}
