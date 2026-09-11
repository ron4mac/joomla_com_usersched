<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
defined('_JEXEC') or die;

use Joomla\CMS\Object\CMSObject;

/**
 * Mock JSite class used to fool the frontend search plugins because they route the results.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_usersched
 */
class JSite extends CMSObject
{
	/**
	 * False method to fool the frontend search plugins
	 */
	public function getMenu ()
	{
		return new JSite();
	}

	/**
	 * False method to fool the frontend search plugins
	 */
	public function getItems ()
	{
		return [];
	}
}
