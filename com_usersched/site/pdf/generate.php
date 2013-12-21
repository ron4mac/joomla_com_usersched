<?php

require_once './pdfGenerator.php';
require_once './pdfWrapper.php';
require_once './tcpdf_ext.php';
$debug = true;
$error_handler = set_error_handler("PDFErrorHandler");

if (get_magic_quotes_gpc()) {
	$xmlString = urldecode(stripslashes($_POST['mycoolxmlbody']));
} else {
	$xmlString = urldecode($_POST['mycoolxmlbody']);
}

if ($debug == false) {
	error_log($xmlString, 3, 'debug_'.date("Y_m_d__H_i_s").'.xml');
}
//echo '<xmp>';var_dump($xmlString);echo '</xmp>';exit();
$xml = new SimpleXMLElement($xmlString, LIBXML_NOCDATA);
$scPDF = new schedulerPDF();
$scPDF->printScheduler($xml);
function PDFErrorHandler ($errno, $errstr, $errfile, $errline) {
	global $xmlString;
	if ($errno < 1024) {
		echo $errstr."<br>";
		error_log($xmlString, 3, 'error_report_'.date("Y_m_d__H_i_s").'.xml');
		exit(1);
	}
}

?>