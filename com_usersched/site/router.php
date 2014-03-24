<?php
defined('_JEXEC') or die;

/**
 * @param	array
 * @return	array
 */
function UserschedBuildRoute(&$query)
{
	$segments = array();	//var_dump($query);

	if (isset($query['view'])) {
		$segments[] = $query['view'];
		unset($query['view']);
	}
	if (isset($query['task'])) {
		$segments[] = $query['task'];
		unset($query['task']);
	}

	return $segments;
}

/**
 * @param	array
 * @return	array
 */
function UserschedParseRoute($segments)
{
	$vars = array();

	if ($segments[0]) $vars['view'] = $segments[0];
	if ($segments[1]) $vars['task'] = $segments[1];

	return $vars;
}
