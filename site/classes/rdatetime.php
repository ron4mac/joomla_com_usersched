<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
namespace RJCreations\Component\Usersched\Site\Helper;

defined('_JEXEC') or die;

class R_DateTime extends \DateTime implements \Stringable
{
	public function __construct ($s='now', $z=null, $t=null)
	{
		parent::__construct($s,$z);
		if ($t) $this->setTimestamp($t);
	}
	public function __toString (): string
	{
		return $this->format('Y-m-d H:i:s');
	}
	public function setDay2 ($day): void
	{
		$this->setDate($m[1],$m[2],$day);
	}
	public function getDay (): int|float
	{
		return $this->format('d') + 0;
	}
	public function getDow (): int|float
	{
		return $this->format('w') + 0;
	}
	public function nextDow ($d,$n=1): void
	{
		$cd = $this->getDow();
		if ($d>$cd) { $di = $d-$cd; }
		else if ($d<$cd) { $di = ($n*7) - $cd + $d; }
		else { $di = $n*7; }
		$this->add(new \DateInterval('P'.($di).'D'));
	}
	public function setMonth ($mth): void
	{
		$this->setDate($m[1],$mth,$m[2]);
	}
	public function setYear ($yr): void
	{
		$this->setDate($yr,$m[1],$m[2]);
	}
	public function getMonth (): int|float
	{
		return $this->format('m') + 0;
	}
	public function getFullYear (): int|float
	{
		return $this->format('Y') + 0;
	}
	public function addTo ($dobj,$inc,$mode)
	{
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
		}
		return $ndate;
	}
}
