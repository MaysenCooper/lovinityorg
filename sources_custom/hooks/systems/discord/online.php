<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core
 */

/**
 * Hook class.
 */
class Hook_discord_online
{

	public function help()
	{
		return array(
		'!online|(username or user ID)' => 'Check to see if the given lovinity.org username or ID is currently on the website. If not, bot will tell you how long since they were last on.'
		);
	}
	
	public function run($params, $userid, $username = 'guest')
	{
		if (!array_key_exists(1,$params))
			{
				$fields['ERROR'] = 'You need to specify a username or user ID to check the online status.';
				return array('title' => '!online', 'color' => '16711680', 'fields' => $fields);
			}
		
		if (!is_numeric($params[1]))
			$params[1] = $GLOBALS['FORUM_DRIVER']->get_member_from_username($params[1]);

		if (is_null($params[1]))
			{
				$fields['ERROR'] = 'That member does not exist on TLC+.';
				return array('title' => '!online', 'color' => '16711680', 'fields' => $fields);
			}

		$last_visit_time = $GLOBALS['FORUM_DRIVER']->get_member_row_field($params[1], 'm_last_visit_time');
		require_code('users2');

        	if (member_is_online($params[1]))
		{
			$onlinestate = 30 - (integer_format((time() - $last_visit_time)/60) > 15 ? 15 : integer_format((time() - $last_visit_time)/60));
			if ($onlinestate > 25)
			{
				$fields['Status'] = 'That member is currently ONLINE.';
				return array('title' => '!online', 'color' => '65280', 'fields' => $fields);
			}
				$fields['Status'] = 'That member is currently ONLINE (but idle).';
				return array('title' => '!online', 'color' => '16776960', 'fields' => $fields);
		} else {
			$fields['Status'] = 'That member is currently OFFLINE.';
           $minutes_ago = intval(floor((floatval(time() - $last_visit_time) / 60.0)));
            $hours_ago = intval(floor((floatval(time() - $last_visit_time) / 60.0 / 60.0)));
            $days_ago = intval(floor((floatval(time() - $last_visit_time) / 60.0 / 60.0 / 24.0)));
            $months_ago = intval(floor((floatval(time() - $last_visit_time) / 60.0 / 60.0 / 24.0 / 31.0)));
            if ($minutes_ago < 180) {
                $fields['Last Seen'] = integer_format($minutes_ago) . ' minutes ago.';
            } elseif ($hours_ago < 72) {
				$fields['Last Seen'] = integer_format($hours_ago) . ' hours ago.';
            } elseif ($days_ago < 93) {
				$fields['Last Seen'] = integer_format($days_ago) . ' days ago.';
            } else {
				$fields['Last Seen'] = integer_format($months_ago) . ' months ago.';
            }
            return array('title' => '!online', 'color' => '16744448', 'fields' => $fields);
	}

				$fields['ERROR'] = 'An unknown error occurred.';
				return array('title' => '!online', 'color' => '16711680', 'fields' => $fields);

	}
	
}