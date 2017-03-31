<?php

/*
This can be used to find percentile of goal met for maximum target Activity Index.

param[0] = timestamp when the activity index meter should freeze.
*/

/**
 * Hook class.
 */
class Hook_symbol_GOAL_ACTIVITYINDEX_FREEZE
{
    public function run($param)
    {

        if (!array_key_exists(0, $param)) {
            return '';
        }

        $freeze = (time() > $param[0]);
        if ($freeze)
        {
		return $GLOBALS['FORUM_DB']->query_value_if_there('SELECT stock_index FROM `43SO_stock_index` WHERE `timestamp` <= ' . ($param[0] + 60) . ' ORDER BY `timestamp` DESC');
		}
		return get_value('current_activity_index');
    }
}
