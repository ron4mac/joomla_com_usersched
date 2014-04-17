<?php
defined('_JEXEC') or die;
 
jimport('rjuserdata.userdata');

class com_userschedInstallerScript
{
	function install ($parent) 
	{
		if (!class_exists('RJUserData',false)) {
			JError::raiseWarning(null, 'RJUserData library is required (install)');
			return false;
		}
		$parent->getParent()->setRedirectURL('index.php?option=com_usersched');
	}

	function uninstall ($parent) 
	{
	}

	function update ($parent) 
	{
		if (!class_exists('RJUserData',false)) {
			JError::raiseWarning(null, 'RJUserData library is required (update)');
			return false;
		}
	}

	function preflight ($type, $parent) 
	{
		if (!class_exists('RJUserData',false)) {
			JError::raiseWarning(null, 'RJUserData library is required (pre-flight)');
			return false;
		}
		$this->release = $parent->get('manifest')->version;
	}

	function postflight ($type, $parent) 
	{
		$params['version'] = $this->release;
		$this->setParams($params, true);
		if ($type == 'install') {
			$params['user_canskin'] = '0';
			$params['user_canalert'] = '0';
			$params['user_recurrevt'] = '0';
			$params['grp_canskin'] = '0';
			$params['grp_canalert'] = '0';
			$params['grp_recurrevt'] = '0';
			$params['show_versions'] = '1';
		}
		$this->setParams($params);
	}

	function old_postflight ($type, $parent) 
	{
		echo '<p>' . JText::_('COM_HELLOWORLD_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
		$defaults = '{';
		$defaults .= '"user_canskin":"0",';
		$defaults .= '"user_canalert":"0",';
		$defaults .= '"user_recurrevt":"0",';
		$defaults .= '"grp_canskin":"0",';
		$defaults .= '"grp_canalert":"0",';
		$defaults .= '"grp_recurrevt":"0",';
		$defaults .= '"show_versions":"1"';
		$defaults .= '}';
		if ($type == 'install') {
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->update('#__extensions');
			$query->set('params = ' . $db->quote($defaults));
			$query->where('name = ' . $db->quote('com_usersched'));
			$db->setQuery($query);
			$db->query();
		} else if ($type == 'update') {
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			$query->select('params');
			$query->from('#__extensions');
			$query->where("name = 'com_usersched'");
			$db->setQuery($query);
			$saved = $db->loadAssoc();
			$old = json_decode($saved['params'], true);
			$new = json_decode($defaults, true);

			// If options already exist, keep the old ones.
			if ($old) {
				$new = array_merge($new, $old);
				$options = json_encode($new);
			} else {
				$options = $defaults;
			}

			$query->update('#__extensions');
			$query->set("params = " . "'".$options."'");
			$query->where("name = 'com_usersched'");
			$db->setQuery($query);
			$db->query();
		}
	}

	private function setParams ($param_array, $replace=false)
	{
		if (count($param_array) > 0) {
			// read the existing component value(s)
			$db = JFactory::getDbo();
			$db->setQuery('SELECT params FROM #__extensions WHERE name = "com_usersched"');
			$params = json_decode($db->loadResult(), true);
			// add the new variable(s) to the existing one(s), replacing existing only if requested
			foreach ($param_array as $name => $value) {
				if (!isset($params[(string) $name]) || $replace)
					$params[(string) $name] = (string) $value;
			}
			// store the combined new and existing values back as a JSON string
			$paramsString = json_encode($params);
			$db->setQuery('UPDATE #__extensions SET params = ' . $db->quote($paramsString) . ' WHERE name = "com_usersched"');
			$db->query();
		}
	}
}