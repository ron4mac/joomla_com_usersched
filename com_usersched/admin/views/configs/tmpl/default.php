<?php
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

//var_dump('vdf',$this);jexit();

$listOrder = $this->state('list.ordering');
$listDirn = $this->state('list.direction');
$canDo = UserSchedHelper::getActions();
?>
<form action="<?php echo JRoute::_('index.php?option=com_usersched&view=configs'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="20">
					<?php echo JText::_('JGRID_HEADING_ROW_NUMBER'); ?>
				</th>
				<th width="20">
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'COM_SEARCH_HEADING_PHRASE', 'a.search_term', $listDirn, $listOrder); ?>
				</th>
				<th width="15%">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
				</th>
				<th width="15%">
					<?php echo JText::_('COM_SEARCH_HEADING_RESULTS'); ?>
				</th>
				<th width="30%">
					&#160;
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="12">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="right">
					<?php echo $i + 1 + $this->pagination->limitstart; ?>
				</td>
				<td>
					<?php echo JHtml::_('grid.id', $i, $item['uid']); ?>
					<!-- <input type="checkbox" class="ssel" name="scheds[]" value="<?php echo $item['uid'] ?>"> -->
				</td>
				<td>
					<?php echo $item['name']; ?>
				</td>
				<td class="center">
					<?php echo $item['uname']; ?>
				</td>
				<td class="center">
					<?php echo $item['uid'] ?>
				</td>
				<td>
					&#160;
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>


	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
