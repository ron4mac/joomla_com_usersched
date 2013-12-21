<?php
defined('_JEXEC') or die;

jimport('rjuserdata.userdata');

class UserschedViewUsersched extends JViewLegacy
{
	protected $config = array(
	'default_date' => '%j %M %Y',
	'month_date' => '%F %Y',
	'load_date' => '%Y-%m-%d',
	'week_date' => '%l',
	'day_date' => '%D, %F %j',
	'hour_date' => '%H:%i',
	'month_day' => '%d',

	'hour_size_px' => 42,
	'time_step' => 5,

	'start_on_monday' => 1,
	'first_hour' => 0,
	'last_hour' => 24,
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

	protected $cal_type;

	function display ($tpl = null)
	{
		$app = JFactory::getApplication();
		$this->params = $app->getParams();	//var_dump($this->params);jexit();
		$this->user = JFactory::getUser();
		$this->cal_type = $this->params->get('cal_type');
		$this->canCfg = false;

		switch ($this->cal_type) {
			case 0:		// user
				$start = 'start';
				$this->jID = $this->user->id;
				if ($this->jID <= 0) return;
				$this->canCfg = true;
				$caldb = new RJUserData('sched');
				break;
			case 1:		// group
				$start = 'gstart';
				$this->jID = $this->params->get('group_auth');
				$gauth = $this->jID;
				if (!is_array($gauth)) $gauth = array($gauth);
				if (array_intersect($this->user->groups,$gauth)) {
					$this->canCfg = true;
				} else {
					$start = 'nope';
				}
				$caldb = new RJUserData('sched',false,$this->jID,true);
				break;
			case 2:		// site
				$start = 'sstart';
				$this->jID = $this->params->get('site_auth');
				$sauth = $this->jID;
				if (!is_array($sauth)) $sauth = array($sauth);
				if (array_intersect($this->user->groups,$sauth)) {
					$this->canCfg = true;
				} else {
					$start = 'nope';
				}
				$caldb = new RJUserData('sched',false,0,true);
				break;
		}

		if ($caldb->dataExists()) {
			$this->alertees = $caldb->getTable('alertees','',true);
			$this->categories = $caldb->getTable('categories','',true);
			$cfg = $caldb->getTable('options','name = "config"');
			if ($cfg) {
				$this->settings = unserialize($cfg['value']);
				$this->applyCfg($cfg['value']);
				$this->cfgcfg = json_encode($this->config);
				$this->skinOptions = $this->getSkinOptions();
				parent::display($tpl);
			}
		} else {
			JHtml::script('components/com_usersched/static/config.js',true);
			JHtml::script('components/com_usersched/static/color-picker.js');
			JHtml::stylesheet('components/com_usersched/static/config.css');
			$this->skinOptions = $this->getSkinOptions();
			parent::display($start);
		}

/*
		switch ($this->cal_type) {
			case 0:
				$this->jID = $this->user->id;																			//////
				$userdb = new RJUserData('sched');																		//////
				if ($userdb->dataExists()) {
					$this->alertees = $userdb->getTable('alertees','',true);
					$cfg = $userdb->getTable('options','name = "config"');
					if ($cfg) {
						$this->settings = unserialize($cfg['value']);
						$this->applyCfg($cfg['value']);
						$this->cfgcfg = json_encode($this->config);
						$this->canCfg = true;																			//////
						parent::display($tpl);
					}
				} else {
					parent::display('start');
				}
				break;
			case 1:
				$this->jID = $this->params->get('group_auth');															//////
				$gauth = $this->jID;
				if (!is_array($gauth)) $gauth = array($gauth);
				$grpdb = new RJUserData('sched',false,$this->params->get('group_auth'),true);							//////
				if ($grpdb->dataExists()) {
					$cfg = $grpdb->getTable('options','name = "config"');
					if ($cfg) {
						$this->settings = unserialize($cfg['value']);
						$this->applyCfg($cfg['value']);
						$this->cfgcfg = json_encode($this->config);
						if (array_intersect($this->user->groups,$gauth)) $this->canCfg = true;	//////
						parent::display($tpl);
					}
				} else {
					if (array_intersect($this->user->groups,$gauth)) {
						parent::display('gstart');
					} else {
						parent::display('nope');
					}
				}
				break;
			case 2:
				//$this->xmlObj = simplexml_load_file(JPATH_COMPONENT.'/default.xml');
				//$this->settings = $this->xmlObj->settings;
				//$this->templates = $this->xmlObj->templates;
				//$model= $this->getModel();
				//$ray = $this->get('Config');
				if (array_intersect($this->user->groups,$this->params->get('site_auth'))) {
					$this->cfgcfg = json_encode($this->config);
					parent::display('gstart');
				} else {
					parent::display('nope');
				}
				break;
		}
*/

	}

	protected function applyCfg ($cfg)
	{
		$s = unserialize($cfg);
		$this->config['default_date'] = $s['templates_defaultdate'];
		$this->config['month_date'] = $s['templates_monthdate'];
		$this->config['week_date'] = $s['templates_weekdate'];
		$this->config['day_date'] = $s['templates_daydate'];
		$this->config['hour_date'] = $s['templates_hourdate'];
		$this->config['month_day'] = $s['templates_monthday'];

		$this->config['hour_size_px'] = $s['templates_hourheight'];
		$this->config['time_step'] = $s['templates_minmin'];

		$this->config['start_on_monday'] = $s['settings_firstday'];
		$this->config['first_hour'] = $s['templates_starthour'];
		$this->config['last_hour'] = $s['templates_endhour'];
		$this->config['dblclick_create'] = $s['settings_singleclick'];
	}

	protected function getConfig ()
	{
		return $this->default_cfg;
	}

	protected function getSkinOptions ()
	{
		jimport('joomla.filesystem.folder');

		// Initialize variables.
		$options = array();
		$path = JPATH_SITE . '/components/com_usersched/skins';

		// Prepend some default options
		$options[] = JHtml::_('select.option', '', JText::_('DEFAULT_SKIN'));

		// Get a list of folders in the search path with the given filter.
		$folders = JFolder::folders($path);

		// Build the options list from the list of folders.
		if (is_array($folders)) {
			foreach ($folders as $folder) {
				$options[] = JHtml::_('select.option', $folder, $folder);
			}
		}

		return $options;
	}

	protected function categoriesJSON ()
	{
		$jsn = array('{key:"",label:"[ none ]"}');
		foreach ($this->categories as $cat) {
			$jsn[] = json_encode(array('key'=>$cat['id'], 'label'=>$cat['name']));
		}
		return $jsn;
	}

	protected function categoriesCSS ()
	{
		$css = '';
		foreach ($this->categories as $cat) {
//			$css .= '.dhx_cal_event.evCat'.$cat['id'].' div.dhx_title, .dhx_cal_event_line.evCat'.$cat['id'].' {background-color: '.$cat['bgcolor'].' !important;background-image: none;color: '.$cat['txcolor'].' !important;}'."\n";
			$css .= '.dhx_cal_event div.evCat'.$cat['id'].', .dhx_cal_event_line.evCat'.$cat['id'].', .dhx_cal_event_clear.evCat'.$cat['id'].' {background-color: '.$cat['bgcolor'].' !important;background-image: none;color: '.$cat['txcolor'].' !important;}'."\n";
		}
		return $css;
	}

	protected function state ($vari, $set=false, $val='0', $glb=false)
	{
		$mainframe =& JFactory::getApplication();
		if ($set) {
			$mainframe->setUserState($option.'_'.$vari, $val);
			return;
		}
		return $mainframe->getUserState(($glb ? '' : "{$option}_").$vari, '0');
	}

}
