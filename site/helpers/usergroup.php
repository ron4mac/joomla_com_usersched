<?php

function groupTitle ($gid) {
	$db = JFactory::getDbo();
    $db->setQuery(
        'SELECT `title`' .
        ' FROM `#__usergroups`' .
        ' WHERE `id` = '. (int) $gid
    );
    $title = $db->loadResult();
	return $title;
}