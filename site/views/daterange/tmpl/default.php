<?php 
defined('_JEXEC') or die('Restricted access');
global $isDevel;
$isDevel = true;
$data = $this->data;	//echo'<pre>';var_dump($data); //jexit();
JFactory::getDocument()->addStyleDeclaration($this->categoriesCSS());
if ($this->params->get('show_page_heading', 1)) {
	echo '<div class="page-header"><h3>'.$this->escape($this->params->get('page_heading')).'</h3></div>';
}
?>
<p style="font-size:1.2em"><?php echo JText::sprintf($this->message ? $this->message : 'COM_USERSCHED_RANGE_MESSAGE', $this->formattedDateTime($this->rBeg, $this->rEnd)) ?></p>
<table align="center" width="100%" cellspacing="10" cellpadding="5" class="ev_table">
<?php
    $num_events = count($data);
    foreach ($data as $row): ;
?>
	<tr>
		<td class="ev_td_<?php echo $row['category'] ?>">
			<ul>
				<li class="ev_td_li">
					<?php echo $this->formattedDateTime($row['t_start'], $row['t_end']); ?><br />
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
