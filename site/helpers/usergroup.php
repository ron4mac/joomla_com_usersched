<?php

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