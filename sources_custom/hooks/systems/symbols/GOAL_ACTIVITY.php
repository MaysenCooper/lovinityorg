<?php

/*
This can be used to find percentile of goal met for new members.

param[0] = timestamp when goal started.
param[1] = Activity target level.
*/

/**
 * Hook class.
 */
class Hook_symbol_GOAL_ACTIVITY
{
    public function run($param)
    {

        if (!array_key_exists(0, $param) || !array_key_exists(1, $param)) {
            return '';
        }

        $timestamp = intval($param[0]);
		$goal = intval($param[1]);
		$current = 0;
		
		$wherestring = "(the_type LIKE 'ADD%' OR the_type LIKE 'FILEDUMP_UPLOAD' OR the_type LIKE 'STATUS_UPDATE' OR the_type LIKE 'GIVE%' OR the_type LIKE 'MAKE%' OR the_type LIKE 'WIKI_ADD_PAGE') AND date_and_time > " . $timestamp;
		
		$actions = $GLOBALS['SITE_DB']->query_select('actionlogs', array('*'), null, 'WHERE ' . $wherestring);
		
		foreach ($actions as $action)
		{
			
			$weight = 0;
			switch ($action['the_type'])
			{
				case 'ADD_AWARD_TYPE':
					$weight = 10;
					break;
				case 'ADD_BANNER':
					$weight = 25;
					break;
				case 'ADD_BANNER_TYPE':
					$weight = 10;
					break;
				case 'ADD_CALENDAR_EVENT':
					$weight = 50;
					break;
				case 'ADD_CATALOGUE':
					$weight = 50;
					break;
				case 'ADD_CATALOGUE_CATEGORY':
					$weight = 0;
					break;
				case 'ADD_CATALOGUE_ENTRY':
					$weight = 50;
					break;
				case 'ADD_CHATROOM':
					$weight = 50;
					break;
				case 'ADD_CUSTOM_COMCODE_TAG':
					$weight = 0;
					break;
				case 'ADD_CUSTOM_PROFILE_FIELD':
					$weight = 0;
					break;
				case 'ADD_DOWNLOAD_CATEGORY':
					$weight = 10;
					break;
				case 'ADD_EVENT_TYPE':
					$weight = 10;
					break;
				case 'ADD_FORUM':
					$weight = 100;
					break;
				case 'ADD_FORUM_GROUPING':
					$weight = 10;
					break;
				case 'ADD_GALLERY':
					$weight = 25;
					break;
				case 'ADD_GROUP':
					$weight = 100;
					break;
				case 'ADD_IMAGE':
					$weight = 25;
					break;
				case 'ADD_MENU_ITEM':
					$weight = 0;
					break;
				case 'ADD_NEWS':
					$weight = 50;
					break;
				case 'ADD_NEWS_CATEGORY':
					$weight = 10;
					break;
				case 'ADD_POLL':
					$weight = 25;
					break;
				case 'ADD_POST':
					$weight = 10;
					$forum_id = $GLOBALS['SITE_DB']->query_select_value_if_there('f_topics', 't_forum_id', array('id' => $action['param_b']));
					if ($forum_id === null)
						$weight = 2;
					break;
				case 'ADD_QUIZ':
					$weight = 50;
					break;
				case 'ADD_THEME':
					$weight = 0;
					break;
				case 'ADD_TICKET_TYPE':
					$weight = 0;
					break;
				case 'ADD_TOPIC':
					$weight = 50;
					if ($action['param_b'] === '')
						$weight = 10;
					break;
				case 'ADD_VIDEO':
					$weight = 50;
					break;
				case 'ADD_WORDFILTER':
					$weight = 0;
					break;
				case 'ADD_ZONE':
					$weight = 0;
					break;
				case 'FILEDUMP_UPLOAD':
					$weight = 10;
					break;
				case 'GIVE_AWARD':
					$weight = 25;
					break;
				case 'MAKE_FRIEND':
					$weight = 10;
					break;
				case 'STATUS_UPDATE':
					$weight = 2;
					break;
				case 'WIKI_ADD_PAGE':
					$weight = 25;
					break;
				default: // this is for all action log types returned by our WHERE condition but didn't have a switch case.
					$weight = 25;
					break;
			}
			
			$current += $weight;
		}
		
		$percent = $current / $goal;
		
		if ($percent > 1)
			$percent = 1;
		
		return $percent;
    }
}
