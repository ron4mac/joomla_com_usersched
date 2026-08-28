<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2022-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.4.0
*/
namespace RJCreations\Component\Usersched\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use RJCreations\Component\Usersched\Administrator\Helper\Html\Usched;
use Psr\Container\ContainerInterface;
	
class UserschedComponent extends MVCComponent implements BootableExtensionInterface
{
	use HTMLRegistryAwareTrait;

	public function boot(ContainerInterface $container)
	{
		$this->getRegistry()->register('ushed', new Usched());
	}

}
