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
				if ($this->user->id <= 0) return;
				$this->jID = array($this->user->id);
				$this->canCfg = true;
				$caldb = new RJUserData('sched');
				break;
			case 1:		// group
				$this->jID = $this->params->get('group_auth');
				$gauth = $this->jID;
				if (!is_array($gauth)) $gauth = array($gauth);
				if (array_intersect($this->user->groups,$gauth)) {
					$this->canCfg = true;
				}
				$caldb = new RJUserData('sched',false,$this->jID,true);
				break;
			case 2:		// site
				$this->jID = $this->params->get('site_auth');
				$sauth = $this->jID;
				if (!is_array($sauth)) $sauth = array($sauth);
				if (array_intersect($this->user->groups,$sauth)) {
					$this->canCfg = true;
				}
				$caldb = new RJUserData('sched',false,0,true);
				break;
		}

		// store the caltype and user in the session
		if (!is_array($this->jID)) $this->jID = array($this->jID);
		$this->state('calid', true, $this->cal_type.':'.implode(',', $this->jID));

		if ($caldb->dataExists()) {
			$this->alertees = $caldb->getTable('alertees','',true); $this->alertees = $this->alertees ?: array();	//if (!$this->alertees) $this->alertees = array();
			$this->categories = $caldb->getTable('categories','',true); $this->categories = $this->categories ?: array();	//if (!$this->categories) $this->categories = array();
			$cfg = $caldb->getTable('options','name = "config"');
			if ($cfg) {
				$this->settings = unserialize($cfg['value']);
				$this->applyCfg($cfg['value']);
				$this->cfgcfg = json_encode($this->config);
				$this->skinOptions = $this->getSkinOptions();
				parent::display($tpl);
			}
		} else {
		//	JHtml::script('components/com_usersched/static/config.js',true);
		//	JHtml::script('components/com_usersched/static/color-picker.js');
		//	JHtml::stylesheet('components/com_usersched/static/config.css');
		//	$this->skinOptions = $this->getSkinOptions();
		//	parent::display($start);
			$app->redirect(JRoute::_('index.php?view=config', false)); 
		//	$this->setRedirect(JRoute::_('index.php?view=config', false));
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
		$options[] = JHtml::_('select.option', '', JText::_('JOPTION_USE_DEFAULT'));

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
//			$css .= '.dhx_cal_event.evCat'.$cat['id'].' div.dhx_title, .dhx_cal_event_line.evCat'.$cat['id'].' {background-color: '.$cat['bgcolor'].' !important;background-image: none;color: '.$cat['txcolor'].' !important;}'."\n";
			$css .= '.dhx_cal_event div.evCat'.$cat['id'].', .dhx_cal_event_line.evCat'.$cat['id'].', .dhx_cal_event_clear.evCat'.$cat['id'].' {background-color: '.$cat['bgcolor'].' !important;background-image: none;color: '.$cat['txcolor'].' !important;}'."\n";
		}
		return $css;
	}

	protected function state ($vari, $set=false, $val='0', $glb=false)
	{
		$app = JFactory::getApplication();
		if ($set) {
			$app->setUserState($option.'_'.$vari, $val);
			return;
		}
		return $app->getUserState(($glb ? '' : "{$option}_").$vari, '0');
	}

}
