<?php

/*
This can be used to find percentile of goal met for maximum target Activity Index.

param[0] = timestamp when goal started.
param[1] = timestamp when goal deadline is.
param[2] = 1 - return percent float, 2 - return HTML for the strong element.
*/

/**
 * Hook class.
 */
class Hook_symbol_GOAL_DATE
{
    public function run($param)
    {

        if (!array_key_exists(0, $param) || !array_key_exists(1, $param) || !array_key_exists(2, $param)) {
            return '';
        }

        $timestamp = intval($param[0]);
	$goal = intval($param[1]);
	$timeleft = ($goal - time());
	if ($timeleft < 0)
		$timeleft = 0;
	$percent = ((time() - $timestamp) / ($goal - $timestamp));
		if ($param[2] == 1)
		{
			if ($percent >= 1)
			{
				return 1;
			} elseif ($percent <= 0) {
				return 0;
			} else {
				return $percent;
			}
		} else {
			return $this->time2string($timeleft);
		}
    }

public function time2string($time) {
    $d = floor($time/86400);
    $_d = ($d < 10 ? '0' : '').$d;

    $h = floor(($time-$d*86400)/3600);
    $_h = ($h < 10 ? '0' : '').$h;

    $m = floor(($time-($d*86400+$h*3600))/60);
    $_m = ($m < 10 ? '0' : '').$m;

    $s = $time-($d*86400+$h*3600+$m*60);
    $_s = ($s < 10 ? '0' : '').$s;

    $time_str = $_d.'D '.$_h.':'.$_m;

    return $time_str;
}
}
