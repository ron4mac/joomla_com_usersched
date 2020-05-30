<?php
define('_JEXEC',1);
$JPB = dirname(dirname(__DIR__));
// makeup for development structure
if (!file_exists($JPB.'/includes/defines.php')) {
	$JPB = dirname($JPB);
}
define('JPATH_BASE', $JPB);
define('JPATH_PLATFORM', JPATH_BASE . '/libraries');
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';
//require_once JPATH_PLATFORM . '/import.php';
require_once JPATH_SITE . '/configuration.php';
require_once JPATH_PLATFORM . '/vendor/autoload.php';

require_once 'alertchk.php';

use Joomla\Application\AbstractApplication;

class USchedApp extends AbstractApplication
{
	public function doExecute()
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_usersched', dirname(__FILE__), $lang->getTag(), true);

		$config = new JConfig();
		ini_set('date.timezone',$config->offset);
		$xtime = time();
		if ($dirh = opendir(JPATH_SITE . '/userstor')) {
			while (false !== ($entry = readdir($dirh))) {
				if ($entry != '.' && $entry != '..' && is_dir(JPATH_SITE.'/userstor/'.$entry)) {
					if ($entry[0] == '@' || $entry[0] == '_') {
						$grp = $entry[0] == '_';
						$dbp = JPATH_SITE.'/userstor/'.$entry.'/com_usersched/sched.sql3';
						if (file_exists($dbp)) {
							$acheck = new USchedAcheck($dbp, $config);
							$acheck->processAlerts($xtime);
							unset($acheck);
						}
					}
				}
			}
			closedir($dirh);
		}
	}
}

//if (!class_exists('JError')) {
//	class JError
//	{
//		
//	}
//}

try
{
    $cron_app = new USchedApp();
    $cron_app->execute();
}
catch (Exception $e)
{
    // An exception has been caught, just echo the message.
    echo date('Y-m-d H:i:s   ') . $e->getMessage();
    exit($e->getCode());
}
