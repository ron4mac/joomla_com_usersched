<?php
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/helpers/events.php';
require_once JPATH_COMPONENT.'/views/uschedview.php';

class UserschedViewDaterange extends UserschedView
{
	protected $rBeg;
	protected $rEnd;

	function display ($tpl = null)
	{
		$this->message = $this->params->get('message');

		$caldb = parent::getUserDatabase();
		if (!$caldb) return;

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
			$this->rBeg = $curTime + $this->params->get('relstart');
			$this->rEnd = $curTime + $this->params->get('relend');
		//	$fields = 'strtotime(`start_date`) AS t_start, strtotime(`end_date`) as t_end, text, category';
	//		$fields = 'strtotime(`start_date`) AS t_start, strtotime(`end_date`) as t_end, *';
			$fields = 'strtotime(`start_date`) AS t_start, strtotime(`end_date`) as t_end, *';
			$where = 'category NOT IN ('.implode(',',$private).') AND (t_start>'.$this->rBeg.' OR end_date LIKE \'9999%\' OR t_end>'.$this->rBeg.') AND t_start<'.$this->rEnd.' ORDER BY t_start';
			$evts = $caldb->getTable('events',$where,true,$fields);
			bugout('[-]'.$where,$evts);
			foreach ($evts as $k=>$evt) {
				if ($evt['rec_type']) {
					if (recursNow($evt, $this->rBeg, $this->rEnd, false)) {
						// adjust end to reflect event length
						$evt['t_end'] = $evt['t_start'] + $evt['event_length'];
						//replace with adjusted event
						$evts[$k]=$evt;
					}
					else unset($evts[$k]);
				}
			}
			usort($evts, function ($a,$b) { if ($a['t_start']==$b['t_start']) return 0; return ($a['t_start'] < $b['t_start']) ? -1 : 1; });
			$this->data = $evts;
			parent::display($tpl);
		} else {
			parent::display('nope');
		}
	}

	protected function formattedDateTime ($from, $to=0)
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

	protected function formattedText ($txt)
	{
		$lines = explode("\n",rtrim($txt));
		$ft = '<b style="line-height:2em">'.array_shift($lines).'</b><br />';
		$ft .= implode('<br />',$lines);
		return $ft;
	}

	protected function categoriesCSS ()
	{
		$css = 'table.ev_table td.ev_td_ {border-left-color: #DDD'."}\n";;
		foreach ($this->categories as $cat) {
			$css .= 'table.ev_table td.ev_td_'.$cat['id'].' {border-left-color: '.$cat['bgcolor']."}\n";
		}
		return $css;
	}

}
