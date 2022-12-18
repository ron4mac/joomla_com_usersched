<?php
defined('_JEXEC') or die;

JHtml::script('administrator/components/com_usersched/models/fields/tperiod.js');

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
		$html = '<input type="number" class="tpni" id="tpni_'.$this->element['name'].'" name="tpnn_'.$this->element['name'].'" value="'.$nn.'" style="width:60px" />&nbsp;';
		$html .= '<select id="tpsi_'.$this->element['name'].'" name="tpsn_'.$this->element['name'].'" style="width:100px">';
		$html .= '<option value="60"'.$sray[0].'>minutes</option>';
		$html .= '<option value="3600"'.$sray[1].'>hours</option>';
		$html .= '<option value="86400"'.$sray[2].'>days</option>';
		$html .= '<option value="604800"'.$sray[3].'>weeks</option>';
		$html .= '</select>';
		$html .= '<input type="hidden" id="tpcv_'.$this->element['name'].'" name="jform[params]['.$this->element['name'].']" />';
		return $html;
	}

}