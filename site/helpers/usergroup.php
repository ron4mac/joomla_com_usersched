<?php
/**
* @package		com_usersched
* @copyright	Copyright (C) 2015-2023 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

use Joomla\CMS\Factory;

function groupTitle ($gid) {
	$db = Factory::getDbo();
    $db->setQuery(
        'SELECT `title`' .
        ' FROM `#__usergroups`' .
        ' WHERE `id` = '. (int) $gid
    );
    $title = $db->loadResult();
	return $title;
}