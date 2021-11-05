<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT.'/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

//var_dump('vdf',$this);jexit();

$listOrder = $this->state('list.ordering');
$listDirn = $this->state('list.direction');
$canDo = UserSchedHelper::getActions();
?>
<form action="<?php echo JRoute::_('index.php?option=com_usersched&view=calendars'); ?>" method="post" name="adminForm" id="adminForm">
	<?php echo HTMLHelper::_('usched.sideBar', $this->sidebar); ?>
	<div id="j-main-container" class="span10">

		<table  class="table table-striped adminlist">
			<thead>
				<tr>
					<th width="1%"></th>
					<th width="1%"><?php echo JHtml::_('usched.checkall'); ?></th>
					<th class="title">
						<?php echo JHtml::_('grid.sort', 'COM_USERSCHED_GROUPNAME', 'a.name', $listDirn, $listOrder); ?>
					</th>
					<th width="15%">
						<?php echo JHtml::_('grid.sort', 'COM_USERSCHED_GROUPMEMBERS', 'a.members', $listDirn, $listOrder); ?>
					</th>
					<th width="15%">
						<?php echo Text::_('COM_USERSCHED_GROUPID'); ?>
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
						<?php echo JHtml::_('grid.id', $i, $item['gid']); ?>
						<!-- <input type="checkbox" class="ssel" name="scheds[]" value="< ?php echo $item['uid'] ? >"> -->
					</td>
					<td>
						<?php echo $item['name']; ?>
						<a href="<?php echo JRoute::_('index.php?option=com_usersched&view=events&isgrp=true&uid=').$item['gid']; ?>">view</a>
					</td>
					<td class="center">
						<?php echo $item['members']; ?>
					</td>
					<td class="center">
						<?php echo $item['gid'] ?>
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
	</div>
</form>
