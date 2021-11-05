<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT.'/helpers/html');
HTMLHelper::_('bootstrap.tooltip');
//JHTML::_('behavior.modal');
//HTMLHelper::_('bootstrap.formvalidation');

$script = '
	function previewSkin (row) {
	var c = document.adminForm, d = c[row];
	if (d) {
		skin = encodeURIComponent(d.value);
		window.open("'.Uri::root().'components/com_usersched/prevskin/skinPreview.php?skin="+skin,"_blank","menubar=0,scrollbar=0,toolbar=0,status=0,location=0,titlebar=0,height=675,width=900")
		//alert(skin);
		}
	}
';
// Add the script to the document head.
Factory::getDocument()->addScriptDeclaration($script);

$canDo = UserSchedHelper::getActions();
$imgPath = Uri::base().'components/com_usersched/static/';
$selIcon = 'selected.png';
$unselIcon = 'unselected.png';
?>
<form action="<?php echo JRoute::_('index.php?option=com_usersched&view=skins'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">

		<table class="table table-striped adminlist">
			<thead>
				<tr>
					<th width="1%"></th>
					<th width="1%"><?php echo JHtml::_('myGrid.checkall'); ?></th>
					<th width="15%">
						<?php echo Text::_('COM_USERSCHED_SKIN_NAME'); ?>
					</th>
					<th width="1%">
						<?php echo Text::_('COM_USERSCHED_USERSKIN_COLUMN') ?>
					</th>
					<th width="1%">
						<?php echo Text::_('COM_USERSCHED_GROUPSKIN_COLUMN') ?>
					</th>
					<th width="1%">
						<?php echo Text::_('COM_USERSCHED_SITESKIN_COLUMN') ?>
					</th>
					<th width="30%">
						&#160;
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="7">
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
						<?php echo JHtml::_('grid.id', $i, $item['name']); ?>
					</td>
					<td>
						<a href="javascript:void(0);" onclick="return previewSkin('cb<?php echo $i; ?>')" title="<?php echo Text::_('COM_USERSCHED_PREVIEW_SKIN'); ?>"><?php echo $item['name']?$item['name']:'-standard-'; ?></a>
					</td>
					<td class="center">
						<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i; ?>','skins.makeDfltU')"><img src="<?php echo $imgPath.($item['isUdef']?$selIcon:$unselIcon)?>" /></a>
					</td>
					<td class="center">
						<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i; ?>','skins.makeDfltG')"><img src="<?php echo $imgPath.($item['isGdef']?$selIcon:$unselIcon)?>" /></a>
					</td>
					<td class="center">
						<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i; ?>','skins.makeDfltS')"><img src="<?php echo $imgPath.($item['isSdef']?$selIcon:$unselIcon)?>" /></a>
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
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
<div style="display:none">
<div id="upload_div" style="width:auto;height:100%;border:1px solid #CCC;padding:10px;display:inline-table">
	<form action="<?php echo JRoute::_('index.php?option=com_usersched&view=skins'); ?>" class="form-validate" onsubmit=" return document.formvalidator.isValid(this)" enctype="multipart/form-data" method="post" name="upldForm" id="upldForm">
		<div>
			<p><?php echo Text::_('COM_USERSCHED_UPLOAD_MSG') ?></p>
			<label><?php echo Text::_('COM_USERSCHED_UPLOAD_LABEL') ?></label><input type="text" name="skin_name" class="required validate-string" required />
			<br /><input type="file" name="skinfile" accept="application/zip" class="required validate-string" />
			<br /><button type="submit" class="validate"><?php echo Text::_('COM_USERSCHED_UPLOAD_SUBMIT') ?></button>
			<input type="hidden" name="task" value="skins.addSkin" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
</div>
