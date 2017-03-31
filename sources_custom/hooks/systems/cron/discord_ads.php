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
 * @package    backup
 */

/**
 * Hook class.
 */
class Hook_cron_discord_ads
{

    public function run()
    {

		// DISCORD ADS
		
		$ads = $GLOBALS['SITE_DB']->query_select('discord_ads', array('*'), null);
		foreach ($ads as $ad)
		{
			if (is_null($ad['next_publish']))
			{
				$nextmidnight = strtotime('+1 day midnight');
				$followingmidnight = strtotime('+2 days midnight');
				$publishtime = mt_rand($nextmidnight, $followingmidnight);
				$updater = $GLOBALS['SITE_DB']->query_update('discord_ads',array('next_publish' => $publishtime),array('ID' => $ad['ID']));
			}
			if (time() >= $ad['next_publish'] && !is_null($ad['next_publish']))
			{

	//Discord server push
	require_code('global3');
	$postparam = array();
	$embedfields = array();
	$embedfields[0] = array('name' => 'Advertisement', 'value' => $ad['message']);
	$embeds = array();
	$embeds[0] = array('title' => 'Please support our sponsors', 'color' => '16777215', 'fields' => $embedfields);
	$postparam['embeds'] = $embeds;
	$postparam['content'] = $ad['mention'];
	$postparam = json_encode($postparam);
	http_download_file('https://discordapp.com/api/webhooks/260827617899446274/zOpixTDLOx4rKLRL1CsSG50_ylvAgsB86UJ6jH9tfXUFSF0nTCfZpl56hIN6ZmURPVak', null, false, true, 'Composr', array($postparam), null, null, null, null, null, null, null, 6.0, true, null, null, null, 'application/json');


				$nextmidnight = strtotime('+1 day midnight');
				$followingmidnight = strtotime('+2 days midnight');
				$publishtime = mt_rand($nextmidnight, $followingmidnight);
				$updater = $GLOBALS['SITE_DB']->query_update('discord_ads',array('next_publish' => $publishtime, 'publishes_left' => ($ad['publishes_left'] - 1)),array('ID' => $ad['ID']));
				if (($ad['publishes_left'] - 1) <= 0)
					$updater = $GLOBALS['SITE_DB']->query_delete('discord_ads',array('ID' => $ad['ID']));
			}
		}

	}
}