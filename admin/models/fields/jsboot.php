<?php
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

JHtml::script('administrator/components/com_usersched/models/fields/jsboot.js');

class JFormFieldJSboot extends JFormField
{
	protected $type = 'JSboot';

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

	protected function getLabel()
	{
		return '<label style="display:none"></label>';
	}

}