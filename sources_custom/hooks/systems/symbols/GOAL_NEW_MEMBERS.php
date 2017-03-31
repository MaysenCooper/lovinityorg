<?php

/*
This can be used to find percentile of goal met for new members.

param[0] = number of total registered members when goal started.
param[1] = target goal of number of total registered members.
param[2] = 1: return percent; 2: return number current members in Javascript form.
*/

/**
 * Hook class.
 */
class Hook_symbol_GOAL_NEW_MEMBERS
{
    public function run($param)
    {

        if (!array_key_exists(0, $param) || !array_key_exists(1, $param)) {
            return '';
        }

        $before_goal = intval($param[0]);
		$target_goal = intval($param[1]);
		$goal = $target_goal - $before_goal;
		
		$current = $GLOBALS['SITE_DB']->query_select_value_if_there('f_members', 'count(*)', array('m_validated' => 1, 'm_is_perm_banned' => 0)) - 1;
		
		$percent = $current;
		
		if ($percent > $target_goal)
			$percent = $target_goal;
		
		if ($param[2] == 2)
			return 'parseInt(' . $current . ' * progress)';
		return $percent;
    }
}
