<?php

/*
Used for getting the status of an Uptime Robot monitor

param[0] = The monitor API
*/

/**
 * Hook class.
 */
class Hook_symbol_UPTIME_ROBOT
{
    public function run($param)
    {

        if (!array_key_exists(0, $param)) {
            return '';
        }
        
        $json = http_download_file('https://api.uptimerobot.com/getMonitors?apiKey=' . $param[0] . '&format=json&noJsonCallback=1');
        $json = json_decode($json);
        $status = $json->monitors->monitor[0]->status;
        $output = '';
        
        switch ($status)
        {
			case 0:
			$output = '<span style="color: DimGrey;">NOT MONITORED</span>';
			break;
			case 1:
			$output = '<span style="color: DarkSlateGrey;">PENDING CHECK</span>';
			break;
			case 2:
			$output = '<span style="color: DarkGreen;">ONLINE</span>';
			break;
			case 8:
			$output = '<span style="color: SaddleBrown;">POSSIBLY OFFLINE</span>';
			break;
			case 9:
			$output = '<span style="color: Red;">OFFLINE</span>';
			break;
			default:
			$output = '<span style="color: Indigo;">UNKNOWN</span>';
			break;
		}
		
		return $output;

	}

}
