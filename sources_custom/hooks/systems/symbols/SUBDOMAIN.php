<?php

/*
This can be used to find percentile of goal met for new members.

param[0] = timestamp when goal started.
param[1] = Activity target level.
*/

/**
 * Hook class.
 */
class Hook_symbol_SUBDOMAIN
{
    public function run($param)
    {

	$domain = $_SERVER['HTTP_HOST'];
	$temp = explode('.',$domain);
	$subdomain = $temp[0];
	if ($subdomain === null || $subdomain === '' || $subdomain === 'lovinity')
		$subdomain = 'main';
	return $subdomain;

    }
}
