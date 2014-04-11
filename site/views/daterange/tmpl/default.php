<?php 
defined('_JEXEC') or die('Restricted access');
$data = $this->data;	//echo'<pre>';var_dump($data); //jexit();
JFactory::getDocument()->addStyleDeclaration($this->categoriesCSS());
echo '<p style="font-size:1.2em">Events in the range: '.$this->formattedDateTime($this->rBeg, $this->rEnd).'</p>';
?>
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