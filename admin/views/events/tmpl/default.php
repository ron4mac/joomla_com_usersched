<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT.'/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

//var_dump('vdf',$this);jexit();

$dtimeformat = 'D, j M Y g:ia';	//Text::_('DATE_FORMAT_LC2');
$listOrder	= $this->state('list.ordering');
$listDirn	= $this->state('list.direction');
$canDo		= UserSchedHelper::getActions();
?>
<form action="<?php echo JRoute::_('index.php?option=com_usersched&view=events'); ?>" method="post" name="adminForm" id="adminForm">
	<?php echo HTMLHelper::_('usched.sideBar', $this->sidebar); ?>
	<div id="j-main-container" class="span10">

		<table class="table table-striped adminlist">
			<thead>
				<tr>
					<th width="1%"></th>
					<th width="1%"><?php echo JHtml::_('usched.checkall'); ?></th>
					<th width="15%">
						<?php echo JHtml::_('grid.sort', 'COM_USERSCHED_EV_START', 'startdate', $listDirn, $listOrder); ?>
					</th>
					<th width="10%">
						<?php echo JHtml::_('grid.sort', 'COM_USERSCHED_EV_CAT', 'category', $listDirn, $listOrder); ?>
					</th>
					<th width="10%">
						<?php echo JHtml::_('grid.sort', 'COM_USERSCHED_EV_RECTYPE', 'rectype', $listDirn, $listOrder); ?>
					</th>
					<th width="50%">
						<?php echo Text::_('COM_USERSCHED_EV_TEXT'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="6">
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
						<?php echo JHtml::_('grid.id', $i, $item['event_id']); ?>
					</td>
					<td>
						<?php $date= new DateTime($item['start_date']); echo $date->format($dtimeformat); ?>
					</td>
					<td>
						<?php echo $item['catname'] ?>
					</td>
					<td>
						<?php echo $item['rec_type'] ?>
					</td>
					<td>
						<?php echo str_replace(array("\r\n", "\r", "\n"), "<br />", $item['text']) ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	
		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="uid" value="<?php echo $this->state->get('usched_uid'); ?>" />
			<input type="hidden" name="isGrp" value="<?php echo $this->state->get('usched_isgrp'); ?>" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
