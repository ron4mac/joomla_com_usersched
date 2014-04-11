<?php
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

JHtml::script('administrator/components/com_usersched/models/fields/timerng.js');

class JFormFieldTPeriod extends JFormField
{
	protected $type = 'TPeriod';

	protected function getInput()
	{	//echo'<xmp>';var_dump($this->form->getValue('cal_type','params'));echo'</xmp>';jexit();
		// Initialize variables.
		$options = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		$sray = array('','','','');
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


		// Iterate through the children and build an array of options.
		foreach ($this->element->children() as $option)
		{

			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_(
				'select.option', (string) $option['value'], trim((string) $option), 'value', 'text',
				((string) $option['disabled'] == 'true')
			);

			// Set some option attributes.
			$tmp->class = (string) $option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		$caltype = $this->form->getValue('cal_type','params');
		$style = $this->element['mask'] == $caltype ? '' : ' style="display:none"';
		$label = $this->element['label'];
		$desc = $this->element['description'];
		$html = '<div id="jfp_'.$this->element['name'].'"'.$style.'><label class="hasTip" title="'.$label.':'.$desc.'">'.$label.'</label>';
		return $html . JHtml::_('access.usergroup', $this->name, $this->value, $attr, $options, $this->id) . '</div>';
	}

//	protected function getLabel()
//	{
//		return '<label style="display:none"></label>';
//	}

}