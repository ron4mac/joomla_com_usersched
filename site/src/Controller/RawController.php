<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
namespace RJCreations\Component\Usersched\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Filesystem\Folder;
use RJCreations\Library\RJUserCom;
use RJCreations\Component\Usersched\Site\Helper\AlertCheck;

\JLoader::register('UschedHelper', JPATH_ADMINISTRATOR.'/components/com_usersched/helpers/usched.php');
//\JLoader::register('USchedAcheck', JPATH_COMPONENT.'/alertcheck.php');

define('RJC_DEV', (JDEBUG) && file_exists(JPATH_ROOT.'/rjcdev.php'));

class RawController extends BaseController
{
	//	ajax call from client scheduler for user birthdays
	public function birthdays (): void
	{
		// clean up the dob as returned from the database
		function unQuote ($val, $comma=false)
		{
			$nq = str_replace('"','',$val);
			if ($comma && $nq) $nq .= ', ';
			return $nq;
		}

		$yr = $this->input->get('y');
		// get the database
		$db = Factory::getContainer()->get('DatabaseDriver');
		// set groups to just get registered users
		$groups = [2];
		// set a where clause for appropriate filtering
		$userGroupWhereStatement = 'u.block=0 AND u.id IN (SELECT ugm.user_id FROM #__user_usergroup_map ugm WHERE ';
		$hasGroups = false;
		if ($groups) {
			foreach ($groups as $value) {
				if ($value != 0) {
					if ($hasGroups == false) {
						$userGroupWhereStatement .= 'ugm.group_id=' . $value;
						$hasGroups = true;
					} else {
						$userGroupWhereStatement .= ' OR ugm.group_id=' . $value;
					}
				}
			}
		}
		$userGroupWhereStatement .= ")";
		// create the query
		$query = 'SELECT u.name, u.block, (SELECT w.profile_value FROM #__user_profiles w WHERE w.user_id=u.id AND w.profile_key=\'profile.dob\') AS dob FROM #__users u';
		// add any filtering
		if ($hasGroups) {
			$query .= ' WHERE ' . $userGroupWhereStatement;
		}
		// fire the query to get user birthdays
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		if (!$rows) {
			echo json_encode(['NOPE']);
			return;
		}

		// turn them into calendar events
		$evts = [];
		foreach ($rows as $u) {
			if (empty($u->dob)) continue;
			$dob = unQuote($u->dob);
			$bday = strtotime($dob);
			$nxd = $bday + 86400;
			if ($bday) {
				$evts[] = ['text'=>$u->name,'start_date'=>$yr.date('-m-d',$bday),'end_date'=>$yr.date('-m-d',$nxd),'xevt'=>'isBrthday','readonly'=>true];
			}
		};
		// send the events to the client
		echo json_encode($evts);
	}

	//	ajax call from client scheduler for holidays (from Google)
	public function holidays (): void
	{
		$yr = $this->input->get('yr', 2017);	// year
		$rg = $this->input->get('rg', 'usa__en');	// region
		echo $this->hCache($yr, $rg);
	}

	private function hCache ($yr, $rg)
	{
		if (is_writable(JPATH_CACHE)) {
			$cdir = JPATH_CACHE.'/'.$this->input->get('option');
			// check cache dir or create cache dir
			if (!Folder::exists($cdir)) {
				Folder::create($cdir); 
			}

			$cache_file = $cdir.'/'.$yr.'-'.$rg.'.json';

			// check cache file, if not then write cache file
			if (!file_exists($cache_file) || filesize($cache_file) == 0 || ((filemtime($cache_file) + 604800 ) < time())) {	// older than 1 week
				$data = $this->getGholidays($yr, $rg);
				file_put_contents($cache_file, $data);
			} else {
				// read cache file
				$data = file_get_contents($cache_file);
			}
			return $data;
		} else {
			return $this->getGholidays($yr, $rg);
		}
	}

	private function getGholidays ($yr, $rg)
	{
		$key = ComponentHelper::getParams('com_usersched')->get('googapi_key','');
		$url = 'https://www.googleapis.com/calendar/v3/calendars/'.$rg.'@holiday.calendar.google.com/events?key='.$key;
		$url .= '&timeMin='.$yr.'-01-01T00%3A00%3A00%2B00%3A00&timeMax='.($yr+1).'-01-01T00%3A00%3A00%2B00%3A00&singelEvents=true';
	//	file_put_contents('GOOG.txt', $url);
		$connector = HttpFactory::getHttp();
		$data = $connector->get($url);
		return $data->body;
	}

	// cron job access here to send alerts
	public function cron (): void
	{
		$dbug = !str_contains($this->input->server->get('HTTP_USER_AGENT'), 'Wget');

		$dbs = RJUserCom::getDbPaths(null, 'usersched', true);

		$config = new \JConfig();
		$xtime = time();

		if ($dbug) echo'<pre>';
		foreach ($dbs as $inst) {
			foreach ($inst as $info) {
				$acheck = new AlertCheck($info['path'], $config, $dbug);
				$acheck->processAlerts($xtime);
				unset($acheck);
			}
		}
		if ($dbug) echo'</pre>';
	}
	
	// my own backend for scheduler 6.x
	public function calJ6 (): void
	{
		try {
			$m = $this->getModel('backend');
			switch ($_SERVER['REQUEST_METHOD']) {
				case 'GET':
					if (RJC_DEV) file_put_contents('LOG.txt', 'G '.print_r($_GET,true)."\n", FILE_APPEND);
					$result = $m->read($_GET);
				//	if (RJC_DEV) file_put_contents('LOG.txt', 'EV '.print_r($result,true)."\n", FILE_APPEND);
					break;
				case 'POST':
					// with Joomla: ->input->json->getraw()
					$json = $this->input->json->getraw();
					if (RJC_DEV) file_put_contents('LOG.txt', 'I '.print_r($json,true)."\n", FILE_APPEND);
					$requestPayload = json_decode($json);
					$id = $requestPayload->id;
					$action = $requestPayload->action;
					$body = (array) $requestPayload->data;

					$result = [
						'action' => $action
					];

					if ($action == 'inserted') {
						$databaseId = $m->create($body);
						$result['tid'] = $databaseId;
						// delete a single occurrence from recurring series
						if (!empty($body['rec_type']) && $body['rec_type'] === 'none') {
							$result['action'] = 'deleted';//!
						}
					} elseif ($action == 'updated') {
						$m->update($body, $id);
					} elseif ($action == 'deleted') {
						$m->delete($id);
					}
					break;
				default: throw new \Exception('Unexpected Method');
			}
		} catch (\Exception $e) {
			$emsg = $e->getMessage();
			UschedHelper::loggit($emsg,true);
			header("HTTP/1.1 500 Failure");
			header('Content-Type: application/json');
			//http_response_code(500);
			$result = [
				'action' => 'error',
				'message' => $emsg
			];
			echo json_encode($result);
			exit();
		}

		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Content-Type: application/json');
		echo json_encode($result);
	}

	// my own backend for scheduler 7.1+ (with rrule recurring)
	public function calJ7r (): void
	{
		try {
			$m = $this->getModel('backendr');
			switch ($_SERVER['REQUEST_METHOD']) {
				case 'GET':
				//	if (RJC_DEV) file_put_contents('LOG.txt', 'G '.print_r($_GET,true)."\n", FILE_APPEND);
					$result = $m->read($_GET);
				//	if (RJC_DEV) file_put_contents('LOG.txt', 'EV '.print_r($result,true)."\n", FILE_APPEND);
					break;
				case 'POST':
					$json = $this->input->json->getraw();
					if (RJC_DEV) file_put_contents('LOG.txt', 'I '.print_r($json,true)."\n", FILE_APPEND);
					$requestPayload = json_decode($json);
					$id = $requestPayload->id;
					$action = $requestPayload->action;
					$body = (array) $requestPayload->data;

					$result = [
						'action' => $action
					];

					if ($action == 'inserted') {
						$databaseId = $m->create($body);
						$result['tid'] = $databaseId;
						// If a deleted instance was inserted - the server response must have the "deleted" status.
						// A deleted instance can be identified by the non-empty value of the deleted property.
						if (isset($body['deleted'])) {
							$result['action'] = 'deleted';
						}
					} elseif ($action == 'updated') {
						$m->update($body, $id);
					} elseif ($action == 'deleted') {
						$m->delete($id);
					}
					break;
				default: throw new \Exception('Unexpected Method');
			}
		} catch (\Exception $e) {
			$emsg = $e->getMessage();
			UschedHelper::loggit($emsg,true);
			header("HTTP/1.1 500 Failure");
			header('Content-Type: application/json');
			//http_response_code(500);
			$result = [
				'action' => 'error',
				'message' => $emsg
			];
			echo json_encode($result);
			exit();
		}

		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: *');
		header('Content-Type: application/json');
		echo json_encode($result);
	}

	public function showsms (): void
	{
		$sms = json_decode(file_get_contents(JPATH_ROOT . '/media/com_usersched/sms.json'),false);

		$cells = '<div class="smslist"><span class="sth">Carrier</span><span class="sth">SMS/MMS</span><span class="sth">Country/Region</span><span class="sth">Note</span>';
		foreach ($sms as $s) {
			$cells .= '<span>'.$s->carrier;
			$cells .= '</span><span>'.$s->{'email-to-sms'}.($s->{'email-to-mms'} ? ('<br>'.$s->{'email-to-mms'}) : '');
		//	$cells .= '</span><span>'.$s->{'email-to-mms'};
			$cells .= '</span><span>'.$s->country.($s->region ? ('/'.$s->region) : '');
		//	$cells .= '</span><span>'.$s->region;
			$cells .= '</span><span>'.$s->notes.'</span>';
		}
		
		echo json_encode([
			'title'=>'SMS/MMS Email Links for Cell Networks',
			'html'=>$cells.'</div>',
			'error'=>'NOT YET FULLY IMPLEMENTED'
		]);
	}

}
