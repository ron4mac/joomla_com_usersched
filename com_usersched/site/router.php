<?php
defined('_JEXEC') or die;

/**
 * @param	array
 * @return	array
 */
function UserSchedBuildRoute(&$query)
{
	$segments = array();

	if (isset($query['view'])) {
		unset($query['view']);
	}
	return $segments;
}

/**
 * @param	array
 * @return	array
 */
function UserSchedParseRoute($segments)
{
	$vars = array();

	$searchword	= array_shift($segments);
	$vars['searchword'] = $searchword;
	$vars['view'] = 'usersched';

	return $vars;
}
