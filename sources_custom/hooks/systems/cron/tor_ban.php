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
class Hook_cron_tor_ban
{

    public function run()
    {

	// Check every 15 minutes for TOR exit nodes. Ban them all for 14 days!
		/*
	$_last_run = get_value('last_time_cron_TOR', null, true);
       	$last_run = is_null($_last_run) ? 0 : intval($_last_run);
        if ($last_run < time() - (60 * 15)) {
			set_value('last_time_cron_TOR', strval(time()), true);
	require_code('failure');

	$torlist = http_download_file('https://check.torproject.org/cgi-bin/TorBulkExitList.py?ip=149.56.30.127&port=');
	$toraddresses = explode("\n", $torlist);
	foreach ($toraddresses as $ipaddr)
	{
		if (strpos($ipaddr, '#') === false)
			add_ip_ban($ipaddr, '(CRON) TOR exit node 14-day ban', time() + (60 * 60 * 24 * 14));	
	}

	}
		*/

	}
}