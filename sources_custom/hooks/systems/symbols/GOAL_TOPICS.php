<?php

/*
This can be used to find percentile of goal met for new members.

param[0] = timestamp when goal started.
param[1] = forum ID.
param[2] = Goal number of topics, via. action log ADD_TOPIC.
*/

/**
 * Hook class.
 */
class Hook_symbol_GOAL_TOPICS
{
    public function run($param)
    {

        if (!array_key_exists(0, $param) || !array_key_exists(1, $param)) {
            return '';
        }

        $timestamp = intval($param[0]);
		$forum_id = intval($param[1]);
		$goal = intval($param[2]);
		
		$wherestring = "the_type LIKE 'ADD_TOPIC' AND param_b = " . $forum_id . " AND date_and_time > " . $timestamp;
		
		$current = $GLOBALS['SITE_DB']->query_select_value_if_there('actionlogs', 'count(*)', null, 'WHERE ' . $wherestring);
		
		$percent = $current / $goal;
		
		if ($percent > 1)
			$percent = 1;
		
		return $percent;
    }
}
