<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    chat
 */

class Hook_Profiles_Tabs_prizes
{

  /**
   * Find whether this hook is active.
   *
   * @param  MEMBER      The ID of the member who is being viewed
   * @param  MEMBER      The ID of the member who is doing the viewing
   * @return boolean    Whether this hook is active
   */
  function is_active($member_id_of,$member_id_viewing)
  {
    return true;
  }

  /**
   * Standard modular render function for profile tab hooks.
   *
   * @param  MEMBER      The ID of the member who is being viewed
   * @param  MEMBER      The ID of the member who is doing the viewing
   * @param  boolean    Whether to leave the tab contents NULL, if tis hook supports it, so that AJAX can load it later
   * @return array      A triple: The tab title, the tab contents, the suggested tab order
   */
  function render_tab($member_id_of,$member_id_viewing,$leave_to_ajax_if_possible=false)
  {

    $title="Prizes";
    $order=70;


    // Friends

      $rows=$GLOBALS['SITE_DB']->query('SELECT * FROM '.$GLOBALS['SITE_DB']->get_table_prefix().'prize_awards WHERE user_ID = '.$member_id_of.' ORDER BY ID DESC',50);
      $prize_map = array();
      //$rows=array(array('member_liked'=>2,'member_likes'=>3),array('member_liked'=>3,'member_likes'=>2));
    //$done_already=array();
    // $all_usergroups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list(true,false,false,NULL,$member_id_of);
      foreach ($rows as $i=>$row)
      {
        $f_id=$row['prize_ID'];
        //if (array_key_exists($f_id,$done_already)) continue;

        //if (($f_id==$row['member_likes']) || (!in_array($f_id,$blocked)))
        //{
        //$box=render_member_box($f_id,false,NULL,NULL,true,($f_id==$member_id_viewing || $member_id_of==$member_id_viewing)?array($mutual_label=>do_lang($appears_twice?'YES':'NO')):NULL);
        //if ($box->is_empty()) continue;
        $prize = array();
        require_code('catalogues');
        $prize = get_catalogue_entry_field_values(NULL,$f_id);
        $ev=$prize[1]['effective_value'];
        $dereference_ev=is_object($prize[1]['effective_value'])?$prize[1]['effective_value']->evaluate():$prize[1]['effective_value'];
        
        //if ((!is_null($ev)) && ($dereference_ev!=''))
        // {
          require_code('images');
          $prizes=do_image_thumb($dereference_ev,($i==1)?'':(is_object($prize[1])?$prize[1]->evaluate():$prize[1]),false,false);
        //} else
        // {
        //  $prize[1]= new ocp_tempcode();
        //}
        
        
          $prize_map[]=array('PRIZENAME'=>$prize[0]['effective_value'],'PRIZEIMAGE'=>$prizes->evaluate(),'URL'=>build_url(array('page'=>'catalogues','type'=>'entry','id'=>$f_id),get_module_zone('catalogues')));

        $done_already[$f_id]=1;
      }

    $content=do_template('CNS_MEMBER_PROFILE_PRIZES',array('MEMBER_ID'=>strval($member_id_of),'PRIZES_FORWARD'=>$prize_map));

    return array($title,$content,$order,'menu/adminzone/setup/awards');
  }

}

