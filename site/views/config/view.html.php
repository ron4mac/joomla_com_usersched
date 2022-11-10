<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\Folder;

//require_once JPATH_COMPONENT.'/helpers/usersched.php';
require_once JPATH_COMPONENT.'/views/uschedview.php';

class UserschedViewConfig extends UserschedView
{
	protected $config = [
	'default_date' => '%j %M %Y',
	'month_date' => '%F %Y',
	'load_date' => '%Y-%m-%d',
	'week_date' => '%l',
	'day_date' => '%D, %F %j',
	'hour_date' => '%H:%i',
	'month_day' => '%d',

	'time_step' => 5,

	'start_on_monday' => false,
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

	'left_border' => true,

	'lang_tag' => 'en-GB'
	];

	function display ($tpl=null)
	{
		$authids = (strpos($this->auth,',')) ? explode(',', $this->auth) : $this->auth;
		$this->canCfg = false;
		$this->canSkin = false;
		$this->canAlert = false;

		switch ((int)$this->params->get('cal_type')) {
			case 0:		// user
			//	var_dump($this->user,$authids);jexit();
				if ((int)$this->user->get('id') <= 0) {var_dump($this->user);jexit();return;}
			//	if ($this->user->id != $authids) return;
				$start = 'start';
				$this->canCfg = true;
				$this->canSkin = $this->cOpts->get('user_canskin') || $this->params->get('can_skin');
				$this->canAlert = $this->cOpts->get('user_canalert') || $this->params->get('can_alert');
				break;
			case 1:		// group
				$start = 'gstart';		//var_dump($this->auth,$authids, $this->user->groups);
//				if (in_array($authids, $this->user->groups)) {
				if ($this->instObj->canCreate()) {
					$this->canCfg = true;
					$this->grpId = $authids;
					$this->canSkin = $this->cOpts->get('grp_canskin') || $this->params->get('can_skin');
					$this->canAlert = $this->cOpts->get('grp_canalert') || $this->params->get('can_alert');
				} else {
					$start = 'nope';
				}
				break;
			case 2:		// site
				$start = 'sstart';
				if (!is_array($authids)) $authids = [$authids];
				if ($this->user->authorise('core.create')) {
					$this->canCfg = true;
					$this->canSkin = true;
					$this->canAlert = true;
				} else {
					$start = 'nope';
				}
				break;
			default:
				echo'<xmp>';var_dump($this);echo'</xmp>';jexit();
		}

		$this->document->addStyleSheet('components/com_usersched/static/config.css');
	//	HTMLHelper::_('bootstrap.colorPicker');	// also initiates jQuery so the next script works okay
		$this->document->addScript('components/com_usersched/static/config.js');
//		$script = 'jQuery(document).ready(function() { tabberAutomatic(tabberOptions); /*attachColorPickers();*/ });'."\n";
//		$this->document->addScriptDeclaration($script);

		//$langTag = Factory::getLanguage()->getTag();
		$this->config['lang_tag'] = Factory::getLanguage()->getTag();

		if (UschedHelper::userDataExists('sched.sql3')) {
			$m = $this->getModel();
			$this->alertees = $m->getUdTable('alertees'); if (!$this->alertees) $this->alertees = [];
			$this->categories = $m->getUdTable('categories'); if (!$this->categories) $this->categories = [];
			$cfg = $m->getUdTable('options','name = "config"',false);
			if ($cfg) {
				$this->settings = unserialize($cfg['value']);
				$this->applyCfg($cfg['value']);
				$this->cfgcfg = json_encode($this->config);
				//$this->cfgcfg['lang_tag'] = $langTag;
				$this->skinOptions = $this->getSkinOptions();
				parent::display($tpl);
			}
		} else {
			$this->alertees = [];
			$this->categories = [];
			$this->skinOptions = $this->getSkinOptions();
			$this->config = UserSchedHelper::$dfltConfig;
			parent::display($start);
		}
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

		$this->config['time_step'] = $s['templates_minmin'];

		$this->config['start_on_monday'] = $s['settings_firstday'];
		$this->config['first_hour'] = $s['templates_starthour'];
		$this->config['last_hour'] = $s['templates_endhour'];
	}

	protected function getSkinOptions ()
	{
		// Initialize variables.
		$options = [];
		$path = JPATH_COMPONENT_SITE . '/skins';

		// Prepend some default options
		$options[] = HTMLHelper::_('select.option', '', Text::_('JOPTION_USE_DEFAULT'));

		// Get a list of folders in the search path with the given filter.
		$folders = Folder::folders($path);

		// Build the options list from the list of folders.
		if (is_array($folders)) {
			foreach ($folders as $folder) {
				$options[] = HTMLHelper::_('select.option', $folder, $folder);
			}
		}

		return $options;
	}

}
