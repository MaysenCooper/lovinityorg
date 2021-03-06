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
class Hook_cron_credibility
{

    public function run()
    {

// Due to the complexity, it will only be calculated once every 15 minutes.
		$_last_run = get_value('last_time_cron__credibility', null, true);
       	$last_run = is_null($_last_run) ? 0 : intval($_last_run);
        if ($last_run < time() - (60 * 15)) {
			set_value('last_time_cron__credibility', strval(time()), true);
			
			require_code('content');
			
			$credibility = array();
			$joinstamp = array();
			$lastloginstamp = array();
			$numberposts = array();
			$numberprosecutions = array();
			$isbanned = array();
			$credibilitypower = array();
			
			// first, build an array of credibility for all members and set them to 0
			$members = $GLOBALS['SITE_DB']->query_select('f_members', array('*'), null);
			foreach ($members as $member)
			{
				$credibility[$member['id']] = 0;
				// we separate credibility gains and losses into two variables because we don't want things that
				// fractionalize credibility to fractionalize the bad... we want it to only do the good.
				$credibilityp[$member['id']] = 0;
				$credibilitym[$member['id']] = 0;
				$credibilitypower[$member['id']] = 0;
				$credibilitymstaff[$member['id']] = 0;
				$credibilitymuser[$member['id']] = 0;
				$credibilitypstaff[$member['id']] = 0;
				$credibilitypuser[$member['id']] = 0;
				$joinstamp[$member['id']] = $member['m_join_time'];
				$lastloginstamp[$member['id']] = $member['m_last_visit_time'];
				$numberposts[$member['id']] = $member['m_cache_num_posts'];
				$numberprosecutions[$member['id']] = $member['m_cache_warnings'];
				$isbanned[$member['id']] = $member['m_is_perm_banned'];
				
				// We need to calculate how much influence members have on other members' credibilities.
				
				if ($numberposts[$member['id']] > 4) // first power comes from having 5 or more posts
					$credibilitypower[$member['id']]++;
				if ($numberposts[$member['id']] > 199) // give another power point if post count 200 or more
					$credibilitypower[$member['id']]++;
				if ($numberposts[$member['id']] > 499) // give another power point if post count 500 or more
					$credibilitypower[$member['id']]++;
				if (($lastloginstamp[$member['id']] - $joinstamp[$member['id']]) >= (60 * 60 * 24 * 90)) // another power point if the last time the user logged in was 90 or more days past when the user registered
					$credibilitypower[$member['id']]++;
				if (($lastloginstamp[$member['id']] - $joinstamp[$member['id']]) >= (60 * 60 * 24 * 365)) // another power point if the last time the user logged in was 1 or more years past when the user registered
					$credibilitypower[$member['id']]++;
					
				if ($isbanned[$member['id']] == 1) // Permanently banned member = no credibility power
					$credibilitypower[$member['id']] = 0;
				if ($credibilitypower[$member['id']] > 1 && $numberprosecutions[$member['id']] > 1) // lose one power point if member would have had 2 or more, but has had 2 or more prosecutions in their lifetime
					$credibilitypower[$member['id']]--;
				if ($credibilitypower[$member['id']] > 0 && $numberprosecutions[$member['id']] > 3) // if user has earned 4 or more prosecutions in their lifetime, the HIGHEST influence they can possibly have on credibility is 1.
					$credibilitypower[$member['id']] = 1;
			}
			
			
			// Start off by getting all the feedback
			$ratings = $GLOBALS['SITE_DB']->query_select('rating', array('*'), null);
			foreach ($ratings as $rating)
			{
				$_content_type = $rating['rating_for_type'];
				$content_id = $rating['rating_for_id'];
				$content_type = convert_composr_type_codes('feedback_type_code', $_content_type, 'content_type');
				if ($content_type != '') {
					$cma_ob = get_content_object($content_type);
					$info = $cma_ob->info();
					list( ,$submitter_id, , , , ) = content_get_details($content_type, $content_id);
				}

				// if the submitter ID AND the ID of the rating user both exist, then do credibility. Otherwise, no credibility.
				if (array_key_exists($submitter_id,$credibility) && array_key_exists($rating['rating_member'],$credibility))
				{
					if ($rating['rating'] > 5) // rating > 5 = add credibility
					{
						$credibilityp[$submitter_id] += $credibilitypower[$rating['rating_member']];
						if ($GLOBALS['FORUM_DRIVER']->is_staff($rating['rating_member']))
						{
							$credibilitypstaff[$submitter_id] += $credibilitypower[$rating['rating_member']];
						} else {
							$credibilitypuser[$submitter_id] += $credibilitypower[$rating['rating_member']];
						}
					} else { // rating <= 5 = lose credibility
						$credibilitym[$submitter_id] += $credibilitypower[$rating['rating_member']];
						if ($GLOBALS['FORUM_DRIVER']->is_staff($rating['rating_member']))
						{
							$credibilitymstaff[$submitter_id] += $credibilitypower[$rating['rating_member']];
						} else {
							$credibilitymuser[$submitter_id] += $credibilitypower[$rating['rating_member']];
						}
					}
				}
			}
			
			// Next, we're going to add credibility for when users receive gift points from others.
			// This only accounts for when users receive gift points for a role. All other gift point transactions don't count.
			// Weight: 0 = no credibility. 1 = credibility for every 25 points given. 2 = 1 for every 20. 3 = 1 for every 15. 4 = 1 for every 10. 5 = 1 for every 5.
			$where = ' WHERE ' . $GLOBALS['SITE_DB']->translate_field_ref('reason') . ' LIKE \'' . db_encode_like('Wise Mind: %') . '\'
			OR ' . $GLOBALS['SITE_DB']->translate_field_ref('reason') . ' LIKE \'' . db_encode_like('Supportive Soul: %') . '\'
			OR ' . $GLOBALS['SITE_DB']->translate_field_ref('reason') . ' LIKE \'' . db_encode_like('Randomly Kind: %') . '\'
			OR ' . $GLOBALS['SITE_DB']->translate_field_ref('reason') . ' LIKE \'' . db_encode_like('Busy for Good: %') . '\'
			OR ' . $GLOBALS['SITE_DB']->translate_field_ref('reason') . ' LIKE \'' . db_encode_like('Warm and Friendly: %') . '\'
			OR ' . $GLOBALS['SITE_DB']->translate_field_ref('reason') . ' LIKE \'' . db_encode_like('Community Guard: %') . '\'
			OR ' . $GLOBALS['SITE_DB']->translate_field_ref('reason') . ' LIKE \'' . db_encode_like('Role Model: %') . '\'
			';
			$gifts = $GLOBALS['SITE_DB']->query_select('gifts', array('*'), null, $where);
			foreach ($gifts as $gift)
			{
				if (array_key_exists($gift['gift_to'],$credibility) && array_key_exists($gift['gift_from'],$credibility) && $credibilitypower[$gift['gift_from']] > 0)
				{
					$credits = intval($gift['amount'] / (30 - (5 * $credibilitypower[$gift['gift_from']])));
					$credibilityp[$gift['gift_to']] += $credits;
						if ($GLOBALS['FORUM_DRIVER']->is_staff($gift['gift_from']))
						{
							$credibilitypstaff[$gift['gift_to']] += $credits;
						} else {
							$credibilitypuser[$gift['gift_to']] += $credits;
						}
				}
			}
			
			// Next, add credibility for every virtual gift received, weighted based on user's credibility power.
			/*
			$gifts = $GLOBALS['SITE_DB']->query_select('members_gifts', array('*'), null);
			foreach ($gifts as $gift)
			{
				if (array_key_exists($gift['to_member_id'],$credibility) && array_key_exists($gift['from_member_id'],$credibility))
				{
					$credibilityp[$gift['to_member_id']] += $credibilitypower[$gift['from_member_id']];
						if ($GLOBALS['FORUM_DRIVER']->is_staff($gift['from_member_id']))
						{
							$credibilitypstaff[$gift['to_member_id']] += $credibilitypower[$gift['from_member_id']];
						} else {
							$credibilitypuser[$gift['to_member_id']] += $credibilitypower[$gift['from_member_id']];
						}
				}
			}
			*/

			// Next, add credibility for having content featured by staff. One award = 10 credibility.
			$awards = $GLOBALS['SITE_DB']->query_select('award_archive', array('*'), null);
			foreach ($awards as $award)
			{
				if (array_key_exists($award['member_id'],$credibility))
				{
					$credibilityp[$award['member_id']] += 10;
					$credibilitypstaff[$award['member_id']] += 10;
				}
			}
			

			foreach ($credibility as $key => $value)
			{
				
			// Time for the fun part (muahaha). 
			// For every prosecution ever earned, lose 50 credibility.
			$credibilitym[$key] = $credibilitym[$key] + (50 * $numberprosecutions[$key]);
			$credibilitymstaff[$key] = $credibilitymstaff[$key] + (50 * $numberprosecutions[$key]);
			}
			
			// There are 15 custom fields relating to a user's reputation on site that staff members
			// keep track of. All 15 of these fields are an integer between 0 and 100. The higher
			// the number, the less reputable the user is for whatever the field describes.
			// So, we're going to fractionalized earned credibility depending on how bad their
			// reputation is as marked by staff. The worse the reputation, the more their earned
			// credibility will be fractionalized, which means the harder it is to earn credibility.
			// We're going to fractionalize at a rate of 5% for every 100 points of bad reputation.
			
			$members2 = $GLOBALS['SITE_DB']->query_select('f_member_custom_fields', array('*'), null);
			foreach ($members2 as $member)
			{
				$multiplier = 1;
				if (array_key_exists($member['mf_member_id'],$credibility))
				{
					$multiplier -= (0.0005 * $member['field_49']);
					$multiplier -= (0.0005 * $member['field_50']);
					$multiplier -= (0.0005 * $member['field_51']);
					$multiplier -= (0.0005 * $member['field_52']);
					$multiplier -= (0.0005 * $member['field_53']);
					$multiplier -= (0.0005 * $member['field_54']);
					$multiplier -= (0.0005 * $member['field_55']);
					$multiplier -= (0.0005 * $member['field_56']);
					$multiplier -= (0.0005 * $member['field_57']);
					$multiplier -= (0.0005 * $member['field_58']);
					$multiplier -= (0.0005 * $member['field_59']);
					$multiplier -= (0.0005 * $member['field_60']);
					$multiplier -= (0.0005 * $member['field_61']);
					$multiplier -= (0.0005 * $member['field_62']);
					$multiplier -= (0.0005 * $member['field_63']);
					
					$credibilitymstaff[$member['mf_member_id']] += intval($credibilityp[$member['mf_member_id']] * (1 - $multiplier));
					$credibilityp[$member['mf_member_id']] = $credibilityp[$member['mf_member_id']] * $multiplier;
					$credibilityp[$member['mf_member_id']] = intval($credibilityp[$member['mf_member_id']]);
				}
			}
			
			// finalize credibility calculations
			foreach ($credibility as $key => $value)
			{
				$credibility[$key] = intval($credibilityp[$key] - $credibilitym[$key]);
				
				// If user is permanently banned, they get lowest possible credibility: -500
				if ($isbanned[$key] == 1)
				{
					$credibility[$key] = -500;
					$credibilitypstaff[$key] = 0;
					$credibilitypuser[$key] = 0;
					$credibilitymuser[$key] = 0;
					$credibilitymstaff[$key] = 500;
				}
					
				// Finally, store the new credibilities
				$updater = $GLOBALS['SITE_DB']->query_update('f_member_custom_fields',array('field_64' => $credibility[$key], 'field_65' => $credibilitymstaff[$key], 'field_66' => $credibilitymuser[$key], 'field_67' => $credibilitypuser[$key], 'field_68' => $credibilitypstaff[$key]),array('mf_member_id' => $key));
			}
		}
	}
}
