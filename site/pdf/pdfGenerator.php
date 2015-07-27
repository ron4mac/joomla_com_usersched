<?php
// License: GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)

class schedulerPDF {


	public $strip_tags = false;

	/* header/footer printing */
	private $header = false;
	private $footer = false;
	public $headerImgHeight = 40;
	public $footerImgHeight = 40;
	public $headerImg = './header.png';
	public $footerImg = './footer.png';

	private $profile = 'color';
	private $orientation = 'P';
	private $mode;
	private $today;
	private $multiday = Array();

	public function printScheduler($xml) {
		$this->renderData($xml);
		$this->renderScales($xml);
		$this->renderEvents($xml);

		if ($this->header !== false) {
			$this->sizes->offsetTop += $this->headerImgHeight;
		}
		if ($this->footer !== false) {
			$this->sizes->offsetBottom += $this->footerImgHeight;
		}
		$this->wrapper = new pdfWrapper($this->sizes);
		switch ($this->mode) {
			case 'month':
				$this->orientation = 'L';
				$this->printMonth();
				break;
			case 'year':
				$this->orientation = 'L';
				$this->printYear();
				break;
			case 'agenda':
			case 'map':
				$this->orientation = 'P';
				$this->printAgenda();
				break;
			case 'matrix':
				$this->orientation = 'L';
				$this->printMatrix();
				break;
			case 'timeline':
				$this->orientation = 'L';
				$this->printTimeline();
				break;
			case 'week_agenda':
				$this->orientation = 'P';
				$this->printWeekAgenda();
				break;
			case 'week':
				$this->orientation = 'L';
				$this->printDayWeek();
				break;
			case 'day':
			case 'unit':
			default:
				$this->orientation = 'P';
				$this->printDayWeek();
				break;
		}

		$this->wrapper->pdfOut();
	}

	// parses options profile, orientation, header and footer from xml 
	private function renderData($data) {
		if (isset($data->attributes()->profile)) {
			$this->profile = (string) $data->attributes()->profile;
		}
		if (isset($data->attributes()->orientation)) {
			$this->orientation = (string) $data->attributes()->orientation;
		}
		if (isset($data->attributes()->header)) {
			$this->header = (string) $data->attributes()->header;
		}
		if (!file_exists($this->headerImg)) {
			$this->header = false;
		}
		if (isset($data->attributes()->footer)) {
			$this->footer = (string) $data->attributes()->footer;
		}
		if (!file_exists($this->footerImg)) {
			$this->footer = false;
		}
		$this->setProfile();
		$this->setSizes();
	}

	private function setSizes() {
		$this->sizes = new Sizes();
		$vars = get_object_vars($this->sizes);
		foreach ($vars as $name => $value) {
			if (isset($this->$name))
				$this->sizes->$name = $this->$name;
		}
	}

	// sets color profile
	private function setProfile() {
		$this->colors= new Colors();
		$this->colors->setColorProfile($this->profile);

		if ($this->profile === 'custom') {
			$vars = get_object_vars($this->colors);
			foreach ($vars as $name => $value) {
				if (isset($this->$name))
					$this->colors->$name = $this->$name;
			}
		}
	}

	// render scales
	private function renderScales($xml) {
		$scales = $xml->scale;
		$this->mode = (string) $scales->attributes()->mode;
		$this->today = $this->strip((string) $scales->attributes()->today);

		switch ($this->mode) {
			case 'month':
				foreach ($scales->x->column as $text) {
					$this->topScales[] = $this->strip((string) $text);
				}
				foreach ($scales->row as $text) {
					$week = explode("|", $text);
					for ($i = 0; $i < count($week); $i++) {
						$week[$i] = $this->strip($week[$i]);
					}
					$this->dayHeader[] = $week;
				}
				break;

			case 'year':
				foreach ($scales->month as $month) {
					$monthArr = Array();
					$monthArr['label'] = $this->strip((string) $month->attributes()->label);
					foreach ($month->column as $col) {
						$monthArr['columns'][] = $this->strip((string) $col);
					}

					foreach ($month->row as $row) {
						$row = $this->strip($row);
						$row = explode("|", $row);
						$monthArr['rows'][] = $row;
					}
					$this->yearValues[] = $monthArr;
				}
				break;
			case 'agenda':
			case 'map':
				$this->agendaHeader[0] = $this->strip((string) $scales->column[0]);
				$this->agendaHeader[1] = $this->strip((string) $scales->column[1]);
				break;

			case 'week_agenda':
				foreach ($scales->column as $col) {
					$day = $this->strip((string) $col);
					$this->dayHeader[] = $day;
				}
				break;
			
			case 'timeline':
			case 'matrix':
				foreach ($scales->x->column as $col) {
					$this->columnHeader[] = $this->strip((string) $col);
				}
				foreach ($scales->y->row as $row) {
					$this->rowHeader[] = $this->strip((string) $row);
					if (isset($row->attributes()->bg)) {
						$bgs = $this->strip((string) $row->attributes()->bg);
						$bgs = explode("|", $bgs);
					} else {
						$bgs = false;
					}
					$this->cellColors[] = $bgs;
				}
				break;

			case 'day':
			case 'unit':
			case 'week':
			default:
				foreach ($scales->x->column as $col) {
					$this->columnHeader[] = $this->strip((string) $col);
				}
				foreach ($scales->y->row as $row) {
					$this->rowHeader[] = $this->strip((string) $row);
				}
				break;
		}
	}


	// parses events from xml
	private function renderEvents($xml) {
		$this->events = Array();
		switch ($this->mode) {
			case 'month':
				foreach ($xml->event as $ev) {
					$event['week'] = (int) $ev->attributes()->week - 1;
					$event['day'] = (int) $ev->attributes()->day;
					$event['x'] = (float) $ev->attributes()->x;
					$event['y'] = (float) $ev->attributes()->y;
					$event['width'] = (float) $ev->attributes()->width;
					$event['height'] = (float) $ev->attributes()->height;
					$event['type'] = (string) $ev->attributes()->type;
					$event['text'] = $this->strip((string) $ev->body);
					$event['text_color'] = $this->parseColor((string) $ev->body->attributes()->color);
					$event['color'] = $this->parseColor((string) $ev->body->attributes()->backgroundColor);
					$this->events[] = $event;
				}
				break;
			case 'year':
				foreach ($xml->event as $ev) {
					$week = (int) $ev->attributes()->week;
					$day = (int) $ev->attributes()->day;
					$month = (int) $ev->attributes()->month;
					$event = Array();
					$event['text_color'] = $this->parseColor((string) $ev->attributes()->color);
					$event['color'] = $this->parseColor((string) $ev->attributes()->backgroundColor);
					$this->events[$month.'_'.$week.'_'.$day] = $event;
				}
				break;
			case 'week_agenda':
				foreach ($xml->event as $ev) {
					$day = $this->strip((int) $ev->attributes()->day);
					$body = $this->strip((string) $ev->body);
					$this->events[] = Array('day' => $day, 'body' => $body);
				}
				break;
			case 'agenda':
			case 'map':
				foreach ($xml->event as $ev) {
					$head = $this->strip((string) $ev->head);
					$body = $this->strip((string) $ev->body);
					$this->events[] = Array('head' => $head, 'body' => $body);
				}
				break;
			case 'day':
			case 'week':
			case 'unit':
			default:
				foreach ($xml->event as $ev) {
					$event['type'] = (string) $ev->attributes()->type;
					$event['head'] = $this->strip((string) $ev->header);
					$event['body'] = $this->strip((string) $ev->body);
					$event['len'] = (int) $ev->attributes()->len;
					$event['x'] = (float) $ev->attributes()->x;
					$event['y'] = (float) $ev->attributes()->y;
					$event['width'] = (float) $ev->attributes()->width;
					$event['height'] = (float) $ev->attributes()->height;
					$event['week'] = (isset($ev->attributes()->week)) ? (int) $ev->attributes()->week : 0;
					$event['day'] = (isset($ev->attributes()->day)) ? (int) $ev->attributes()->day : 0;
					$event['text_color'] = $this->parseColor((string) $ev->body->attributes()->color);
					$event['color'] = $this->parseColor((string) $ev->body->attributes()->backgroundColor);
					if (($event['type'] == 'event_line')&&($this->mode !== 'timeline'))
						$this->multiday[] = $event;
					else
						$this->events[] = $event;
				}
		}
	}


	private function parseColor($colorStr) {
		$color_ind = array();
		preg_match("/rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)/", $colorStr, $color_ind);
		if (count($color_ind) > 0) {
			$r = (String) dechex($color_ind[1]);
			$r = (strlen($r) > 1) ? $r : '0'.$r;
			$g = (String) dechex($color_ind[2]);
			$g = (strlen($g) > 1) ? $g : '0'.$g;
			$b= (String) dechex($color_ind[3]);
			$b = (strlen($b) > 1) ? $b : '0'.$b;
			return $r.$g.$b;
		}
		preg_match("/#([0-9a-fA-F]{6})/", $colorStr, $color_ind);
		if (count($color_ind) == 2) {
			return $color_ind[1];
		}
		return false;
	}


	private function printMonth() {
		if ($this->header !== false) {
			$this->wrapper->setHeader($this->headerImg, $this->headerImgHeight);
		}
		if ($this->footer !== false) {
			$this->wrapper->setFooter($this->footerImg, $this->footerImgHeight);
		}
		$this->wrapper->drawMonthHeader($this->orientation, $this->topScales, $this->sizes, $this->colors);
		$this->wrapper->drawMonthGrid($this->dayHeader, $this->sizes, $this->colors);
		if ($this->profile === 'bw')
			$this->wrapper->bwMonthBorder($this->colors);
		$this->wrapper->drawToday($this->today, $this->sizes, $this->colors);
		$this->wrapper->drawMonthEvents($this->events, $this->sizes, $this->colors);
	}


	private function printYear() {
		if ($this->header !== false) {
			$this->wrapper->setHeader($this->headerImg, $this->headerImgHeight);
		}
		if ($this->footer !== false) {
			$this->wrapper->setFooter($this->footerImg, $this->footerImgHeight);
		}
		$this->wrapper->drawYear($this->orientation, $this->yearValues, $this->events, $this->sizes, $this->colors);
		$this->wrapper->drawToday($this->today, $this->sizes, $this->colors);
	}


	private function printAgenda() {
		if ($this->header !== false) {
			$this->wrapper->setHeader($this->headerImg, $this->headerImgHeight);
		}
		if ($this->footer !== false) {
			$this->wrapper->setFooter($this->footerImg, $this->footerImgHeight);
		}
		$this->wrapper->drawAgenda($this->agendaHeader, $this->events, $this->sizes, $this->colors, $this->today);
	}


	private function printDayWeek() {
		if ($this->header !== false) {
			$this->wrapper->setHeader($this->headerImg, $this->headerImgHeight);
		}
		if ($this->footer !== false) {
			$this->wrapper->setFooter($this->footerImg, $this->footerImgHeight);
		}
		$this->wrapper->drawDayWeekHeader($this->columnHeader, $this->rowHeader, $this->sizes, $this->colors, $this->orientation, $this->multiday);
		$this->wrapper->drawToday($this->today, $this->sizes, $this->colors);
		if ($this->profile === 'bw')
			$this->wrapper->bwBorder($this->colors);
		$this->wrapper->drawDayWeekEvents($this->events, $this->multiday, $this->sizes, $this->colors);
	}

	private function printTimeline() {
		if ($this->header !== false) {
			$this->wrapper->setHeader($this->headerImg, $this->headerImgHeight);
		}
		if ($this->footer !== false) {
			$this->wrapper->setFooter($this->footerImg, $this->footerImgHeight);
		}
		$this->wrapper->drawTimelineHeader($this->columnHeader, $this->rowHeader, $this->cellColors, $this->sizes, $this->colors, $this->orientation);
		$this->wrapper->drawToday($this->today, $this->sizes, $this->colors);

		$heights = $this->getTimelineSectionHeights();
		$max_heights = $heights['max_heights'];
		$add_event_height = $heights['add_event_height'];

		for ($i = 0; $i < count($this->rowHeader); $i++) {
			$drawed = $this->wrapper->drawTimelineSection($this->rowHeader[$i], $max_heights[$i], $this->cellColors[$i], $this->getEventsByWeek($i), $this->sizes, $this->colors);
			$k = 0;

			while (($drawed['height'] !== 0)) {
				if ($this->profile === 'bw')
					$this->wrapper->bwBorder($this->colors);
				$this->wrapper->drawTimelineHeader($this->columnHeader, $this->rowHeader, $this->cellColors, $this->sizes, $this->colors, $this->orientation);
				$this->wrapper->drawToday($this->today, $this->sizes, $this->colors);

				$drawed = $this->wrapper->drawTimelineSection($this->rowHeader[$i], $drawed['height'], $this->cellColors[$i], $drawed['events'], $this->sizes, $this->colors);
				$k++;
			}
		}
		if ($this->profile === 'bw')
			$this->wrapper->bwBorder($this->colors);
	}

	
	private function printMatrix() {
		if ($this->header !== false) {
			$this->wrapper->setHeader($this->headerImg, $this->headerImgHeight);
		}
		if ($this->footer !== false) {
			$this->wrapper->setFooter($this->footerImg, $this->footerImgHeight);
		}
		
		$this->wrapper->drawTimelineHeader($this->columnHeader, $this->rowHeader, $this->cellColors, $this->sizes, $this->colors, $this->orientation);
		$this->wrapper->drawToday($this->today, $this->sizes, $this->colors);
		$this->wrapper->drawMatrixEvents($this->events, $this->sizes, $this->colors);
		if ($this->profile === 'bw')
			$this->wrapper->bwBorder($this->colors);
	}


	private function printWeekAgenda() {
		if ($this->header !== false) {
			$this->wrapper->setHeader($this->headerImg, $this->headerImgHeight);
		}
		if ($this->footer !== false) {
			$this->wrapper->setFooter($this->footerImg, $this->footerImgHeight);
		}
		$events = $this->events;
		while (count($events) > 0) {
			$this->wrapper->drawWeekAgendaContainer($this->dayHeader, $this->sizes, $this->colors, $this->orientation);
			$this->wrapper->drawToday($this->today, $this->sizes, $this->colors);
			$events = $this->wrapper->drawWeekAgendaEvents($events, $this->sizes, $this->colors);
		}
	}


	private function getTimelineSectionHeights() {
		$normal_sections = Array();
		$whole_height = count($this->rowHeader)*100;
		$max_heights = Array();
		$add_event_height = Array();
		for ($i = 0; $i < count($this->rowHeader); $i++) {
			$events = $this->getEventsByWeek($i);
			$max_height = 0;
			for ($j = 0; $j < count($events); $j++) {
				if ($events[$j]['y'] + $this->getTimelineEventRelativeHeight() > $max_height) {
					$max_height = $events[$j]['y'] + $this->getTimelineEventRelativeHeight();
				}
			}
			if ($max_height <= 100) {
				$max_height = 100;
				$normal_sections[] = $i;
				$add_event_height[$i] = 0;
			} else {
				$add_event_height[$i] = $this->sizes->timelineEventHeight;
				$add_event_height[$i] = 0;
			}
			$whole_height -= $max_height;
			$max_heights[$i] = $max_height;
		}
		$add_height = (count($normal_sections) > 0) ? $whole_height/count($normal_sections) : 0;
		if (100 + $add_height >= $this->sizes->minTimelineSectionHeight) {
			for ($i = 0; $i < count($normal_sections); $i++)
				$max_heights[$normal_sections[$i]] += $add_height;
		}
		return Array('max_heights' => $max_heights, 'add_event_height' => $add_event_height);
	}


	private function getTimelineEventRelativeHeight() {
		$relHeight = $this->wrapper->getTimelineRelativeHeight($this->sizes->timelineEventHeight);
		return $relHeight;
	}


	private function getEventsByWeek($week) {
		$events = Array();
		for ($i = 0; $i < count($this->events); $i++) {
			if ($this->events[$i]['week'] == $week)
				$events[] = $this->events[$i];
		}
		return $events;
	}


	private function strip($param) {
		if ($this->strip_tags == true) {
			$param = strip_tags($param);
		}
		$param = html_entity_decode($param);
		return $param;
	}

}



class Colors {

	public $bgColor = 'C2D5FC';
	// line color
	public $lineColor = 'c1d4fc';
	// scales line color
	public $headerLineColor = 'FFFFFF';
	// header text color
	public $headerTextColor = '2F3A48';
	// text color of today label
	public $todayTextColor = '000000';

	// month view colors:
	public $dayHeaderColor = 'EBEFF4';
	public $dayBodyColor = 'FFFFFF';
	public $dayHeaderColorInactive = 'E2E3E6';
	public $dayBodyColorInactive = 'ECECEC';

	// event text color
	public $eventTextColor = '887A2E';
	// event border color
	public $eventBorderColor = 'B7A543';
	// event background color
	public $eventColor = 'FFE763';

	// day|week|agenda grid colors
	public $scaleColorOne = 'FCFEFC';
	public $scaleColorTwo = 'DCE6F4';
	// bg color for multiday events
	public $multidayBgColor = 'E1E6FF';

	// year view colors
	public $yearDayColor = 'EBEFF4';
	public $yearDayColorInactive = 'd6d6d6';
	public $yearTextColor = '000000';
	public $yearTextColorInactive = '000000';

	// timeline cell background color
	public $timelineCellBg = 'FFFFFF';
	// matrix cell background color
	public $matrixEventColor = 'FFFFFF';

	public $profile = 'color';
	
	
	
	public function setColorProfile($profile) {
		$this->profile = $profile;
		switch ($this->profile) {
			case 'color':
				// background color
				$this->bgColor = 'C2D5FC';
				// line color
				$this->lineColor = 'c1d4fc';
				// scales line color
				$this->headerLineColor = 'FFFFFF';
				// header text color
				$this->headerTextColor = '2F3A48';
				// text color of today label
				$this->todayTextColor = '000000';
				
				// month view colors:
				$this->dayHeaderColor = 'EBEFF4';
				$this->dayBodyColor = 'FFFFFF';
				$this->dayHeaderColorInactive = 'E2E3E6';
				$this->dayBodyColorInactive = 'ECECEC';

				// event text color
				$this->eventTextColor = '887A2E';
				// event border color
				$this->eventBorderColor = 'B7A543';
				// event background color
				$this->eventColor = 'FFE763';
				
				// day|week|agenda grid colors
				$this->scaleColorOne = 'FCFEFC';
				$this->scaleColorTwo = 'DCE6F4';
				// bg color for multiday events
				$this->multidayBgColor = 'E1E6FF';

				// year view colors
				$this->yearDayColor = 'EBEFF4';
				$this->yearDayColorInactive = 'd6d6d6';
				$this->yearTextColor = '000000';
				$this->yearTextColorInactive = '000000';

				// timeline cell background color
				$this->timelineCellBg = 'FFFFFF';
				// matrix cell background color
				$this->matrixEventColor = 'FFFFFF';


				break;
			case 'gray':
				// background color
				$this->bgColor = 'D3D3D3';
				// line color
				$this->lineColor = 'e7e7e7';
				// scales line color
				$this->headerLineColor = 'FFFFFF';
				// header text color
				$this->headerTextColor = '383838';
				// text color of today label
				$this->todayTextColor = '000000';
				
				// month view colors:
				$this->dayHeaderColor = 'EEEEEE';
				$this->dayBodyColor = 'FFFFFF';
				$this->dayHeaderColorInactive = 'E3E3E3';
				$this->dayBodyColorInactive = 'ECECEC';

				// event text color
				$this->eventTextColor = '887A2E';
				// event border color
				$this->eventBorderColor = '9F9F9F';
				// event background color
				$this->eventColor = 'DFDFDF';
				
				// day|week|agenda grid colors
				$this->scaleColorOne = 'E4E4E4';
				$this->scaleColorTwo = 'FDFDFD';
				// bg color for multiday events
				$this->multidayBgColor = 'E7E7E7';

				// year view colors
				$this->yearDayColor = 'EBEFF4';
				$this->yearDayColorInactive = 'E2E3E6';
				$this->yearTextColor = '000000';
				$this->yearTextColorInactive = '000000';

				// timeline cell background color
				$this->timelineCellBg = 'FFFFFF';
				// matrix cell background color
				$this->matrixEventColor = 'FFFFFF';

				break;
			case 'bw':
				// background color
				$this->bgColor = 'FFFFFF';
				// line color
				$this->lineColor = '000000';
				// scales line color
				$this->headerLineColor = '000000';
				// header text color
				$this->headerTextColor = '000000';
				// text color of today label
				$this->todayTextColor = '000000';
				
				// month view colors:
				$this->dayHeaderColor = 'FFFFFF';
				$this->dayBodyColor = 'FFFFFF';
				$this->dayHeaderColorInactive = 'FFFFFF';
				$this->dayBodyColorInactive = 'FFFFFF';

				// event text color
				$this->eventTextColor = '000000';
				// event border color
				$this->eventBorderColor = '000000';
				// event background color
				$this->eventColor = 'FFFFFF';
				
				// day|week|agenda grid colors
				$this->scaleColorOne = 'FFFFFF';
				$this->scaleColorTwo = 'FFFFFF';
				// bg color for multiday events
				$this->multidayBgColor = 'FFFFFF';

				// year view colors
				$this->yearDayColor = 'FFFFFF';
				$this->yearDayColorInactive = 'FFFFFF';
				$this->yearTextColor = '000000';
				$this->yearTextColorInactive = '000000';

				// timeline cell background color
				$this->timelineCellBg = 'FFFFFF';
				// matrix cell background color
				$this->matrixEventColor = 'FFFFFF';

				break;
		}
	}
	
	
}


class Sizes {

	// page offsets
	public $offsetTop = 15;
	public $offsetBottom = 10;
	public $offsetLeft = 10;
	public $offsetRight = 10;

	// header height of day container in month mode
	public $monthDayHeaderHeight = 6;
	// header height in month mode
	public $monthHeaderHeight = 8;
	// height of month name container in year mode
	public $yearMonthHeaderHeight = 8;
	// height of row in agenda mode
	public $agendaRowHeight = 6;
	// height of header in day and week mode
	public $dayTopHeight = 6;
	// width of left scale in day and week mode
	public $dayLeftWidth = 26;
	// minimal height of timeline section in relative units (100) - default height of section
	public $minTimelineSectionHeight = 50;
	// timeline event height
	public $timelineEventHeight = 4;
	// height of one multiday line
	public $multidayLineHeight = 5;
	// height of month line event
	public $monthEventHeaderHeight = 4;
	// header height of day container in month mode
	public $weekAgendaEventHeight = 6;

	// font size settings
	public $monthHeaderFontSize = 9;
	public $monthDayHeaderFontSize = 8;
	public $monthEventFontSize = 7;
	public $yearHeaderFontSize = 8;
	public $yearFontSize = 8;
	public $agendaFontSize = 7.5;
	public $dayHeaderFontSize = 7;
	public $dayScaleFontSize = 8;
	public $dayEventHeaderFontSize = 7;
	public $dayEventBodyFontSize = 7;
	public $todayFontSize = 11;

}
