<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2025 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.0
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

Factory::getDocument()->addScript('components/com_usersched/fields/tperiod.js');

class JFormFieldTPeriod extends Joomla\CMS\Form\FormField
{
	protected $type = 'TPeriod';

	protected function getInput()
	{
		// Initialize variables.
		$options = [];
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		$sray = ['','','',''];
		$nn = '';
		if ($this->value) {
			$tval = $this->value / 60;
			if ($tval % 10080 == 0) { //weeks
				$sray[3] = ' selected="selected"';
				$nn = (int) $tval / 10080;
			} else if($tval % 1440 == 0) { //days
				$sray[2] = ' selected="selected"';
				$nn = (int) $tval / 1440;
			} else if($tval % 60 == 0) { //hours
				$sray[1] = ' selected="selected"';
				$nn = (int) $tval / 60;
			} else { //minutes
				$sray[0] = ' selected="selected"';
				$nn = (int) $tval;
			}
		}

		// build the combo field
		$html = '<input type="number" class="tper-num" onchange="TPER.mul(this.parentNode)" onkeyup="TPER.mul(this.parentNode)" value="'.$nn.'" style="width:60px" />&nbsp;';
		$html .= '<select class="tper-mul" onchange="TPER.mul(this.parentNode)" style="width:100px">';
		$html .= '<option value="60"'.$sray[0].'>minutes</option>';
		$html .= '<option value="3600"'.$sray[1].'>hours</option>';
		$html .= '<option value="86400"'.$sray[2].'>days</option>';
		$html .= '<option value="604800"'.$sray[3].'>weeks</option>';
		$html .= '</select>';
		$html .= '<input type="hidden" class="tper-valu" name="jform[params]['.$this->element['name'].']" />';
		return $html;
	}

}