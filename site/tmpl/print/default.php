<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

//echo'<xmp>';var_dump($this->post);echo'</xmp>';
$skn = $this->skin ? ('_'.$this->skin) : '';
$this->document->addStylesheet('components/com_usersched/scheduler/dhtmlxscheduler'.$skn.'.css');
$this->document->addStyleDeclaration($this->categoriesCSS());

echo $this->html;
?>
<script type="text/javascript">
	(function() {
		let bpd = false;
		let apd = false;
	
		const beforePrint = () => {
			if (bpd) return;
			bpd = true;
			console.log("Functionality to run before printing.");
		};
	
		const afterPrint = () => {
			if (apd) return;
			apd = true;
			console.log("Functionality to run after printing");
			window.close();
			window.history.back();
		};
	
		if (window.matchMedia) {
			let mediaQueryList = window.matchMedia("print");
			mediaQueryList.addListener( (mql) => {
				if (mql.matches) {
					beforePrint();
				} else {
					afterPrint();
				}
			});
		}
	
		window.addEventListener("beforeprint", beforePrint);
		window.addEventListener("afterprint", afterPrint);
	
	//	window.print();
	}());
	document.addEventListener('DOMContentLoaded', function() {window.print();});
</script>
