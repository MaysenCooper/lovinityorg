<?php

/*
This can be used to find percentile of goal met for new members.

param[0] = timestamp when goal started.
param[1] = Target number of entries.
param[2] = Catalogue codename (string).
param[3] = 1 - return percent float, 2 - return HTML for the strong element.
*/

/**
 * Hook class.
 */
class Hook_symbol_GOAL_CATALOGUE_ENTRIES
{
    public function run($param)
    {

        if (!array_key_exists(0, $param) || !array_key_exists(1, $param) || !array_key_exists(2, $param) || !array_key_exists(3, $param)) {
            return '';
        }

        $timestamp = intval($param[0]);
		$goal = intval($param[1]);
		$catalogue = $param[2];
		$current = 0;
		
		
		$current = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_entries', 'count(*)', array('c_name' => $catalogue), ' AND `ce_submitter` != 2 AND `ce_add_date` >= ' . $timestamp);
		
		$percent = $current / $goal;
		
		if ($percent > 1)
			$percent = 1;
		
		if ($param[3] == 2)
			return $current;
		return $percent;
    }
}
