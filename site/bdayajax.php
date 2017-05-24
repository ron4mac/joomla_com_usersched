<?php
/* accessed via ajax from the client browser to get birthday events for joomla users */
// Set flag that this is a parent file
define('_JEXEC', 1);
// establish the site base path
$jpb = dirname($_SERVER['SCRIPT_FILENAME'], 3);
define('JPATH_BASE', $jpb);

// fire up the Joomla engine
require_once (JPATH_BASE.'/includes/defines.php');
require_once (JPATH_BASE.'/includes/framework.php');
jimport('joomla.database.database');
jimport('joomla.database.table');
$app = JFactory::getApplication('site');
$app->initialise();

// get the call parameter
$yr = $app->input->get('y');
// get the database
$db = JFactory::getDbo();
// set groups to just get registered users
$groups = array(2);
// set a where clause for appropriate filtering
$userGroupWhereStatement = 'u.block=0 AND u.id IN (SELECT ugm.user_id FROM #__user_usergroup_map ugm WHERE ';
$hasGroups = false;
if ($groups) {
	foreach ($groups as $value) {
		if ($value != '') {
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
// turn them into calendar events
$evts = array();
foreach ($rows as $u) {
	$dob = unQuote($u->dob);
	$bday = strtotime($dob);
	$nxd = $bday + 86400;
	if ($bday) {
		$evts[] = array('text'=>$u->name,'start_date'=>$yr.date('-m-d',$bday),'end_date'=>$yr.date('-m-d',$nxd),'xevt'=>'isBrthday');
	}
};
// send the events to the client
echo json_encode($evts);

// clean up the dob as returned from the database
function unQuote ($val, $comma=false)
{
	$nq = str_replace('"','',$val);
	if ($comma && $nq) $nq .= ', ';
	return $nq;
}
