<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.2.0
*/
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/views/uschedview.php';

class UserschedViewUsersched extends UserschedView
{
	protected $tabs = [];
	protected $plugs = ['readonly' => true];
	protected $config = [
	'default_date' => '%j %M %Y',
	'month_date' => '%F %Y',
	'load_date' => '%Y-%m-%d',
	'week_date' => '%l',
	'day_date' => '%D, %F %j',
	'hour_date' => '%g:%i%a',
	'month_day' => '%j',

//	'hour_size_px' => 42,
	'time_step' => 15,

	'start_on_monday' => false,
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

	'icons_edit' => ['icon_save', 'icon_cancel'],
	'icons_select' => ['icon_details', 'icon_edit', 'icon_delete'],
	'buttons_left' => ['dhx_save_btn', 'dhx_cancel_btn'],
	'buttons_right' => ['dhx_delete_btn'],
	'highlight_displayed_event' => true,
	'displayed_event_color' => '#ffc5ab',
	'displayed_event_text_color' => '#7e2727',

	'left_border' => true
	];

	function display ($tpl=null)
	{
		$this->canCfg = false;
		list($cal_type, $jID) = UschedHelper::getInstanceID(true);
		$jID = is_array($jID) ? $jID : explode(',',$jID);
		switch ($cal_type) {
			case 0:
				$this->canCfg = true;
				break;
			case 1:
				if (array_intersect($this->user->groups, $jID)) {
					$this->canCfg = true;
				}
				break;
			case 2:
				if ($this->user->authorise('core.edit')) {
					$this->canCfg = true;
				}
				break;
		}

		// store the caltype and user in the session
		if (!is_array($jID)) $jID = [$jID];
		//$this->state('calid', true, $this->cal_type.':'.implode(',', $jID));
		UserSchedHelper::uState('calid', true, $this->params->get('cal_type').':'.implode(',', $jID));

		$m = $this->getModel();
		$this->alertees = $m->getUdTable('alertees'); $this->alertees = $this->alertees ?: [];	//if (!$this->alertees) $this->alertees = [];
		$this->categories = $m->getUdTable('categories'); $this->categories = $this->categories ?: [];	//if (!$this->categories) $this->categories = [];
		$cfg = $m->getUdTable('options', 'name = "config"', false);
		if ($cfg) {
			$dhxver = $this->params->get('dhtmlx_version', '7.0');
			if ((int)$dhxver > 6) $this->plugs['export_api'] = true;
			$this->settings = unserialize($cfg['value']);
			$this->applyCfg($this->settings);
			$this->cfgcfg = json_encode($this->config);
			parent::display($tpl.$dhxver);
		}
	}

	protected function applyCfg ($s)
	{
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

		// setup tabs
		if ($s['settings_agenda']) {
			$this->tabs[] = 'agenda';
			$this->plugs['agenda_view'] = true;
		}
		if ($s['settings_day']) $this->tabs[] = 'day';
		if ($s['settings_week']) $this->tabs[] = 'week';
		if ($s['settings_month']) $this->tabs[] = 'month';
		if ($s['settings_year']) {
			$this->tabs[] = 'year';
			$this->plugs['year_view'] = true;
		}
		// month view at minimum
		if (!$this->tabs) $this->tabs[] = 'month';

		// any other needed plugins
		if ($s['settings_repeat']) $this->plugs['recurring'] = true;
		if ($s['settings_expand']) $this->plugs['expand'] = true;
	}

	protected function categoriesJSON ()
	{
		$jsn = ['{key:"",label:"[ none ]"}'];
		if ($this->categories)
		foreach ($this->categories as $cat) {
			$jsn[] = json_encode(['key'=>$cat['id'], 'label'=>$cat['name']]);
		}
		return $jsn;
	}

	protected function categoriesCSS ()
	{
		$css = '';
		if ($this->categories)
		foreach ($this->categories as $cat) {
			$css .= '.dhx_cal_event div.evCat'.$cat['id']
			.',.dhx_cal_event_line.dhx_cal_event_line_start.dhx_cal_event_line_end.evCat'.$cat['id']
			.',.dhx_cal_event_clear.dhx_cal_event_line_start.dhx_cal_event_line_end.evCat'.$cat['id']
			.' {background-image:none;';
			if ($cat['bgcolor']) $css .= 'background-color:'.$cat['bgcolor'].';';
			if ($cat['txcolor']) $css .= 'color:'.$cat['txcolor'].';';
			$css .= "}\n";
		}
		return $css;
	}

}
