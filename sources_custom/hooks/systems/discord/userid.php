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
class Hook_discord_userid
{
	public function help()
	{
		return array(
		'!userid' => 'Learn how you can link your Discord account to your lovinity.org account for full bot access.'
		);
	}
	
	public function run($params, $userid, $username = 'guest')
	{
				$theuser = 'Not Linked!';
				require_code('rp');
				$user = rp_get_user_by_discord($userid);
				if ($user != 0)
					$theuser = $GLOBALS['FORUM_DRIVER']->get_username($user);
		$fields['Your Discord ID'] = $userid;
		$fields['lovinity.org Username Linked to this ID'] = $theuser;
		$fields['Instructions'] = 'Log in to your TLC+ account and then go to https://lovinity.org/members/view.htm . Click the edit tab, then the profile tab, and put this user ID in the Discord ID field. That way, you will have full access to some of the other commands with this bot.';
		return array('title' => '!userid', 'color' => '16744703', 'fields' => $fields);
	}
	
}