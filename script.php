<?php
defined('_JEXEC') or die;
 
jimport('rjuserdata.userdata');

class com_userschedInstallerScript
{
	function install ($parent) 
	{
		$parent->getParent()->setRedirectURL('index.php?option=com_usersched');
		if (!class_exists('RJUserData',false)) {
			JError::raiseWarning(null, 'RJUserData library is required (install)');
			return false;
		}
	}

	function uninstall ($parent) 
	{
		echo '<p>' . JText::_('COM_HELLOWORLD_UNINSTALL_TEXT') . '</p>';
	}

	function update ($parent) 
	{
		echo '<p>' . JText::_('COM_HELLOWORLD_UPDATE_TEXT') . '</p>';
		if (!class_exists('RJUserData',false)) {
			JError::raiseWarning(null, 'RJUserData library is required (update)');
			return false;
		}
	}

	function preflight ($type, $parent) 
	{
		echo '<p>' . JText::_('COM_HELLOWORLD_PREFLIGHT_' . $type . '_TEXT') . '</p>';
		if (!class_exists('RJUserData',false)) {
			$msg = 'RJUserData library is required (pre-flight)';
			//JError::raiseWarning(500, $msg);
			//throw new RuntimeException($msg);
			JFactory::getApplication()->enqueueMessage($msg, 'error');
			return false;
		}
	}

	function postflight ($type, $parent) 
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
}