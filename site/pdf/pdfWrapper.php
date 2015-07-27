<?php
// License: GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)

require_once 'tcpdf_ext.php';

class pdfWrapper {
	private $cb;
	private $footenotes;
	private $colWidth = 50; // column width in month mode for footenotes
	private $colOffset = 5;  // spaces between columns in month mode for footenotes
	
	private $headerImg = false;
	private $footerImg = false;
	private $headerImgHeight = false;
	private $footerImgHeight = false;

	private $timelineSectionsY = 0;

	public function pdfWrapper($sizes) {
		$this->cb = new TCPDFExt('P', 'mm', 'USLETTER', true, 'UTF-8', false);

		$this->offsetLeft = $sizes->offsetLeft;
		$this->offsetRight = $sizes->offsetRight;
		$this->offsetTop = $sizes->offsetTop;
		$this->offsetBottom = $sizes->offsetBottom;

		// sets header and footer
		$this->cb->setPrintHeader(false);
		$this->cb->setPrintFooter(false);
		$this->cb->SetMargins($this->offsetLeft, $this->offsetTop, $this->offsetRight);
		$this->cb->SetAutoPageBreak(FALSE, $this->offsetBottom);
		$this->cb->SetFooterMargin($this->offsetBottom);

		// sets output PDF information
		$this->cb->SetCreator('AltrusaRochester');
		$this->cb->SetAuthor('AltrusaRochester');
		$this->cb->SetTitle('AltrusaRochester');
		$this->cb->SetSubject('Calendar');
		$this->cb->SetKeywords('');

		// sets font family and size
		$this->cb->SetFont('helvetica', '', 8);
	}


	public function setHeader($headerImg, $headerHeight) {
		$this->headerImg = $headerImg;
		$this->headerImgHeight = $headerHeight;
	}


	public function setFooter($footerImg, $footerHeight) {
		$this->footerImg = $footerImg;
		$this->footerImgHeight = $footerHeight;
	}


	// draws scheduler header in month mode
	public function drawMonthHeader($orientation, $topScale, $sizes, $colors) {
		$this->cb->AddPage($orientation);
		$bgColor = $this->convertColor($colors->bgColor);
		$lineColor = $this->convertColor($colors->lineColor);
		$headerTextColor = $this->convertColor($colors->headerTextColor);
		$headerLineColor = $this->convertColor($colors->headerLineColor);
		$this->topScaleHeight = $sizes->monthHeaderHeight;
		$this->topScale = $topScale;

		$lineStyle = Array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $headerLineColor);
		$this->cb->SetLineStyle($lineStyle);
		$this->setFillColor($bgColor);
		$this->setTextColor($headerTextColor);
		$this->cb->SetFontSize($sizes->monthHeaderFontSize);

		// calculates day width
		$this->dayWidth = ($this->cb->getPageWidth() - $this->offsetLeft - $this->offsetRight)/7;
		// circle: for every column of header draw cell
		for ($i = 0; $i < count($topScale); $i++) {
			$text = $this->textWrap($topScale[$i], $this->dayWidth, $sizes->monthHeaderFontSize);
			$this->cb->Cell($this->dayWidth, $sizes->monthHeaderHeight, $text, 0, 0, 'C', 1);
			//d raw cells separator
			if ($i > 0) {
				$x = $this->offsetLeft + $i*$this->dayWidth;
				$y = $this->offsetTop;
				$lineStyle['color'] = $headerLineColor;
				$this->cb->Line($x, $y, $x, $y + $sizes->monthHeaderHeight, $lineStyle);
			}
		}
		$this->drawImgHeader();
		$this->drawImgFooter();
	}


	// draws scheduler grid on month mode
	public function drawMonthGrid($dayHeader, $sizes, $colors) {
		// sets starting coordinates
		$this->cb->setX($this->offsetLeft);
		$this->cb->setY($this->offsetTop + $this->topScaleHeight, false);

		$dayHeaderColor = $this->convertColor($colors->dayHeaderColor);
		$dayBodyColor = $this->convertColor($colors->dayBodyColor);
		$dayHeaderColorInactive = $this->convertColor($colors->dayHeaderColorInactive);
		$dayBodyColorInactive = $this->convertColor($colors->dayBodyColorInactive);
		$lineColor = $this->convertColor($colors->lineColor);
		$lineStyle = Array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $lineColor);
		$this->cb->SetLineStyle($lineStyle);

		// calculates height of day container header, body and whole height of day container
		$this->dayHeaderHeight = $sizes->monthDayHeaderHeight;
		$this->dayHeader = $dayHeader;
		$this->dayBodyHeight = ($this->cb->getPageHeight() - $this->offsetTop - $this->offsetBottom - $this->topScaleHeight - $this->dayHeaderHeight*count($dayHeader))/count($dayHeader);
		$this->dayHeight = $this->dayHeaderHeight + $this->dayBodyHeight;

		// sets font size
		$this->cb->SetFontSize($sizes->monthDayHeaderFontSize);
		// creates array of values where result['week']['day'] is true if day container in current month and false otherwise
		$activeDays = $this->setActiveDays($dayHeader);

		// circle for drawing day containers
		for ($i = 0; $i < count($dayHeader); $i++) {
			// draws day headers for every cell of row
			for ($j = 0; $j < count($dayHeader[$i]); $j++) {
				$ln = ($j == 6) ? 1 : 0;
				$color = ($activeDays[$i][$j] == true) ? $dayHeaderColor : $dayHeaderColorInactive;
				$this->setFillColor($color);
				$this->cb->Cell($this->dayWidth, $this->dayHeaderHeight, $dayHeader[$i][$j], 1, $ln, 'R', 1);
				$this->dayEventsHeight[$i][$j] = 0;
			}
			// draws day body for every cell of row
			for ($j = 0; $j < 7; $j++) {
				$ln = ($j == 6) ? 1 : 0;
				$color = ($activeDays[$i][$j] == true) ? $dayBodyColor : $dayBodyColorInactive;
				$this->setFillColor($color);
				$this->cb->Cell($this->dayWidth, $this->dayBodyHeight, '', 1, $ln, 'R', 1);
			}
		}
	}


	// draws events in month mode
	public function drawMonthEvents($events, $sizes, $colors, $offset = 1, $offsetLine = 6) {
		$eventColor = $this->convertColor($colors->eventColor);
		$eventBorderColor = $this->convertColor($colors->eventBorderColor);
		$eventTextColor = $this->convertColor($colors->eventTextColor);
		$lineColor = $this->convertColor($colors->lineColor);
		$textColor = $this->convertColor($colors->headerTextColor);
		$this->events = $events;

		$this->cb->setFontSize($sizes->monthEventFontSize);
		$this->setFillColor($eventColor);
		// initial value for footnote number
		$footNum = 0;
		// initial values for checking if in some day footenote has already drawed
		$day = -1;
		$week = -1;

		// circle for drawing every event
		for ($i = 0; $i < count($this->events); $i++) {
			$event = $this->events[$i];
			if ($event['week'] >= count($this->dayHeader)) {
				continue;
			}
			// calculation x-, y- positions, width and height of event
			$x = $this->offsetLeft + $event['day']*$this->dayWidth;
			$y = $this->offsetTop + $this->topScaleHeight + $event['week']*$this->dayHeight + $this->dayHeaderHeight + $this->dayEventsHeight[$event['week']][$event['day']] + $offset;
			if ($event['type'] == 'event_line') {
				$width = $event['width']*$this->dayWidth/125;
				$x += 1.5;
			} else {
				$width = $this->dayWidth - 1;
				$x += 0.5;
			}
			$height = 4;

			$this->cb->setX($x);
			$this->cb->setY($y, false);

			// checks if event can be drawed in day container
			if ($y + $height > ($this->offsetTop + $this->topScaleHeight + ($event['week'] + 1)*$this->dayHeight)) {
				// checks if footenote hasn't already drawed for current day and week values
				if (!(($week == $event['week'])&&($day == $event['day']))) {
					$footNum++;
					$x1Line = $this->offsetLeft + $this->dayWidth*($event['day'] + 1);
					$x2Line = $x1Line - $offsetLine;
					$y1Line = $this->offsetTop + $this->topScaleHeight + $this->dayHeight*($event['week'] + 1) - $offsetLine;
					$y2Line = $this->offsetTop + $this->topScaleHeight + $this->dayHeight*($event['week'] + 1);
					$lineStyle = Array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $lineColor);
					$this->cb->SetLineStyle($lineStyle);
					$this->setTextColor($textColor);
					$points = Array($x1Line, $y1Line, $x2Line, $y2Line, $x1Line, $y2Line);
					$this->cb->Polygon($points, 'DF', $lineStyle, $lineColor, true);
					$textWidth = $this->cb->getStringWidth($footNum);
					$xText = $x1Line - $textWidth - 0.5;
					$yText = $y2Line - 0.6;

					$day = $event['day'];
					$week = $event['week'];
					$this->cb->Text($xText, $yText, $footNum);
				}
				// sets variable which means that this event will print in footenotes
				$this->events[$i]['foot'] = true;
			} else {
				// draws event in current day
				$lineStyle = Array('width' => 0.3, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $eventBorderColor);
				$this->cb->SetLineStyle($lineStyle);
				$text = $this->textWrap($event['text'], $width, $sizes->monthEventFontSize);

				// sets event color
				$color = $event['color'];
				if (($color == false)||($colors->profile !== 'color')) {
					$this->setFillColor($eventColor);
				} else {
					$color = $this->convertColor($color);
					$this->setFillColor($color);
				}
				$text_color = $event['text_color'];
				if (($text_color == false)||($colors->profile !== 'color')) {
					$this->setTextColor($eventTextColor);
				} else {
					$text_color = $this->convertColor($text_color);
					$this->setTextColor($text_color);
				}

				if ($event['type'] == 'event_clear') {
					$this->cb->Cell($width, $height, $text, 0, 0, 'L', 0);
				} else {
					$this->cb->Cell($width, $height, $text, 1, 0, 'L', 1);
				}
				$this->dayEventsHeight[$event['week']][$event['day']] += $height;
				for ($j = $event['day'] + 1; ($j < $event['day'] + ceil($width/$this->dayWidth))&&($j < 7); $j++) {
					if ($event['week'] < count($this->dayHeader)) {
						$this->dayEventsHeight[$event['week']][$j] = $this->dayEventsHeight[$event['week']][$event['day']] + $offset;
					}
				}
				$this->events[$i]['foot'] = false;
			}
		}
		// draws footenotes
		if ($footNum > 0) {
			$this->drawMonthFootenotes($colors, $colors->profile);
		}
	}


	// draws pages with events which can not being showed in day container
	private function drawMonthFootenotes($colors, $mode) {
		$this->cb->addPage();
		$this->drawImgHeader();
		$this->drawImgFooter();
		$eventColor = $this->convertColor($colors->eventColor);
		$eventBorderColor = $this->convertColor($colors->eventBorderColor);
		$eventTextColor = $this->convertColor($colors->eventTextColor);

		// initials x-, y- coordinates
		$x = $this->offsetLeft;
		$y = $this->offsetTop;
		
		// variables for checking if events have the same date
		$week = -1;
		$day = -1;
		$footNum = 1;
		// circle for all events
		for ($i = 0; $i < count($this->events); $i++) {
			// checks not printed events
			if (($this->events[$i]['week'] < count($this->dayHeader))&&($this->events[$i]['foot'] == true)) {
				$event = $this->events[$i];
				$text = $event['text'];
				// checks if it's necessary to print footenote number
				if (!(($week == $event['week'])&&($day == $event['day']))) {
					$textTop = $this->dayHeader[$event['week']][$event['day']].'['.$footNum.']';
					$linesNum = $this->cb->getNumLines($text, $this->colWidth);
					$heightEvent = $linesNum*$this->cb->getFontSize() + $this->cb->getFontSize()*0.5*($linesNum + 1);
					$linesNum = $this->cb->getNumLines($textTop, $this->colWidth);
					$heightHeader = $linesNum*$this->cb->getFontSize() + $this->cb->getFontSize()*0.5*($linesNum + 1);
					// checks if the current column is over
					if ($y + $heightEvent + $heightHeader > $this->cb->getPageHeight() - $this->offsetBottom) {
						$x += $this->colWidth + $this->colOffset;
						$y = $this->offsetTop;
						// checks if it's necessary to add new page
						if ($x + $this->colWidth > $this->cb->getPageWidth() - $this->offsetRight) {
							$this->cb->addPage();
							$this->drawImgHeader();
							$this->drawImgFooter();
							$x = $this->offsetLeft;
							$y = $this->offsetTop;
						}
					}
					
					$this->cb->MultiCell($this->colWidth, 5, $textTop, 0, 'C', 0, 0, $x, $y);
					$y += $this->cb->getLastH();
					$footNum++;
				}

				// checks if currrent column is over
				if ($y + $heightEvent > $this->cb->getPageHeight() - $this->offsetBottom) {
					$x += $this->colWidth + $this->colOffset;
					$y = $this->offsetTop;
					// checks if it's necessary to add new page
					if ($x + $this->colWidth > $this->cb->getPageWidth() - $this->offsetRight) {
						$this->cb->addPage();
						$x = $this->offsetLeft;
						$y = $this->offsetTop;
					}
					$textTop = $this->dayHeader[$event['week']][$event['day']].'['.($footNum - 1).']';
					$this->cb->MultiCell($this->colWidth, 5, $textTop, 0, 'C', 0, 0, $x, $y);
					$y += $this->cb->getLastH();
				}
				// draws event
				$color = $event['color'];
				if (($color == false)||(($mode !== 'color')&&($mode !== 'fullcolor'))) {
					$this->setFillColor($eventColor);
				} else {
					$color = $this->convertColor($color);
					$this->setFillColor($color);
				}
				$text_color = $event['text_color'];
				if (($text_color == false)||(($mode !== 'color')&&($mode !== 'fullcolor'))) {
					$this->setTextColor($eventTextColor);
				} else {
					$text_color = $this->convertColor($text_color);
					$this->setTextColor($text_color);
				}
				$this->cb->MultiCell($this->colWidth, 5, $text, 1, 'L', 1, 0, $x, $y);
				$y += $this->cb->getLastH();
				$week = $event['week'];
				$day = $event['day'];
			}
		}
	}


	// creates array with active/inactive option for every day
	private function setActiveDays($dayHeader) {
		if ($dayHeader[0][0] == '1') {
			// month grid starts from first day of month
			$flag = true;
			$flagCount = 1;
		} else {
			// month grid starts from day of previous month
			$flag = false;
			$flagCount = 0;
		}
		$activeDays = Array();
		$prevDay = (int) $dayHeader[0][0];
		
		for ($i = 0; $i < count($dayHeader); $i++) {
			for ($j = 0; $j < count($dayHeader[$i]); $j++) {
				// check if previous day value is less then current day value
				if (((int) $dayHeader[$i][$j] < $prevDay)&&($flagCount < 2)) {
					$flag = !$flag;
					$flagCount++;
				}
				$activeDays[$i][$j] = $flag;
				$prevDay = (int) $dayHeader[$i][$j];
			}
		}
		return $activeDays;
	}


	//
	public function drawYear($orientation, $yearValues, $events, $sizes, $colors) {
		$this->cb->AddPage($orientation);
		$bgColor = $this->convertColor($colors->bgColor);
		$lineColor = $this->convertColor($colors->lineColor);
		$dayColor = $this->convertColor($colors->yearDayColor);
		$dayColorInactive = $this->convertColor($colors->yearDayColorInactive);
		$eventColor = $this->convertColor($colors->eventColor);
		$eventTextColor = $this->convertColor($colors->eventTextColor);
		$headerTextColor = $this->convertColor($colors->headerTextColor);
		$headerLineColor = $this->convertColor($colors->headerLineColor);
		$yearTextColor = $this->convertColor($colors->yearTextColor);
		$yearTextColorInactive = $this->convertColor($colors->yearTextColorInactive);
		$this->yearValues = $yearValues;
		$this->events = $events;
		$this->headerHeight = $sizes->yearMonthHeaderHeight;

		// offset between monthes
		$offset = 5;

		// sets line style and color
		$lineStyle = Array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $headerLineColor);
		$this->cb->SetLineStyle($lineStyle);
		$this->setFillColor($bgColor);

		// calculates month width and height
		$monthWidth = ($this->cb->getPageWidth() - $this->offsetLeft - $this->offsetRight - $offset*3)/4;
		$monthHeight = ($this->cb->getPageHeight() - $this->offsetTop - $this->offsetBottom - $offset*2)/3;

		// circles for drawing all monthes
		for ($i = 0; $i < 3; $i++) {
			for ($j = 0; $j < 4; $j++) {
				$lineStyle['color'] = $headerLineColor;
				$this->cb->SetLineStyle($lineStyle);
				$this->setFillColor($bgColor);
				$this->setTextColor($headerTextColor);
				$num = $i*4 + $j;
				// calculates x- and y-coordinates of current month
				$x = $this->offsetLeft + $j*$monthWidth + $offset*($j);
				$y = $this->offsetTop + $i*$monthHeight + $offset*($i);
				$this->cb->setX($x);
				$this->cb->setY($y, false);
				$this->cb->SetFontSize($sizes->yearHeaderFontSize);
				// draws name of month
				$border = ($colors->profile === 'bw') ? 1 : 0;
				$this->cb->Cell($monthWidth, $this->headerHeight, $this->yearValues[$num]['label'], $border, 1, 'C', 1);
				// calculates day width and height
				$dayWidth = $monthWidth/7;
				$dayHeight = ($monthHeight - $sizes->yearMonthHeaderHeight)/7;
				$y += $sizes->yearMonthHeaderHeight;
				// create array of day in current month
				$activeDays = $this->setActiveDays($this->yearValues[$num]['rows']);

				// draws day names
				$border = ($colors->profile === 'bw') ? 1 : 0;
				for ($k = 0; $k < count($this->yearValues[$num]['columns']); $k++) {
					$this->cb->setX($x);
					$this->cb->setY($y, false);
					$this->cb->Cell($dayWidth, $dayHeight, $this->yearValues[$num]['columns'][$k], $border, 1, 'C', 1);
					if ($k > 0 && $colors->profile !== 'bw') {
						$this->cb->Line($x, $y, $x, $y + $dayHeight, $lineStyle);
					}
					$x += $dayWidth;
				}
				$x -= $dayWidth*7;
				$this->cb->Line($x, $y, $x + $dayWidth*7, $y, $lineStyle);
				$y += $dayHeight;
				$this->cb->SetFontSize($sizes->yearFontSize);
				
				$lineStyle['color'] = $lineColor;
				$this->cb->SetLineStyle($lineStyle);
				// draws days
				for ($k = 0; $k < count($this->yearValues[$num]['rows']); $k++) {
					for ($l = 0; $l < count($this->yearValues[$num]['rows'][$k]); $l++) {
						$this->cb->setX($x);
						$this->cb->setY($y, false);
						// checks if day of current month
						if ($activeDays[$k][$l] == true) {
							// checks if the day have events
							if (isset($this->events[$num.'_'.$k.'_'.$l])) {
								$ev = $this->events[$num.'_'.$k.'_'.$l];
								$color = $this->convertColor($ev['color']);
								if (($color == 'transparent')||($colors->profile !== 'color')) {
									$this->setFillColor($eventColor);
								} else {
									$this->setFillColor($color);
								}
								$color = $this->convertColor($ev['text_color']);
								if (($color == 'transparent')||($colors->profile !== 'color')) {
									$this->setTextColor($eventTextColor);
								} else {
									$this->setTextColor($color);
								}
							} else {
								$this->setFillColor($dayColor);
								$this->setTextColor($yearTextColor);
							}
							$text = $this->yearValues[$num]['rows'][$k][$l];
						} else {
							// it's the day of previous or next month
							$this->setFillColor($dayColorInactive);
							$this->setTextColor($yearTextColorInactive);
							$text = $this->yearValues[$num]['rows'][$k][$l];
						}
						// draw day
						$this->cb->Cell($dayWidth, $dayHeight, $text, 1, 1, 'C', 1);
						$x += $dayWidth;
					}
					$y += $dayHeight;
					$x -= $dayWidth*7;
				}
			}
		}
		$this->drawImgHeader();
		$this->drawImgFooter();
	}


	// draws agenda mode
	public function drawAgenda($agendaHeader, $events, $sizes, $colors, $today) {
		$bgColor = $this->convertColor($colors->bgColor);
		$lineColor = $this->convertColor($colors->lineColor);
		$headerLineColor = $this->convertColor($colors->headerLineColor);
		$headerTextColor = $this->convertColor($colors->headerTextColor);
		$scaleColorOne = $this->convertColor($colors->scaleColorOne);
		$scaleColorTwo = $this->convertColor($colors->scaleColorTwo);
		$eventTextColor = $this->convertColor($colors->yearTextColor);
		$this->cb->addPage();
		$this->drawToday($today, $sizes, $colors);
		$this->cb->setFontSize($sizes->agendaFontSize);
		$this->setTextColor($headerTextColor);
		$this->rowHeight = $sizes->agendaRowHeight;

		// sets column 
		$timeColWidth = 50;
		$dscColWidth = $this->cb->getPageWidth() - $this->offsetLeft - $this->offsetRight - $timeColWidth;
		$lineStyle = Array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $headerLineColor);
		$this->cb->SetLineStyle($lineStyle);
		$this->setFillColor($bgColor);
		$this->setTextColor($headerTextColor);

		$this->cb->setX($this->offsetLeft);

		$border = ($colors->profile === 'bw') ? 1 : 0;
		$text = $this->textWrap($agendaHeader[0], $timeColWidth, $sizes->agendaFontSize);
		$this->cb->Cell($timeColWidth, $sizes->agendaRowHeight, $text, $border, 0, 'C', 1);

		$border = ($colors->profile === 'bw') ? 1 : 'L';
		$text = $this->textWrap($agendaHeader[1], $dscColWidth, $sizes->agendaFontSize);
		$this->cb->Cell($dscColWidth, $sizes->agendaRowHeight, $text, $border, 1, 'C', 1);

		$this->drawImgHeader();
		$this->drawImgFooter();
		$lineStyle['color'] = $lineColor;
		$this->cb->SetLineStyle($lineStyle);

		for ($i = 0; $i < count($events); $i++) {
			// checks if the page is over
			if ($this->cb->getY() + $sizes->agendaRowHeight > $this->cb->getPageHeight() - $this->offsetBottom) {
				// add new page, draws page num, header and today label
				$this->cb->setPrintFooter(true);
				$this->cb->addPage();
				$this->drawImgHeader();
				$this->drawImgFooter();
				$this->drawToday($today, $sizes, $colors);
				$this->cb->setFontSize($sizes->agendaFontSize);
				$this->setFillColor($bgColor);
				$this->setTextColor($headerTextColor);
				$this->cb->setX($this->offsetLeft);
				$border = ($colors->profile === 'bw') ? 1 : 0;
				$text = $this->textWrap($agendaHeader[0], $timeColWidth, $sizes->agendaFontSize);
				$this->cb->Cell($timeColWidth, $sizes->agendaRowHeight, $text, $border, 0, 'C', 1);
				
				$border = ($colors->profile === 'bw') ? 1 : 'L';
				$text = $this->textWrap($agendaHeader[1], $dscColWidth, $sizes->agendaFontSize);
				$this->cb->Cell($dscColWidth, $sizes->agendaRowHeight, $text, $border, 1, 'C', 1);
			}
			// selects scale color
			if ($i%2 == 0) {
				$this->setFillColor($scaleColorOne);
			} else {
				$this->setFillColor($scaleColorTwo);
			}
			$this->setTextColor($eventTextColor);
			// draws time cell
			$border = ($colors->profile === 'bw') ? 1 : 'L';
			$text = $this->textWrap($events[$i]['head'], $timeColWidth, $sizes->agendaFontSize);
			$this->cb->Cell($timeColWidth, $sizes->agendaRowHeight, $text, $border, 0, 'C', 1);
			// draws description cell
			$border = ($colors->profile === 'bw') ? 1 : 'LR';
			$text = $this->textWrap($events[$i]['body'], $dscColWidth, $sizes->agendaFontSize);
			$this->cb->Cell($dscColWidth, $sizes->agendaRowHeight, $text, $border, 1, 'L', 1);
		}
	}


	// draws headers and grid for day and week mode
	public function drawDayWeekHeader($columnHeader, $rowHeader, $sizes, $colors, $orientation = 'P', $multiday = Array()) {
		$this->cb->addPage($orientation);
		$bgColor = $this->convertColor($colors->bgColor);
		$lineColor = $this->convertColor($colors->lineColor);
		$scaleColorOne = $this->convertColor($colors->scaleColorOne);
		$scaleColorTwo = $this->convertColor($colors->scaleColorTwo);
		$headerTextColor = $this->convertColor($colors->headerTextColor);
		$headerLineColor = $this->convertColor($colors->headerLineColor);
		$multidayBgColor = $this->convertColor($colors->multidayBgColor);

		$this->multidayLineHeight = $sizes->multidayLineHeight;


		$this->leftWidth = $sizes->dayLeftWidth;
		$this->topHeight = $sizes->dayTopHeight;

		$lineStyle = Array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $headerLineColor);
		$this->cb->SetLineStyle($lineStyle);
		$this->setFillColor($bgColor);
		$this->setTextColor($headerTextColor);

		// calculates cell width and height
		$this->colWidth = ($this->cb->getPageWidth() - $this->offsetLeft - $this->offsetRight - $sizes->dayLeftWidth)/count($columnHeader);


		// draws scales on top
		$this->cb->setX($this->offsetLeft + $sizes->dayLeftWidth);
		$this->cb->setY($this->offsetTop, false);
		$this->cb->setFontSize($sizes->dayHeaderFontSize);
		for ($i = 0; $i < count($columnHeader); $i++) {
			$Ln = ($i == count($columnHeader) - 1) ? 1 : 0;
			$text = $this->textWrap($columnHeader[$i], $this->colWidth, $sizes->dayHeaderFontSize);
			$border = ($i > 0) ? 'L' : 0;
			$this->cb->Cell($this->colWidth, $sizes->dayTopHeight, $text, $border, $Ln, 'C', 1);
		}

		// draws multiday scales
		if (count($multiday) > 0) {
			$multiTop = $this->getMultidayHeight($multiday, $sizes->multidayLineHeight);
			$this->cb->setX($this->offsetLeft);
			$this->cb->setY($this->offsetTop + $sizes->dayTopHeight, false);
			$this->setFillColor($multidayBgColor);
			$this->cb->Cell($sizes->dayLeftWidth, $multiTop, '', 'R', 0, 'C', 1);
			$this->cb->Cell($this->colWidth*count($columnHeader), $multiTop, '', 0, $Ln, 'C', 1);
			$this->topHeight = $sizes->dayTopHeight += $multiTop;
			$this->multiHeight = $multiTop;
		}

		// draws scales on left
		$this->colHeight = ($this->cb->getPageHeight() - $this->offsetTop - $this->offsetBottom - $sizes->dayTopHeight)/count($rowHeader);
		$this->cb->setY($this->offsetTop + $this->topHeight, false);
		$this->setFillColor($bgColor);
		$this->cb->setFontSize($sizes->dayScaleFontSize);
		for ($i = 0; $i < count($rowHeader); $i++) {
			$this->cb->Cell($sizes->dayLeftWidth, $this->colHeight, $rowHeader[$i], 'TR', 1, 'C', 1);
		}

		// circle for drawing grid
		$this->cb->setX($this->offsetLeft + $sizes->dayLeftWidth);
		$this->cb->setY($this->offsetTop + $sizes->dayTopHeight, false);
		for ($i = 0; $i < count($rowHeader); $i++) {
			// draws white line
			$this->setFillColor($scaleColorOne);
			$border = (($i == 0)||($colors->profile == 'bw')) ? 'LRT' : 'LR';
			$this->cb->Cell($this->colWidth*count($columnHeader), $this->colHeight/2, '', 0, 1, 'C', 1);
			// draws blue line
			$this->cb->setX($this->offsetLeft + $sizes->dayLeftWidth);
			$this->setFillColor($scaleColorTwo);
			$border = (($i == count($rowHeader) - 1)||($colors->profile == 'bw')) ? 'LRB' : 'LR';
			$this->cb->Cell($this->colWidth*count($columnHeader), $this->colHeight/2, '', 0, 1, 'C', 1);
			$this->cb->setX($this->offsetLeft + $sizes->dayLeftWidth);
		}
		// draws lines delemiters between days if it's week mode
		if (count($columnHeader > 1)) {
			$lineStyle['color'] = $lineColor;
			for ($i = 0; $i < count($columnHeader) - 1; $i++) {
				$x = $this->offsetLeft + $sizes->dayLeftWidth + ($i + 1)*$this->colWidth;
				$y1 = $this->offsetTop + $sizes->dayTopHeight;
				$y2 = $this->cb->getPageHeight() - $this->offsetBottom;
				$this->cb->line($x, $y1, $x, $y2, $lineStyle);
			}
		}

		$this->drawImgHeader();
		$this->drawImgFooter();
	}

	public function bwBorder($colors) {
		$lineColor = $this->convertColor($colors->lineColor);
		$lineStyle = Array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $lineColor);
		$x = $this->offsetLeft + $this->leftWidth;
		$y = $this->offsetTop;
		$pageWidth = $this->cb->getPageWidth() - $this->offsetLeft - $this->offsetRight - $this->leftWidth;
		$pageHeight = $this->cb->getPageHeight() - $this->offsetTop - $this->offsetBottom - $this->topHeight;
		$this->cb->Line($x, $y, $x + $pageWidth, $y, $lineStyle);

		if (isset($this->multiHeight) && $this->multiHeight > 0) {
			$y = $this->offsetTop + $this->topHeight - $this->multiHeight;
			$this->cb->Line($x, $y, $x + $pageWidth, $y, $lineStyle);
		}
		
		$x = $this->offsetLeft;
		$y = $this->offsetTop + $this->topHeight;
		$this->cb->Line($x, $y, $x + $this->leftWidth + $pageWidth, $y, $lineStyle);

		$y = $this->offsetTop + $this->topHeight + $pageHeight;
		$this->cb->Line($x, $y, $x + $this->leftWidth + $pageWidth, $y, $lineStyle);

		$x = $this->offsetLeft;
		$y = $this->offsetTop + $this->topHeight;
		$this->cb->Line($x, $y, $x, $y + $pageHeight, $lineStyle);

		$x = $this->offsetLeft + $this->leftWidth;
		$y = $this->offsetTop;
		$this->cb->Line($x, $y, $x, $y + $this->topHeight, $lineStyle);

		$x = $this->offsetLeft + $this->leftWidth + $pageWidth;
		$y = $this->offsetTop;
		$this->cb->Line($x, $y, $x, $y + $this->topHeight + $pageHeight, $lineStyle);
	}

	public function bwMonthBorder($colors) {
		$lineColor = $this->convertColor($colors->lineColor);
		$lineStyle = Array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $lineColor);
		$x = $this->offsetLeft;
		$y = $this->offsetTop;
		$pageWidth = $this->cb->getPageWidth() - $this->offsetLeft - $this->offsetRight;
		$pageHeight = $this->cb->getPageHeight() - $this->offsetTop - $this->offsetBottom - $this->topScaleHeight;
		$this->cb->Line($x, $y, $x + $pageWidth, $y, $lineStyle);

		$this->cb->Line($x, $y, $x, $y + $this->topScaleHeight, $lineStyle);
		$x += $pageWidth;
		$this->cb->Line($x, $y, $x, $y + $this->topScaleHeight, $lineStyle);
	}

	
	public function getMultidayHeight($events, $lineHeight) {
		$scheme = Array(0);
		for ($i = 0; $i < count($events); $i++) {
			$start = $events[$i]['day'];
			$length = $events[$i]['len'];
			for ($j = $start; $j < $start + $length; $j++) {
				if (!isset($scheme[$j]))
					$scheme[$j] = 0;
				$scheme[$j]++;
			}
		}
		$result = max($scheme);
		return $result*$lineHeight;
	}


	// draws headers and grid for timeline mode
	public function drawTimelineHeader($columnHeader, $rowHeader, $cellColors, $sizes, $colors, $orientation = 'P') {
		$this->cb->addPage($orientation);
		$bgColor = $this->convertColor($colors->bgColor);
		$lineColor = $this->convertColor($colors->lineColor);
		$cellColor = $this->convertColor($colors->timelineCellBg);
		$headerTextColor = $this->convertColor($colors->headerTextColor);
		$headerLineColor = $this->convertColor($colors->headerLineColor);
		$this->leftWidth = $sizes->dayLeftWidth;
		$this->topHeight = $sizes->dayTopHeight;
		$this->columnHeader = $columnHeader;
		$this->rowHeader = $rowHeader;
		$this->timelineSectionsY = 0;
		$this->dayScaleFontSize = $sizes->dayScaleFontSize;

		$this->lineStyle = Array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $headerLineColor);
		$this->cb->SetLineStyle($this->lineStyle);
		$this->setFillColor($bgColor);
		$this->setTextColor($headerTextColor);

		// calculates cell width and height
		$this->colWidth = ($this->cb->getPageWidth() - $this->offsetLeft - $this->offsetRight - $sizes->dayLeftWidth)/count($columnHeader);
		$this->colHeight = ($this->cb->getPageHeight() + $this->headerImgHeight + $this->footerImgHeight - $this->offsetTop - $this->offsetBottom - $sizes->dayTopHeight)/count($rowHeader);

		$this->cb->setX($this->offsetLeft + $sizes->dayLeftWidth);
		$this->cb->setY($this->offsetTop, false);

		// draws scales on top
		$this->cb->setFontSize($sizes->dayHeaderFontSize);
		for ($i = 0; $i < count($columnHeader); $i++) {
			$Ln = ($i == count($columnHeader) - 1) ? 1 : 0;
			$border = ($i > 0) ? 'L' : 0;
			$this->cb->Cell($this->colWidth, $sizes->dayTopHeight, $columnHeader[$i], $border, $Ln, 'C', 1);
		}
		$this->drawImgHeader();
		$this->drawImgFooter();
	}


//	public function drawTimelineSection($name, $height, $cellColors, $events, $eventHeight, $colors, $timelineEventFontSize, $mode) {
	public function drawTimelineSection($name, $height, $cellColors, $events, $sizes, $colors) {
		$bgColor = $this->convertColor($colors->bgColor);
		$lineColor = $this->convertColor($colors->lineColor);
		$cellColor = $this->convertColor($colors->timelineCellBg);
		$headerTextColor = $this->convertColor($colors->headerTextColor);
		$headerLineColor = $this->convertColor($colors->headerLineColor);
		
		$height = $this->colHeight*$height/100;
		$diff_height = 0;
		if (($this->offsetTop + $this->topHeight + $this->timelineSectionsY + $height) > ($this->cb->getPageHeight() - $this->offsetBottom)) {
			$diff_height = -($this->cb->getPageHeight() - $this->offsetBottom - $this->offsetTop - $this->topHeight - $this->timelineSectionsY - $height);
			$height -= $diff_height;
			if ($height < $sizes->timelineEventHeight) {
				return Array('height' => $height + $diff_height, 'events' => $events);
			}
			if ($diff_height < $sizes->timelineEventHeight) {
				$diff_height = 0;
			} elseif ($diff_height < $sizes->timelineEventHeight*2.5) {
				$diff_height = $sizes->timelineEventHeight*2.5;
			}
		}

		$lineStyle = Array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $headerLineColor);
		$this->cb->SetLineStyle($lineStyle);
		$this->setFillColor($bgColor);
		$this->cb->setFontSize($this->dayScaleFontSize);

		$this->cb->setX($this->offsetLeft);
		$this->cb->setY($this->offsetTop + $this->topHeight + $this->timelineSectionsY, false);
		$this->setFillColor($bgColor);
		$this->setTextColor($headerTextColor);
		$this->cb->Cell($this->leftWidth, $height, $this->textWrap($name, $this->leftWidth, $this->dayScaleFontSize), 'T', 0, 'C', 1);
		$this->cb->setX($this->offsetLeft + $this->leftWidth);

		for ($j = 0; $j < count($this->columnHeader); $j++) {
			if ($cellColors[$j] != false) {
				$bgColor = $this->convertColor($cellColors[$j]);
				$this->setFillColor($bgColor);
			} else {
				$this->setFillColor($cellColor);
			}
			$border = 1;
			$lineStyle['color'] = $lineColor;
			$this->cb->SetLineStyle($lineStyle);
			$x = $this->cb->getX();
			$y = $this->cb->getY();
			$this->cb->Cell($this->colWidth, $height, '', $border, 0, 'C', 1);

			$this->cb->setX($this->offsetLeft + $this->leftWidth + $this->colWidth*($j + 1));
		}
		$on_next_page = $this->drawTimelineEvents($events, $sizes, $colors, $height);
		if (count($on_next_page) == 0) {
			$diff_height = 0;
		}
		$this->timelineSectionsY += $height;
		if ($diff_height != 0)
			return Array('height' => $this->getTimelineRelativeHeight($diff_height + $sizes->timelineEventHeight), 'events' => $on_next_page);
		else
			return Array('height' => 0, 'events' => $on_next_page);
	}


	public function getTimelineRelativeHeight($absHeight) {
		$relHeight = 100*$absHeight/$this->colHeight;
		return $relHeight;
	}


	// draws events in day and week modes
	public function drawDayWeekEvents($events, $multiday, $sizes, $colors) {
		$eventColor = $this->convertColor($colors->eventColor);
		$eventBorderColor = $this->convertColor($colors->eventBorderColor);
		$eventTextColor = $this->convertColor($colors->eventTextColor);

		$lineStyle = Array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $eventBorderColor);
		$this->cb->setLineStyle($lineStyle);

		// circle for every event
		for ($i = 0; $i < count($events); $i++) {
			$event = $events[$i];
			// sets event color
			$color = $this->processEventColor($event['color']);
			if (($color == 'transparent')||($colors->profile !== 'color')) {
				$this->setFillColor($eventColor);
			} else {
				$color = $this->convertColor($color);
				$this->setFillColor($color);
			}

			// calculates x-, y-coordinates, width and height of event
			$x = $this->offsetLeft + $sizes->dayLeftWidth + $event['x']*$this->colWidth/100;
			$y = $this->offsetTop + $sizes->dayTopHeight + $event['y']*$this->colHeight/100;
			$width = $event['width']*$this->colWidth/100;
			$height = $event['height']*$this->colHeight/100;

			$height = ($height < 8.42) ? 8.42 : $height;
			if ($y + $height > ($this->cb->getPageHeight() - $this->offsetBottom)) {
				$height = $this->cb->getPageHeight() - $y - $this->offsetBottom - 0.2;
			}
			$height_start = $height;

			$color = $event['color'];
			if (($color == false)||($colors->profile !== 'color')) {
				$color = $eventColor;
			} else {
				$color = $this->convertColor($color);
			}

			$text_color = $event['text_color'];
			if (($text_color == false)||($colors->profile !== 'color')) {
				$this->setTextColor($eventTextColor);
			} else {
				$text_color = $this->convertColor($text_color);
				$this->setTextColor($text_color);
			}

			// draws event container
			$this->cb->RoundedRect($x, $y, $width, $height, 1, '1111', 'DF', array(), $color);
			$this->cb->setX($x);
			$this->cb->setY($y, false);
			// draws event header
			$this->cb->setFontSize($sizes->dayEventHeaderFontSize);
			$text = $this->textWrap($event['head'], $width, $sizes->dayEventHeaderFontSize);
			$this->cb->Cell($width, $sizes->monthEventHeaderHeight, $text, 0, 0, 'C', 0);
			$y += $sizes->monthEventHeaderHeight;
			$height -= $sizes->monthEventHeaderHeight;
			// draws event body
			$this->cb->setFontSize($sizes->dayEventBodyFontSize);
			$this->cb->MultiCell($width, $height, $event['body'], 0, 'L', 0, 0, $x, $y,  true, 0, false, true, $height);

			// draws separate line
			$this->cb->line($x, $y, $x + $width, $y);
		}

		$scheme = Array();
		$this->cb->setFontSize($sizes->dayEventHeaderFontSize);
		for ($i = 0; $i < count($multiday); $i++) {
			$event = $multiday[$i];
			$day = $event['day'];
			$len = $event['len'];
			$text = $event['body'];
			$width = $len*$this->colWidth - 0.4;
			$height = $this->multidayLineHeight - 1;
			$this->cb->setX(0.2 + $this->offsetLeft + $this->leftWidth + $day*$this->colWidth);
			$topOffset = isset($scheme[$day]) ? $scheme[$day]*$this->multidayLineHeight : 0;
			$this->cb->setY(0.2 + $this->offsetTop + $this->topHeight - $this->multiHeight + $topOffset, false);

			$color = $event['color'];
			if (($color == false)||(($colors->profile !== 'color')&&($colors->profile !== 'fullcolor'))) {
				$color = $eventColor;
			} else {
				$color = $this->convertColor($color);
			}
			$this->setFillColor($color);

			$text_color = $event['text_color'];
			if (($text_color == false)||($colors->profile !== 'color')) {
				$this->setTextColor($eventTextColor);
			} else {
				$text_color = $this->convertColor($text_color);
				$this->setTextColor($text_color);
			}
			
			$this->cb->Cell($width, $height, $text, 1, 0, 'L', 1);
			for ($j = $day; $j < $day + $len; $j++) {
				if (!isset($scheme[$j]))
					$scheme[$j] = 0;
				$scheme[$j]++;
			}
		}
	}

	// draws timeline events
	public function drawTimelineEvents($events, $sizes, $colors, $sectionHeight, $offset = 0.8, $offsetLine = 6) {
		$eventColor = $this->convertColor($colors->eventColor);
		$eventBorderColor = $this->convertColor($colors->eventBorderColor);
		$eventTextColor = $this->convertColor($colors->eventTextColor);
		$this->timelineEventFontSize = $sizes->monthEventFontSize;

		$this->cb->setFontSize($sizes->monthEventFontSize);
		$lineStyle = Array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $eventBorderColor);
		$this->cb->setLineStyle($lineStyle);
		$on_next_page = Array();

		for ($i = 0; $i < count($events); $i++) {
			$event = $events[$i];
			$x = $this->offsetLeft + $this->leftWidth + $this->colWidth*$event['x']/100;
			$y = $this->offsetTop + $this->topHeight + $this->timelineSectionsY + $this->colHeight*$event['y']/100;
			$width = $this->colWidth*$event['width']/100;
			$height = $sizes->timelineEventHeight;
			$text = $event['body'];

			$this->cb->setX($x);
			$this->cb->setY($y, false);

			$color = $event['color'];
			if (($color == false)||($colors->profile !== 'color')) {
				$this->setFillColor($eventColor);
			} else {
				$color = $this->convertColor($color);
				$this->setFillColor($color);
			}
			$text_color = $event['text_color'];
			if (($text_color == false)||($colors->profile !== 'color')) {
				$this->setTextColor($eventTextColor);
			} else {
				$text_color = $this->convertColor($text_color);
				$this->setTextColor($text_color);
			}
			if (($y + $height) > ($this->cb->getPageHeight() - $this->offsetBottom)) {
				if (count($on_next_page) == 0)
					$on_next_page_start_height = $event['y'] - 1;
				$event['y'] -= $on_next_page_start_height;
				$on_next_page[] = $event;
			} else {
				$text = $this->textWrap($text, $width, $sizes->monthEventFontSize);
				$this->cb->Cell($width, $height, $text, 1, 0, 'L', 1);
			}
		}
		return $on_next_page;
	}

	public function drawMatrixEvents($events, $sizes, $colors) {
		$eventColor = $this->convertColor($colors->matrixEventColor);
		$eventBorderColor = $this->convertColor($colors->eventBorderColor);
		$eventTextColor = $this->convertColor($colors->eventTextColor);
		$lineColor = $this->convertColor($colors->lineColor);
		$bgColor = $this->convertColor($colors->bgColor);
		$headerTextColor = $this->convertColor($colors->headerTextColor);
		$headerLineColor = $this->convertColor($colors->headerLineColor);
		$this->timelineEventFontSize = $sizes->monthEventFontSize;
		$lineStyle = Array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $headerLineColor);

		for ($i = 0; $i < count($this->rowHeader); $i++) {
			for ($j = 0; $j <= count($this->columnHeader); $j++) {
				$event = $events[$i*(count($this->columnHeader) + 1) + $j];
				$x = $this->offsetLeft + max($j - 1, 0)*$this->colWidth + ($j !== 0)*$this->leftWidth;
				$y = $this->offsetTop + $this->topHeight + $i*$this->colHeight;
				$width = $this->colWidth;
				if ($j === 0) {
					$width = $this->leftWidth;
				}
				$this->cb->setX($x);
				$this->cb->setY($y, false);

				// set colors
				if ($j !== 0) {
					$border = ($i === 0) ? 'LBR' : 1;
					$lineStyle['color'] = $lineColor;
					$this->cb->SetLineStyle($lineStyle);
					$color = $event['color'];
					if (($color == false)||(($colors->profile !== 'color'))) {
						$this->setFillColor($eventColor);
					} else {
						$color = $this->convertColor($color);
						$this->setFillColor($color);
					}
					$text_color = $event['text_color'];
					if (($text_color == false)||($colors->profile !== 'color')) {
						$this->setTextColor($eventTextColor);
					} else {
						$text_color = $this->convertColor($text_color);
						$this->setTextColor($text_color);
					}
				} else {
					$border = ($i === 0) ? 0 : 'T';
					$lineStyle['color'] = $headerLineColor;
					$this->cb->SetLineStyle($lineStyle);
					$this->setFillColor($bgColor);
					$this->cb->setFontSize($this->dayScaleFontSize);
					$this->setTextColor($headerTextColor);
				}

				$this->cb->Cell($width, $this->colHeight, $event['body'], $border, 0, 'C', 1);
			}
		}
	}

	
	
	public function drawWeekAgendaContainer($dayHeader, $sizes, $colors, $orientation) {
		$this->cb->addPage();
		$lineColor = $this->convertColor($colors->lineColor);
		$headerLineColor = $this->convertColor($colors->headerLineColor);
		$headerColor = $this->convertColor($colors->bgColor);
		$lineStyle = Array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $lineColor);
		$this->cb->setLineStyle($lineStyle);
		$this->cb->SetFontSize($sizes->monthHeaderFontSize);

		$this->contWidth = $width = ($this->cb->getPageWidth() - $this->offsetLeft - $this->offsetRight)/2;
		$this->contHeight = $height = ($this->cb->getPageHeight() - $this->offsetTop - $this->offsetBottom)/3;
		$this->setFillColor($headerColor);
		for ($i = 0; $i < 3; $i++) {
			$x = $this->offsetLeft;
			$y = $this->offsetTop + $height*$i;
			$this->drawWeekAgendaDay($sizes, $colors, $dayHeader[$i], $width, $height, $x, $y);
		}

		for ($i = 0; $i < 2; $i++) {
			$x = $this->offsetLeft + $width;
			$y = $this->offsetTop + $height*$i;
			$this->drawWeekAgendaDay($sizes, $colors, $dayHeader[$i + 3], $width, $height, $x, $y);
		}

		$tall_height = $height/2;
		for ($i = 0; $i < 2; $i++) {
			$x = $this->offsetLeft + $width;
			$y = $this->offsetTop + $height*2 + $tall_height*$i;
			$this->drawWeekAgendaDay($sizes, $colors, $dayHeader[$i + 5], $width, $tall_height, $x, $y);
		}

		$x = $this->offsetLeft + $width;
		for ($i = 0; $i < 3; $i++) {
			$y = $this->offsetTop + $height*$i;
			$lineStyle['color'] = $headerLineColor;
			$this->cb->Line($x, $y, $x, $y + $sizes->monthHeaderHeight, $lineStyle);
		}
		$this->drawImgHeader();
		$this->drawImgFooter();
	}

	public function drawWeekAgendaDay($sizes, $colors, $name, $width, $height, $x, $y, $border = 1) {
		$this->cb->setX($x);
		$this->cb->setY($y, false);
		$this->cb->Cell($width, $height, '', 1, 0, 'C', 0);
		$this->cb->setX($x);
		$this->cb->setY($y, false);
		$this->cb->Cell($width, $sizes->monthHeaderHeight, $name, 1, 0, 'C', 1);
	}

	public function drawWeekAgendaEvents($events, $sizes, $colors) {
		$lineColor = $this->convertColor($colors->lineColor);
		$lineStyle = Array('width' => 0.1, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => $lineColor);
		$this->cb->setLineStyle($lineStyle);
		$this->cb->setFontSize($sizes->monthHeaderFontSize);
		$offsets = array_fill(0, 7, 0);
		$rest = Array();
		
		
		for ($i = 0; $i < count($events); $i++) {
			$event = $events[$i];
			$day = $event['day'];
			$cont_height = ($day < 5) ? $this->contHeight : $this->contHeight/2;
			$cont_width = $this->contWidth;
			switch ($day) {
				case 0:
				case 2:
				case 4:
					$x = $this->offsetLeft;
					break;
				default:
					$x = $this->offsetLeft + $cont_width;
					break;
			}
			$cont_start_y = $this->offsetTop + floor($day/2)*$this->contHeight - ($day > 5 ? $cont_height : 0);
			$offset = $offsets[$day]*$sizes->weekAgendaEventHeight;
			$y = $cont_start_y + $sizes->monthHeaderHeight + $offset;

			if ($cont_start_y + $cont_height < $y + $sizes->weekAgendaEventHeight) {
				$rest[] = $event;
				continue;
			}

			$this->cb->SetX($x);
			$this->cb->SetY($y, false);
			$this->cb->Cell($cont_width, $sizes->weekAgendaEventHeight, $event['body'], 'B', 0, 'L');
			$offsets[$day]++;
		}
		return $rest;
	}

	// draws header image
	public function drawImgHeader() {
		if ($this->headerImg == false) {
			return true;
		}
		$y = $this->offsetTop - $this->headerImgHeight;
		$headerWidth = $this->cb->getPageWidth() - $this->offsetLeft - $this->offsetRight;
		$x = $this->offsetLeft;
		$this->cb->Image($this->headerImg, $x, $y, $headerWidth, $this->headerImgHeight, 'PNG', '', 'M', false, 96, 'L', false, false, 0, false, false);
		$this->cb->setY($this->offsetTop);
	}


	// draws footer image
	public function drawImgFooter() {
		if ($this->footerImg == false) {
			return true;
		}
		$footerWidth = $this->cb->getPageWidth() - $this->offsetLeft - $this->offsetRight;
		$x = $this->offsetLeft;
		$y = $this->cb->getPageHeight() - $this->offsetBottom;
		$this->cb->Image($this->footerImg, $x, $y, $footerWidth, $this->footerImgHeight, 'PNG', '', 'M', false, 96, 'L', false, false, 0, false, false);
		$this->cb->setY($this->offsetTop);
	}


	// draws today value
	public function drawToday($today, $sizes, $colors) {
		$this->todayFontSize = $sizes->todayFontSize;
		$this->cb->setFontSize($sizes->todayFontSize);
		$textColor = $this->convertColor($colors->todayTextColor);
		$this->setTextColor($textColor);
		$this->cb->Text($this->offsetLeft + 2, $this->offsetTop - 7, $today);
	}


	// wraps text accordigly getted width and font size
	private function textWrap($text, $width, $fontSize) {
		$strWidth = $this->cb->GetStringWidth($text, '', '', $fontSize, false);
		// if text should be wrapped
		if ($strWidth >= $width) {
			$newStr = '';
			$newW = 0;
			$i = 0;
			// adds one symbol and checks text width
			while ($newW < $width - 1) {
				$newStr .= $text[$i];
				$newW = $this->cb->GetStringWidth($newStr.$text[$i + 1].'...', '', '', $fontSize, false);
				$i++;
			}
			return $newStr.'...';
		} else {
			return $text;
		}
	}


	// outputs PDF in browser
	public function pdfOut() {
		// send PDF-file in browser
		$this->cb->Output('scheduler.pdf', 'I');
	}


	// converts color from "ffffff" to Array('R' => 255, 'G' => 255, 'B' => 255)
	private function convertColor($colorHex) {
		if ($colorHex == '') return 'transparent';
		$final = Array();
		$final['R'] = hexdec(substr($colorHex, 0, 2));
		$final['G'] = hexdec(substr($colorHex, 2, 2));
		$final['B'] = hexdec(substr($colorHex, 4, 2));
		return $final;
	}


	// convert event color ot RGB-format
	private function processEventColor($color) {
		if ($color == 'transparent') {
			return $color;
		}

		if (preg_match("/#[0-9A-Fa-f]{6}/", $color)) {
			return substr($color, 1);
		}
		$result = preg_match_all("/rgb\s?\((\d{1,3})\s?,\s?(\d{1,3})\s?,\s?(\d{1,3})\)/", $color, $rgb);
		
		if ($result) {
			$color = '';
			for ($i = 1; $i <= 3; $i++) {
				$comp = dechex($rgb[$i][0]);
				if (strlen($comp) == 1) {
					$comp = '0'.$comp;
					}
				$color .= $comp;
			}
			return $color;
		} else {
			return 'transparent';
		}
	}
	
	private function setFillColor($color) {
		$this->cb->SetFillColor($color['R'], $color['G'], $color['B']);
	}
	
	private function setTextColor($color) {
		$this->cb->SetTextColor($color['R'], $color['G'], $color['B']);
	}

}
