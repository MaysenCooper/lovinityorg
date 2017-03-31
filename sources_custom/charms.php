<?php

/*
 * Function award_charm. Awards the specified charm to the specified member.
 * 
 * @param  int $member_id the ID of the member being awarded the charm.
 * @param  int $charm the catalogue_entry meta ID of the charm being awarded to the member.
 * @return  BOOL whether or not the charm was awarded.
 */

function award_charm($member_id, $charm)
{
$success = true;
$charmmembers = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_long', 'cv_value', array('cf_id' => '68', 'ce_id' => $charm));
$charmmembers2 = preg_split("/[\r\n]+/", $charmmembers);
			foreach ($charmmembers2 as $member)
			{
				if ($member == $member_id)
				{
					$success = false;
				}
			}

if ($success)
{
	if ($charmmembers == null)
	{
		$charmmembers = $member_id;
	} else {
		$charmmembers = $charmmembers . '\n' . $member_id;
	}
	$GLOBALS['SITE_DB']->query("UPDATE `43SO_catalogue_efv_long` SET `cv_value` = '" . $charmmembers . "' WHERE `cf_id` = 68 AND `ce_id` = " . $charm);
return true;
}

return false;

}

/*
 * Function retrieve_charms. Get the charms of the specified member ID. Also refreshes charms cache is stale.
 * 
 * @param  int $member_id_of the ID of the member we want to get the awarded charms of.
 * @return  TEXT a serialised listing of the charms awarded to the specified member.
 */

function retrieve_charms($member_id_of)
	{
	require_code("caches");
	require_code("caches2");
	$cache_identifier = serialize($member_id_of); 
	$_earned_charms = get_cache_entry('earned_charms', $cache_identifier, CACHE_AGAINST_NOTHING_SPECIAL, 60);
		if ($_earned_charms !== null)
			return $_earned_charms;
	  
    $rows=$GLOBALS['SITE_DB']->query('SELECT * FROM '.$GLOBALS['SITE_DB']->get_table_prefix().'catalogue_efv_long WHERE cf_id = 68');
	$rows2=$GLOBALS['SITE_DB']->query('SELECT id FROM '.$GLOBALS['SITE_DB']->get_table_prefix().'f_members');
		$charms = array();
		$usercharms = array();
		foreach ($rows2 as $row2)
			$usercharms[$row2['id']] = array();
		
		foreach ($rows as $row)
		{
			$charms[$row['ce_id']] = preg_split("/[\r\n]+/", $row['cv_value']);
			
			foreach ($rows2 as $row2)
			{
				
				if (in_array($row2['id'], $charms[$row['ce_id']]))
					array_push($usercharms[$row2['id']], $row['ce_id']);
			}
		}
		
		foreach ($rows2 as $row2)
		{
			$thecharm = serialize($usercharms[$row2['id']]);
			$cache_identifier = serialize($row2['id']);
			put_into_cache('earned_charms', 60 * 60, $cache_identifier, null, null, '', null, '', $thecharm);
		}
		
		return serialize($usercharms[$member_id_of]);
		
	}
	
	
/*
 * Function get_charms__entries. Get Tempcode of all the charms the user earned, in catalogue entry format.
 * 
 * @param  int $member_id_of the ID of the member we want to get the awarded charms of.
 * @return  Tempcode template containing the charms that the user earned in catalogue format.
 */
function get_charms__entries($member_id_of)
{
	require_css('catalogues');
	require_code('catalogues');
	require_code('images');
	  
	$charms = unserialize(retrieve_charms($member_id_of));
	  $prize_map = array();
	  $done_already = array();
	  $prize = array();
	  foreach ($charms as $key => $value)
	  {
		  if ($value !== 0)
		  {
		 $prize = get_catalogue_entry_field_values(NULL,$value);
		 $ev=$prize[1]['effective_value'];
		 $dereference_ev=is_object($prize[1]['effective_value'])?$prize[1]['effective_value']->evaluate():$prize[1]['effective_value'];
		 $prizes=do_image_thumb($dereference_ev,$prize[0]['effective_value'],false,false,128,128);
		 $prize_map[]=array('PRIZENAME'=>$prize[0]['effective_value'],'PRIZEIMAGE'=>$prizes->evaluate(),'URL'=>build_url(array('page'=>'catalogues','type'=>'entry','id'=>$value),get_module_zone('catalogues')));
		 }
		 $done_already[$value]=1;
	  }

    return do_template('CNS_MEMBER_PROFILE_PRIZES',array('MEMBER_ID'=>strval($member_id_of),'PRIZES_FORWARD'=>$prize_map));
}
	
/*
 * Function get_charms__icons. Get Tempcode of all the charms the user earned, in tiny icon format.
 * 
 * @param  int $member_id_of the ID of the member we want to get the awarded charms of.
 * @return  Tempcode template containing the charms that the user earned in icon format.
 */
function get_charms__icons($member_id_of)
{
	        require_code('catalogues');
			require_code('images');
			
	        $charms = unserialize(retrieve_charms($member_id_of));
			$prize_map = array();
	  		$done_already = array();
	  		$prize = array();
	  		foreach ($charms as $key => $value)
	  		{
				if ($value !== 0)
		  		{
					$prize = get_catalogue_entry_field_values(NULL,$value);
		 			$ev=$prize[1]['effective_value'];
		 			$dereference_ev=is_object($prize[1]['effective_value'])?$prize[1]['effective_value']->evaluate():$prize[1]['effective_value'];
		 			$prizes=do_image_thumb($dereference_ev,$prize[0]['effective_value'],false,false,16,16);
		 			$prize_map[]=array('PRIZENAME'=>$prize[0]['effective_value'],'PRIZEIMAGE'=>$prizes->evaluate(),'URL'=>build_url(array('page'=>'catalogues','type'=>'entry','id'=>$value),get_module_zone('catalogues')));
		 		}
		 	$done_already[$value]=1;
	  		}
	  		return do_template('CNS_TOPIC_POST_CHARMS',array('MEMBER_ID'=>strval($member_id_of),'PRIZES_FORWARD'=> $prize_map));
}
