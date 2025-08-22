<?php 
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2025 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.3.1
*/
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;


global $isDevel;
$isDevel = true;
$data = $this->data;	//echo'<pre>';var_dump($data); //jexit();
$sform = '
<form class="esrch" method="POST" onsubmit="return USched.doSearch(event,this)">
	<input type="search" name="cevsterm" placeholder=" search...">
</form>
';
$script = '
var USched = {
	doSearch: (evt, frm) => {
		if (!frm.sterm.value) {
			alert("Please enter a search term");
			return false;
		}
		return true;
	}
}
';
Factory::getDocument()->addStyleDeclaration($this->categoriesCSS());
Factory::getDocument()->addScriptDeclaration($script);
//echo'<xmp>';var_dump($this->instObj);echo'</xmp>';
if ($this->canSearch) echo $sform;
if ($this->params->get('show_page_heading', 1)) {
	echo '<div class="page-header"><h3>'.$this->escape($this->params->get('page_heading')).'</h3></div>';
}
?>
<?php if ($this->isSearch): ?>
	<p style="font-size:1.2em"><span style="font-weight: bold;margin-right:.7rem">Searching:</span><span><?php echo $this->sterm ?></span></p>
<?php else: ?>
	<p style="font-size:1.2em"><?php echo Text::sprintf($this->message ? $this->message : 'COM_USERSCHED_RANGE_MESSAGE', $this->formattedDateTime($this->rBeg, $this->rEnd)) ?></p>
<?php endif; ?>
<table align="center" class="ev_table">
<?php
    $num_events = count($data);
    foreach ($data as $row): ;
?>
	<tr>
		<td class="ev_td_<?php echo $row['category'] ?>">
			<ul>
				<li class="ev_td_li">
					<?php echo $this->formattedDateTime($row['t_start'], $row['t_end']); echo $row['alert_user'] ? '<i class="usch-alert far fa-bell"></i>' : ''; ?><br />
					<?php echo $this->formattedText($row['text']); ?>
				</li>
			</ul>
		</td>
	</tr>
<?php
	endforeach;
?>
</table>
<?php if ($this->show_versions) :?>
<div id="versionbar" class="userschedver">UserSched <span id="userschedver"><?php echo $this->version ?></span></div>
<?php endif; ?>
