<?php
defined('_JEXEC') or die;

abstract class UschedHelper
{
	protected static $instanceType = null;
	protected static $ownerID = null;
	protected static $udp = null;

	public static function userDataPath ()
	{
		if (self::$udp) return self::$udp;
		self::getTypeOwner();
		$cmp = JApplicationHelper::getComponentName();
		switch ((int)self::$instanceType) {
			//case -1:
			case 0:
				$ndir = '@'. self::$ownerID;
				break;
			case 1:
				$ndir = '_'. self::$ownerID;
				break;
			case 2:
				$ndir = '_0';
				break;
		}

		self::$udp = self::getStorPath().'/'.$ndir.'/'.$cmp;
		return self::$udp;
	}


	public static function userDataExists ($fnam)
	{
		return file_exists(self::userDataPath().'/'.$fnam);
	}


	public static function getDbPaths ($which, $full=false)
	{
		$paths = array();
		$cmp = JApplicationHelper::getComponentName();
		switch ($which) {
			case 'u':
				$char1 = '@';
				break;
			case 'g':
				$char1 = '_';
				break;
			default:
				$char1 = '';
				break;
		}
		$dpath = JPATH_SITE.'/'.self::getStorPath().'/';
		if (is_dir($dpath) && ($dh = opendir($dpath))) {
			while (($file = readdir($dh)) !== false) {
				if ($file[0]==$char1) {
					$ptf = $dpath.$file.'/'.$cmp.'/sched.sql3';
					if (file_exists($ptf))
						$paths[] = $full ? $ptf : $file;
				}
			}
			closedir($dh);
		}
		return $paths;
	}


	public static function userAuth ($uid)
	{
		self::getTypeOwner();
		$user = JFactory::getUser();
		$uid = $user->get('id');
		$ugrps = $user->get('groups');	//var_dump('ug:',$ugrps);
		switch (self::$instanceType) {
			case 0:
				return $uid == self::$ownerID ? 2 : 0;
				break;
			case 1:
			case 2:
				return in_array(self::$ownerID, $ugrps) ? 2 : 1;
				break;
		}
	}


	public static function getInstanceID ($asAry=false)
	{
		if (is_null(self::$instanceType)) self::getTypeOwner();
		if ($asAry) {
			return array(self::$instanceType, self::$ownerID);
		} else {
			return base64_encode(self::$instanceType.':'.self::$ownerID);
		}
	}


	// convert string in form n(K|M|G) to an integer value
	public static function to_bytes ($val)
	{
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			case 'g': $val *= 1024;
			case 'm': $val *= 1024;
			case 'k': $val *= 1024;
		}
		return $val;
	}


	// convert integer value to n(K|M|G) string
	public static function to_KMG ($val=0)
	{
		$sizm = 'K';
		if ($val) {
			if (($val % 0x40000000) == 0) {
				$sizm = 'G';
				$val >>= 30;
			} elseif (($val % 0x100000) == 0) {
				$sizm = 'M';
				$val >>= 20;
			} else {
			//	$val >>= 10;
			}
		}
		return $val.$sizm;
	}


	public static function formatBytes ($bytes, $precision=2)
	{
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1); 
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . ' ' . $units[$pow];
	}


	private static function getStorPath ()
	{
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onRjuserDatapath', null);
		$dsp = isset($results[0]) ? trim($results[0]) : false;
		return ($dsp ?: 'userstor');
	}


	private static function getTypeOwner ()
	{
		if (is_null(self::$instanceType)) {
			$app = JFactory::getApplication();
			$id = $app->input->getBase64('calid', false);
			if ($id) {
				$ids = explode(':',base64_decode($id));
				self::$instanceType = $ids[0];
				self::$ownerID = $ids[1];
			} else {
				$params = $app->getParams();
				self::$instanceType = $params->get('cal_type');
				switch (self::$instanceType) {
					case 0:
						self::$ownerID = JFactory::getUser()->get('id');
						if (!self::$ownerID) self::$ownerID = -1;
						break;
					case 1:
						self::$ownerID = $params->get('group_auth');
						break;
					case 2:
						self::$ownerID = $params->get('site_auth');
						break;
				}
			}
		//var_dump(self::$instanceType,self::$ownerID);
		}
	}

}