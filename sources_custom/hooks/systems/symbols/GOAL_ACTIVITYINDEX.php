<?php

/*
This can be used to find percentile of goal met for maximum target Activity Index.

param[0] = timestamp when goal started.
param[1] = Activity index target.
param[2] = 1 - return percent float, 2 - return HTML for the strong element.
*/

/**
 * Hook class.
 */
class Hook_symbol_GOAL_ACTIVITYINDEX
{
    public function run($param)
    {

        if (!array_key_exists(0, $param) || !array_key_exists(1, $param) || !array_key_exists(2, $param)) {
            return '';
        }

        $timestamp = intval($param[0]);
		$goal = float_format($param[1]);
		$firststock = $GLOBALS['FORUM_DB']->query_value_if_there('SELECT stock_index FROM `43SO_stock_index` WHERE `timestamp` >= ' . $timestamp . ' ORDER BY `timestamp` ASC');
		$maximum = $GLOBALS['FORUM_DB']->query_value_if_there('SELECT MAX(stock_index) FROM `43SO_stock_index` WHERE `timestamp` >= ' . $timestamp);
		if ($param[2] == 1)
		{
		if ($maximum >= $goal)
		{
			return $maximum;
		} elseif (($goal - $firststock) <= 0) {
			return $firststock;
		} else {
			$percent = (($maximum - $firststock) / ($goal - $firststock));
			return $maximum;
		}
		} else {
			return 'parseFloat(' . $maximum . ' * progress).toFixed(2) + \' max\'';
		}
    }
}
