<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2025 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.2
*/
namespace RJCreations\Component\Usersched\Site\View\Daterange;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use RJCreations\Component\Usersched\Site\Helper\Events;
use RJCreations\Component\Usersched\Site\Helper\HtmlUsersched;

define('RJC_DEVR', (JDEBUG) && Factory::getApplication()->input->get('dev',0,'integer'));

class HtmlView extends \RJCreations\Component\Usersched\Site\View\UschedView
{
	protected $rBeg;
	protected $rEnd;
	protected $canSearch = false;
	protected $isSearch = false;
	protected $oCalUrl = '';

	function display ($tpl = null)
	{
		$this->message = $this->params->get('message');
//echo '@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'.$this->getLayout().'@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@';
		$m = $this->getModel();
		if (!$m->hasData()) { parent::display('nope'); return; }

		$this->document->addStyleSheet('components/com_usersched/static/upcoming.css', ['version' => 'auto']);
		$this->categories = $m->getUdTable('categories');

		$caliObj = \UschedHelper::getInstanceObject($this->params->get('cal_menu'));
		$this->oCalUrl = Route::_('index.php?option=com_usersched&view=usersched&Itemid='.$caliObj->menuid, false);
		$this->canSearch = $caliObj->canEdit();

		if ($this->canSearch && $this->app->input->post->get('cevsterm', null, 'string')) {
			$this->isSearch = true;
			$this->sterm = Factory::getApplication()->input->get('cevsterm', '__', 'string');
			$evts = $m->evtSearch($this->sterm);
			$this->data = $evts;
			parent::display($tpl);
			return;
		}

		//$this->alertees = $m->getUdTable('alertees','',true);
		// if not registered, hide private categories
		$private = [];
		if ($this->user->id == 0) {
			//var_dump($this->categories);
			foreach ($this->categories as $cat) {
				if (preg_match('#\(.+\)#',trim($cat['name']))) {
					$private[] = $cat['id'];
				}
			}
			//var_dump($private);
		}
		$cfg = $m->getUdTable('options','name = "config"',false);
		// get event range
		$curTime = time();
		$this->rBeg = $curTime + $this->params->get('relstart');		//echo'<xmp>';var_dump($this->params);echo'</xmp>';
		$this->rEnd = $curTime + $this->params->get('relend');
	//	$fields = 'strtotime(`start_date`) AS t_start, strtotime(`end_date`) as t_end, text, category';
//		$fields = 'strtotime(`start_date`) AS t_start, strtotime(`end_date`) as t_end, *';
		$fields = 'strtotime(`start_date`) AS t_start, strtotime(`end_date`) as t_end, *';
		$where = '';
		if ($private) $where .= 'category NOT IN ('.implode(',',$private).') AND ';
		$where = '(t_start>'.$this->rBeg.' OR end_date LIKE \'9999%\' OR t_end>'.$this->rBeg.') AND t_start<'.$this->rEnd.' ORDER BY t_start';
//			$where = '(t_start>'.$this->rBeg.' OR t_end>'.$this->rBeg.') AND t_start<'.$this->rEnd.' ORDER BY t_start';
		$evts = $m->getUdTable('events', $where, true, $fields);
	//	Events::bugout('[-]'.$where,$evts);
		foreach ($evts as $k=>$evt) {
		//	Events::bugout('ts:te', [$evt['start_date'],$evt['t_start'],$evt['end_date'],$evt['t_end']]);
			if (!empty($evt['rrule'])) {
				// check for deleted individual instances of repeated events
				if ($evt['deleted']) {
					// remove this from the event list
					unset($evts[$k]);
					// find and remove the associated event instance
					foreach ($evts as $ek=>$ue) {
						if ($evt['event_pid'] == $ue['event_id'] && $evt['event_length'] == $ue['t_start']) {
							unset($evts[$ek]);
						}
					}
					continue;
				}
				if (Events::recursNow($evt, $this->rBeg, $this->rEnd, false)) {
					// adjust end to reflect event length
					$evt['t_end'] = $evt['t_start'] + $evt['duration'];
					//replace with adjusted event
					$evts[$k]=$evt;
				}
				else unset($evts[$k]);
			}
		}

		// turn urls into links
		foreach ($evts as $k=>$evt) {
			HtmlUsersched::makeLinks($evts[$k]['text']);
		}

		usort($evts, function ($a,$b) { if ($a['t_start']==$b['t_start']) return 0; return ($a['t_start'] < $b['t_start']) ? -1 : 1; });
		$this->data = $evts;
		parent::display($tpl);
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
		$ft = '<span class="evt-desc">'.array_shift($lines).'</span>';
		if ($lines) $ft .= '<br>'.implode('<br>',$lines);
		return $ft;
	}

	protected function categoriesCSS ()
	{
		$css = 'table.ev_table td.ev_td_ {border-left-color: #DDD'."}\n";;
		foreach ($this->categories as $cat) {
		//	$css .= 'table.ev_table td.ev_td_'.$cat['id'].' {border-left-color: '.$cat['bgcolor']."}\n";
			$css .= 'table.ev_table td.ev_td_'.$cat['id'].' {border-color: '.$cat['bgcolor']."}\n";
		}
		return $css;
	}

}
