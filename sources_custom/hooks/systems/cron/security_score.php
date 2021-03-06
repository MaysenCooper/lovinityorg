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
class Hook_cron_security_score
{

    public function run()
    {

		// SECURITY CHECKS
		// Security rating starts off at 0. The more at risk we feel TLC+ is security wise, the higher the rating. 100 is "alarming".
		$securityscore = 0;

		// Some security checks revolve around the average number of hits per day in the last 30 days.
		$monthlyhits = $GLOBALS['SITE_DB']->query_value_if_there('SELECT COUNT(*) FROM ' . get_table_prefix() . 'stats WHERE date_and_time>' . strval(time() - 60 * 60 * 24 * 30));
		$averagedailyhits = intval($monthlyhits / 30);
		set_value('security_score__monthly_hits', strval($monthlyhits));
		set_value('security_score__daily_hits', strval($averagedailyhits));
		
		// Allow for the registration of 1/10th of the members within the last month in one day. After that, add 5 score per new member as this could be a mass registration attack.
		$monthmembers = $GLOBALS['FORUM_DB']->query_value_if_there('SELECT COUNT(*) FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_members WHERE m_join_time>' . strval(time() - 60 * 60 * 24 * 30));
		$daymembers = $GLOBALS['FORUM_DB']->query_value_if_there('SELECT COUNT(*) FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_members WHERE m_join_time>' . strval(time() - 60 * 60 * 24));
		$securityscore += ($daymembers > ($monthmembers / 10)) ? intval(($daymembers - ($monthmembers / 10)) * 5) : 0;
		set_value('security_score__new_members', strval(($daymembers > ($monthmembers / 10)) ? intval(($daymembers - ($monthmembers / 10)) * 5) : 0));
		
		// For every time the words DOS, hack, mitigat, drill, kill, die, death, secur, breach, threat, crash, virus, troj, delay, disrupt, warn, ware, spam, reboot, restart, shut, worm, cyber, deny, deni, brute, VPN, proxy, or attack appear in page request parameters within the last 24 hours, add 1 to the security score.
		$wordflag = $GLOBALS['SITE_DB']->query_select_value_if_there('stats', 'COUNT(*)', null, ' WHERE date_and_time > ' . (time() - (60 * 60 * 24)) . ' AND ip NOT LIKE \'71.72.161.99\' AND (s_get LIKE \'%dos%\' OR post LIKE \'%dos%\' OR s_get LIKE \'%hack%\' OR post LIKE \'%hack%\' OR s_get LIKE \'%mitigat%\' OR post LIKE \'%mitigat%\' OR s_get LIKE \'%drill%\' OR post LIKE \'%drill%\' OR s_get LIKE \'%kill%\' OR post LIKE \'%kill%\' OR s_get LIKE \'%die%\' OR post LIKE \'%die%\' OR s_get LIKE \'%death%\' OR post LIKE \'%death%\' OR s_get LIKE \'%secur%\' OR post LIKE \'%secur%\' OR s_get LIKE \'%breach%\' OR post LIKE \'%breach%\' OR s_get LIKE \'%threat%\' OR post LIKE \'%threat%\' OR s_get LIKE \'%crash%\' OR post LIKE \'%crash%\' OR s_get LIKE \'%virus%\' OR post LIKE \'%virus%\' OR s_get LIKE \'%troj%\' OR post LIKE \'%troj%\' OR s_get LIKE \'%delay%\' OR post LIKE \'%delay%\' OR s_get LIKE \'%disrupt%\' OR post LIKE \'%disrupt%\' OR s_get LIKE \'%warn%\' OR post LIKE \'%warn%\' OR s_get LIKE \'%ware%\' OR post LIKE \'%ware%\' OR s_get LIKE \'%spam%\' OR post LIKE \'%spam%\' OR s_get LIKE \'%reboot%\' OR post LIKE \'%reboot%\' OR s_get LIKE \'%restart%\' OR post LIKE \'%restart%\' OR s_get LIKE \'%shut%\' OR post LIKE \'%shut%\' OR s_get LIKE \'%worm%\' OR post LIKE \'%worm%\' OR s_get LIKE \'%cyber%\' OR post LIKE \'%cyber%\' OR s_get LIKE \'%deny%\' OR post LIKE \'%deny%\' OR s_get LIKE \'%deni%\' OR post LIKE \'%deni%\' OR s_get LIKE \'%brute%\' OR post LIKE \'%brute%\' OR s_get LIKE \'%vpn%\' OR post LIKE \'%vpn%\' OR s_get LIKE \'%proxy%\' OR post LIKE \'%proxy%\' OR s_get LIKE \'%attack%\' OR post LIKE \'%attack%\')');
		$securityscore += ($wordflag * 1);
		set_value('security_score__flagged_words', strval(($wordflag * 1)));

		// Allow 1 access denied for every 20 requests (daily average). After that, add 1 to the security score for every access denied.
		$accessdeniedallow = intval($averagedailyhits / 20);
		$accessdenied = $GLOBALS['SITE_DB']->query_select_value_if_there('stats', 'COUNT(*)', null, ' WHERE the_page LIKE \'%access_denied%\' AND date_and_time > ' . (time() - (60 * 60 * 24)));
		$securityscore += ($accessdenied > $accessdeniedallow) ? (($accessdenied - $accessdeniedallow) * 1) : 0;
		set_value('security_score__access_denied', strval(($accessdenied > $accessdeniedallow) ? (($accessdenied - $accessdeniedallow) * 1) : 0));
		
		// After daily average number of requests, For every (daily requests / 10) page requests within the last 24 hours, add 1 to the security score.
		$pagerequests = $GLOBALS['SITE_DB']->query_select_value_if_there('stats', 'COUNT(*)', null, ' WHERE date_and_time > ' . (time() - (60 * 60 * 24)));
		$securityscore += ($pagerequests > $averagedailyhits) ? intval(($pagerequests - $averagedailyhits) / intval($averagedailyhits / 10 + 1)) : 0;
		set_value('security_score__page_requests', strval(($pagerequests > $averagedailyhits) ? intval(($pagerequests - $averagedailyhits) / intval($averagedailyhits / 10 + 1)) : 0));
		
		// We are also going to run checks on 5-minute request intervals for quick DDOS detection
		// Take the MAX requests in a 5 minute period within the last 24 hours. Allow up to (daily requests / 10) requests. After that, add 1 score for every (daily requests / 100) requests.
		$minute5 = $GLOBALS['SITE_DB']->query_select_value_if_there('stats', 'COUNT(*)', null, ' WHERE date_and_time > ' . (time() - (60 * 5)));
		$GLOBALS['SITE_DB']->query_insert('requestblocks', array(
            	'requests' => $minute5,
            	'timestamp' => time(),
       		));
       	$GLOBALS['SITE_DB']->query_delete('requestblocks', null, ' WHERE timestamp < ' . (time() - (60 * 60 * 24)));
       	$max5 = $GLOBALS['SITE_DB']->query_select_value_if_there('requestblocks', 'MAX(requests)', null, ' WHERE timestamp > ' . (time() - (60 * 60 * 24)));
       	$securityscore += ($max5 > intval($averagedailyhits / 10)) ? intval(($max5 - intval($averagedailyhits / 10)) / intval($averagedailyhits / 100 + 1)) : 0;
       	set_value('security_score__5_minute_requests', strval(($max5 > intval($averagedailyhits / 10)) ? intval(($max5 - intval($averagedailyhits / 10)) / intval($averagedailyhits / 100 + 1)) : 0));
		
		// For every failed login attempt within the last 24 hours, add 3 to the security score.
		$failedlogins = $GLOBALS['SITE_DB']->query_select_value_if_there('failedlogins', 'COUNT(*)', null, ' WHERE date_and_time > ' . (time() - (60 * 60 * 24)));
		$securityscore += ($failedlogins * 3);
		set_value('security_score__failed_logins', strval(($failedlogins * 3)));
		
		// For every hack attack logged within the last 24 hours, add 25 to the security score.
		$hackattacks = $GLOBALS['SITE_DB']->query_select_value_if_there('hackattack', 'COUNT(*)', null, ' WHERE date_and_time > ' . (time() - (60 * 60 * 24)));
		$securityscore += ($hackattacks * 25);
		set_value('security_score__hack_attacks', strval(($hackattacks * 25)));
		
		// For every use of the forgot password feature in the last 24 hours, add 5 to the security score.
		$forgotpassword = $GLOBALS['SITE_DB']->query_select_value_if_there('actionlogs', 'COUNT(*)', array('the_type' => 'LOST_PASSWORD'), ' AND date_and_time > ' . (time() - (60 * 60 * 24)));
		$securityscore += ($forgotpassword * 5);
		set_value('security_score__forgot_password', strval(($forgotpassword * 5)));
		
		// Finally, calculate manual security score entries.
		$manualscore = $GLOBALS['SITE_DB']->query_select_value_if_there('suspicious', 'SUM(score)', null, ' WHERE timestamp > ' . (time() - (60 * 60 * 24)));
		$securityscore += $manualscore;
		set_value('security_score__manual', strval($manualscore));
		
		// Set the final security score value
		set_value('security_score', strval($securityscore));
		
		// Was the score 100 or above? Take down the site as a precaution!
		if ($securityscore >= 100 && get_option('site_closed') != '1')
		{

	//Discord server push
	require_code('global3');
	require_code('images');
	require_code('addons');
	require_code('config2');
	$thumbfields = array();
	$addonthumb = find_addon_icon('securitylogging');
	$params = array($addonthumb,'36x36','addon_icon_normalise','','','pad','both','#FFFFFF00');
	$ensurethumb = _symbol_thumbnail($params);
	$thumbfields['url'] = $ensurethumb;
	$postparam = array();
	$embedfields = array();
	$embedfields[0] = array('name' => 'Message', 'value' => 'The security risk score has passed the 100 trigger because of very suspicious website activity. As a precaution, the website has been put offline until staff investigate.');
	$embedfields[1] = array('name' => 'Security Score', 'value' => $securityscore);
	$embeds = array();
	$embeds[0] = array('title' => 'lovinity.org has been closed due to security!', 'color' => '16711680', 'thumbnail' => $thumbfields, 'fields' => $embedfields);
	$postparam['content'] = '@everyone';
	$postparam['embeds'] = $embeds;
	$postparam = json_encode($postparam);
	http_download_file('https://discordapp.com/api/webhooks/276127156701102083/AC3U7mWGK04oYIQ0sPITYD7QY60VAMWPMBUU_KxAhdW2SCnFbtw228CHpP4MP8EGi5p_', null, false, true, 'Composr', array($postparam), null, null, null, null, null, null, null, 6.0, true, null, null, null, 'application/json');
	http_download_file('https://discordapp.com/api/webhooks/260827617899446274/zOpixTDLOx4rKLRL1CsSG50_ylvAgsB86UJ6jH9tfXUFSF0nTCfZpl56hIN6ZmURPVak', null, false, true, 'Composr', array($postparam), null, null, null, null, null, null, null, 6.0, true, null, null, null, 'application/json');

        set_option('site_closed', '1');
	    set_option('closed', 'The Lovinity Community+ has been temporarily closed due to a security concern. Staff will investigate and re-open the website as soon as possible. We apologise for the inconvenience.');
		}



	}
}
