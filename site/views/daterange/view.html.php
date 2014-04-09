<?php
defined('_JEXEC') or die;

jimport('rjuserdata.userdata');

require_once JPATH_COMPONENT . '/helpers/events.php';

class UserschedViewDaterange extends JViewLegacy
{
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
				$this->jID = $this->user->id;
				if ($this->jID <= 0) return;
				$caldb = new RJUserData('sched');
				break;
			case 1:		// group
				$this->jID = $this->params->get('group_auth');
				$caldb = new RJUserData('sched',false,$this->jID,true);
				break;
			case 2:		// site
				$this->jID = $this->params->get('site_auth');
				$caldb = new RJUserData('sched',false,0,true);
				break;
		}

		JHtml::stylesheet('components/com_usersched/static/upcoming.css');
		if ($caldb->dataExists()) {
			//$this->alertees = $caldb->getTable('alertees','',true);
			$this->categories = $caldb->getTable('categories','',true);
			// if not registered, hide private categories
			$private = array(-1);
			if ($this->user->id == 0) {
				//var_dump($this->categories);
				foreach ($this->categories as $cat) {
					if (preg_match('#\(.+\)#',trim($cat['name']))) {
						$private[] = $cat['id'];
					}
				}
				//var_dump($private);
			}
			$cfg = $caldb->getTable('options','name = "config"');
			// get event range
			$curTime = time();
			$rBeg = $curTime - 86400;
			$rEnd = $curTime + 7776000;
		//	$fields = 'strtotime(`start_date`) AS t_start, strtotime(`end_date`) as t_end, text, category';
	//		$fields = 'strtotime(`start_date`) AS t_start, strtotime(`end_date`) as t_end, *';
			$fields = 'strtotime(`start_date`) AS t_start, strtotime(`end_date`) as t_end, *';
			$where = 'category NOT IN ('.implode(',',$private).') AND (t_start>'.$rBeg.' OR end_date LIKE \'9999%\' OR t_end>'.$rBeg.') AND t_start<'.$rEnd.' ORDER BY t_start';
			$evts = $caldb->getTable('events',$where,true,$fields);
			bugout('[-]'.$where,$evts);
			foreach ($evts as $k=>$evt) {
				if ($evt['rec_type']) {
					if (recursNow($evt, $rBeg, $rEnd, false)) {
						// adjust end to reflect event length
						$evt['t_end'] = $evt['t_start'] + $evt['event_length'];
						//replace with adjusted event
						$evts[$k]=$evt;
					}
					else unset($evts[$k]);
				}
			}
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
