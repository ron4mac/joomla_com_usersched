<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.0
*/
namespace RJCreations\Component\Usersched\Site\Helper;

defined('_JEXEC') or die;

class R_DateTime extends \DateTime {
	public function __construct($s='now', $z=null, $t=null) {
		parent::__construct($s,$z);
		if ($t) $this->setTimestamp($t);
	}
	public function __toString() {
		return $this->format('Y-m-d H:i:s');
	}
	public function setDay2($day) {
		$new = preg_match('/^(\d\d\d\d)\-(\d\d)/', $this->format('Y-m-d'), $m);	//var_dump($m);
		$this->setDate($m[1],$m[2],$day);
	}
	public function getDay() {
		return $this->format('d') + 0;
	}
	public function getDow() {
		return $this->format('w') + 0;
	}
	public function nextDow ($d,$n=1) {
		$cd = $this->getDow();
		if ($d>$cd) { $di = $d-$cd; }
		else if ($d<$cd) { $di = ($n*7) - $cd + $d; }
		else { $di = $n*7; }
		$this->add(new \DateInterval('P'.($di).'D'));
	}
	public function setMonth($mth) {
		$new = preg_match('/^(\d\d\d\d)\-\d\d\-(\d\d)/', $this->format('Y-m-d'), $m);	//var_dump($m);
		$this->setDate($m[1],$mth,$m[2]);
	}
	public function setYear($yr) {
		$new = preg_match('/^\d\d\d\d\-(\d\d)\-(\d\d)/', $this->format('Y-m-d'), $m);	//var_dump($m);
		$this->setDate($yr,$m[1],$m[2]);
	}
	public function getMonth() {
		return $this->format('m') + 0;
	}
	public function getFullYear() {
		return $this->format('Y') + 0;
	}
	function addTo($dobj,$inc,$mode){
		global $actb;
		$ndate = new R_DateTime($dobj->__toString());
		switch($mode){
			case 'week':
				$inc *= 7;
			case 'day':
				$ndate->setDay2($ndate->getDay() + $inc);
				if (!$dobj->getHours() && $ndate->getHours()) //shift to yesterday
					$ndate->setTime($ndate->getTime() + 60 * 60 * 1000 * (24 - $ndate->getHours()));
				break;
			case 'month': $ndate->setMonth($ndate->getMonth()+$inc); break;
			case 'year': $ndate->setYear($ndate->getFullYear()+$inc); break;
			case 'hour': $ndate->setHours($ndate->getHours()+$inc); break;
			case 'minute': $ndate->setMinutes($ndate->getMinutes()+$inc); break;
			default:
				return $actb['add_'.$mode]($dobj,$inc,$mode);
				exit();
		}
		return $ndate;
	}
}
