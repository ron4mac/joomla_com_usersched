<?php
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/helpers/usersched.php';
require_once JPATH_COMPONENT.'/helpers/ical.php';
jimport('rjuserdata.userdata');

class UserschedViewIcal extends JViewLegacy
{
	protected $cal_type;

	function display ($tpl = null)
	{
		$app = JFactory::getApplication();
		$this->params = $app->getParams();	//var_dump($this->params);jexit();
		$this->user = JFactory::getUser();
		$calid = UserSchedHelper::uState('calid');
		list($this->cal_type, $this->jID) = explode(':',$calid);

		switch ($this->cal_type) {
			case 0:		// user
				if ($this->jID <= 0) return;
				$caldb = new RJUserData('sched');
				break;
			case 1:		// group
				$caldb = new RJUserData('sched',false,$this->jID,true);
				break;
			case 2:		// site
				$caldb = new RJUserData('sched',false,0,true);
				break;
			default:
				$caldb = null;
		}

		JHtml::stylesheet('components/com_usersched/static/ical.css');
		if ($caldb->dataExists()) {
			$evts = $caldb->getTable('events');
			$this->data = $evts;
			parent::display($tpl);
		} else {
			parent::display('nope');
		}
	}

	function formattedDateTime ($from, $to=0)
	{
		if ($to-$from == 86400) {
			return date('D j F Y', $from);
		}
		$fdt = date('D j F Y g:ia', $from);
		if ($to) {
			if ($to-$from > 86400) {
				if ((date('Hi',$from).date('Hi',$to)) == '00000000') {
					return date('D j F Y', $from).' - '.date('D j F Y', $from);
				} else {
					$fdt .= ' - '.date('D j F Y g:ia', $to);
				}
			} else {
				$fdt .= ' to '.date('g:ia', $to);
			}
		}
		return $fdt;
	}

	function formattedText ($txt)
	{
		$lines = explode("\n",rtrim($txt));
		$ft = '<b style="line-height:2em">'.array_shift($lines).'</b><br />';
		$ft .= implode('<br />',$lines);
		return $ft;
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
		$css = 'table.ev_table td.ev_td_ {border-left-color: #DDD'."}\n";;
		foreach ($this->categories as $cat) {
			$css .= 'table.ev_table td.ev_td_'.$cat['id'].' {border-left-color: '.$cat['bgcolor']."}\n";
		}
		return $css;
	}
/*
	protected function state ($vari, $set=false, $val='0', $glb=false)
	{
		$stvar = ($glb?'':'com_usersched.').$vari;
		$app = JFactory::getApplication();
		if ($set) {
			$app->setUserState($stvar, $val);
			return;
		}
		return $app->getUserState($stvar, '0');
	}
*/
}
