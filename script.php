<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.2.2
*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Log\Log;

class com_userschedInstallerScript extends InstallerScript
{
	protected $minimumJoomla = '4.0';
	protected $com_name = 'com_usersched';
	protected $deleteFolders = ['components/com_usersched/pdf','components/com_usersched/scheduler'];
	protected $deleteFiles = ['components/com_usersched/cron.php','components/com_usersched/cront.php','components/com_usersched/alertchk.php'];

	public function install ($parent) 
	{
		$parent->getParent()->setRedirectURL('index.php?option='.$this->com_name);
	}

	public function uninstall ($parent) 
	{
	}

	public function update ($parent) 
	{
	}

	public function preflight ($type, $parent) 
	{
		// give the parent first shot
		if (parent::preflight($type, $parent) === false) return false;

		// ensure that SQLite is active in joomla
		$dbs = JDatabaseDriver::getConnectors();
		if (!in_array('sqlite', $dbs) && !in_array('Sqlite', $dbs)) {
			Log::add('Joomla support for SQLite(3) is required for this component.', Log::WARNING, 'jerror');
			return false;
		}
		// ensure that the RJUser library is installed
		include JPATH_LIBRARIES . '/rjuser/com.php';
		if (!class_exists('RJUserCom')) {
			Log::add('The <a href="https://github.com/ron4mac/joomla_lib_rjuser" target="_blank">RJUser Library</a> is required for this component.', Log::WARNING, 'jerror');
			return false;
		}
		// get the version number being installed/updated
		if (method_exists($parent,'getManifest')) {
			$this->release = $parent->getManifest()->version;
		} else {
			$this->release = $parent->get('manifest')->version;
		}
	}

	public function postflight ($type, $parent) 
	{
		$params['version'] = $this->release;
		$this->mySetParams($params, true);
		if ($type == 'install') {
			$params['user_canskin'] = false;
			$params['user_canalert'] = false;
			$params['user_recurrevt'] = false;
			$params['grp_canskin'] = false;
			$params['grp_canalert'] = false;
			$params['grp_recurrevt'] = false;
			$params['show_versions'] = true;
			$this->mySetParams($params);
		}
	}

	private function mySetParams ($param_array, $replace=false)
	{
		if (count($param_array) > 0) {
			// read the existing component value(s)
			$db = Factory::getDbo();
			$db->setQuery('SELECT params FROM #__extensions WHERE name = "'.$this->com_name.'"');
			$params = json_decode($db->loadResult(), true);
			// add the new variable(s) to the existing one(s), replacing existing only if requested
			foreach ($param_array as $name => $value) {
				if (!isset($params[(string) $name]) || $replace)
					$params[(string) $name] = (string) $value;
			}
			// store the combined new and existing values back as a JSON string
			$paramsString = json_encode($params);
			$db->setQuery('UPDATE #__extensions SET params = ' . $db->quote($paramsString) . ' WHERE name = "'.$this->com_name.'"');
			$db->execute();
		}
	}
}
