<?php

require_once('./tcpdf/tcpdf.php');

class TCPDFExt extends TCPDF {

	public function Footer() {				
		$cur_y = $this->GetY();
		$ormargins = $this->getOriginalMargins();
		$this->SetTextColor(0, 0, 0);			
		//set style for cell border
		$line_width = 0.85 / $this->getScaleFactor();
		$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
		//print document barcode
		$barcode = $this->getBarcode();
		if (!empty($barcode)) {
			$this->Ln($line_width);
			$barcode_width = round(($this->getPageWidth() - $ormargins['left'] - $ormargins['right'])/3);
			$this->write1DBarcode($barcode, 'C128B', $this->GetX(), $cur_y + $line_width, $barcode_width, (($this->getFooterMargin() / 3) - $line_width), 0.3, '', '');	
		}
		if (empty($this->pagegroups)) {
			$pagenumtxt = $this->l['w_page'].' '.$this->getAliasNumPage().' / '.$this->getAliasNbPages();
		} else {
			$pagenumtxt = $this->l['w_page'].' '.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias();
		}		
		$this->SetY($cur_y);
		//Print page number
		if ($this->getRTL()) {
			$this->SetX($ormargins['right']);
			$this->Cell(0, 0, $pagenumtxt, 0, 0, 'L');
		} else {
			$this->SetX($ormargins['left']);
			$this->Cell(0, 0, $pagenumtxt, 0, 0, 'R');
		}
	}

}

?>