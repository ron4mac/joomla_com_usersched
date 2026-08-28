<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_usersched/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

//var_dump('vdf',$this);jexit();

$listOrder = $this->state('list.ordering');
$listDirn = $this->state('list.direction');
$isU = ($this->relm == 'usersched');
if ($isU) {
	$th1 = HTMLHelper::_('grid.sort', 'COM_USERSCHED_USERNAME', 'username', $listDirn, $listOrder);
	$th2 = HTMLHelper::_('grid.sort', 'COM_USERSCHED_FULLNAME', 'fullname', $listDirn, $listOrder);
	$th3 = HTMLHelper::_('grid.sort', 'COM_USERSCHED_USERID', 'userid', $listDirn, $listOrder);
} else {
	$th1 = HTMLHelper::_('grid.sort', 'COM_USERSCHED_GROUPNAME', 'a.name', $listDirn, $listOrder);
	$th2 = HTMLHelper::_('grid.sort', 'COM_USERSCHED_GROUPMEMBERS', 'a.members', $listDirn, $listOrder);
	$th3 = Text::_('COM_USERSCHED_GROUPID');
}
?>
<form action="<?php echo Route::_('index.php?option=com_usersched&view='.$this->relm); ?>" method="post" name="adminForm" id="adminForm">
	<?php //echo HTMLHelper::_('usched.sideBar', $this->sidebar); ?>
	<div id="j-main-container" class="span10">

		<table class="table table-striped adminlist">
			<thead>
				<tr>
					<th width="1%"></th>
					<th width="1%"><?php echo HTMLHelper::_('usched.checkall'); ?></th>
					<th width="15%">
						<?php echo $th1; ?>
					</th>
					<th width="15%">
						<?php echo $th2; ?>
					</th>
					<th width="15%">
						<?php echo $th3; ?>
					</th>
					<th width="30%">
						&#160;
					</th>
				</tr>
			</thead>
			<?php if ($this->pagination->total > 0): ?>
			<tfoot>
				<tr>
					<td colspan="6">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<?php endif; ?>
			<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="right">
						<?php echo $i + 1 + $this->pagination->limitstart; ?>
					</td>
					<td>
						<?php echo HTMLHelper::_('grid.id', $i, $item['guid'].'|'.$item['mnun']); ?>
					</td>
					<td>
						<?php echo $isU ? $item['uname'] : $item['name']; ?>
					</td>
					<td class="center">
						<?php echo $isU ? $item['name'] : $item['members']; ?>
					</td>
					<td class="center">
						<?php echo substr($item['guid'],1) ?>
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
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>

	</div>
</form>
