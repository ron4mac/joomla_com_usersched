<?php
// Set flag that this is a parent file
define('_JEXEC', 1);
// establish the site base path
define('JPATH_BASE', dirname(__FILE__).'/../..' );

// fire up the Joomla engine
require_once (JPATH_BASE.'/includes/defines.php');
require_once (JPATH_BASE.'/includes/framework.php');
jimport('joomla.database.database');
jimport('joomla.database.table');

$app = JFactory::getApplication('site');
$app->initialise();
$yr = $app->input->get('y');

$db = JFactory::getDbo();

$groups = array(2);

$userGroupWhereStatement = "u.id IN (SELECT ugm.user_id FROM #__user_usergroup_map ugm WHERE ";
$hasGroups = false;
if ($groups) {
	foreach ($groups as $value) {
		if ($value != "") {
			if ($hasGroups == false) {
				$userGroupWhereStatement .= "ugm.group_id=" . $value;
				$hasGroups = true;
			} else {
				$userGroupWhereStatement .= " OR ugm.group_id=" . $value;
			}
		}
	}
}
$userGroupWhereStatement .= ")";

$query = "SELECT u.name, u.block, (SELECT w.profile_value FROM #__user_profiles w WHERE w.user_id=u.id AND w.profile_key='profile.dob') AS dob FROM #__users u";

if($hasGroups) {
	$query .= " WHERE " . $userGroupWhereStatement;
}

$db->setQuery( $query );
$rows = $db->loadObjectList();
$evts = array();
foreach ($rows as $u) {
	$dob = unQuote($u->dob);
	$bday = strtotime($dob);
	$nxd = $bday + 86400;
	if ($bday) {
		$evts[] = array('text'=>$u->name,'start_date'=>$yr.date('-m-d',$bday),'end_date'=>$yr.date('-m-d',$nxd),'xevt'=>'isBrthday');
	}
};
echo json_encode($evts);

function unQuote($val,$comma=false)
{
	$nq = str_replace('"','',$val);
	if ($comma && $nq) $nq .= ', ';
	return $nq;
}
