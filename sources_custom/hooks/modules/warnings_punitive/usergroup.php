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
 * @package    cns_warnings
 */

/**
 * Hook class.
 */
class Hook_warnings_punitive_usergroup
{
    /**
     * Run function for displaying UI fields for this punitive action in the warnings form.
     */
    public function UI(&$hidden, &$fields)
    {
        require_code('form_templates');
        
                    if (has_privilege(get_member(), 'member_maintenance')) {
            
                $rows = $GLOBALS['FORUM_DB']->query_select('f_groups', array('id', 'g_name'), array('g_is_private_club' => 0));
                $groups = new Tempcode();
                $groups->attach(form_input_list_entry('-1', false, do_lang_tempcode('NA_EM')));
                foreach ($rows as $group) {
                    if ($group['id'] != db_get_first_id()) {
                        $groups->attach(form_input_list_entry(strval($group['id']), false, get_translated_text($group['g_name'], $GLOBALS['FORUM_DB'])));
                    }
                }
                $fields->attach(form_input_list(do_lang_tempcode('CHANGE_USERGROUP_TO'), do_lang_tempcode('DESCRIPTION_CHANGE_USERGROUP_TO'), 'changed_usergroup_from', $groups));
            }
        
}
}