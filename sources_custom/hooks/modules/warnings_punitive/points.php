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
class Hook_warnings_punitive_points
{
    /**
     * Run function for displaying UI fields for this punitive action in the warnings form.
     */
    public function UI(&$hidden, &$fields)
    {
        require_code('form_templates');
        
            $member_id = get_param_integer('member_id', get_member());
        
            if (addon_installed('points')) {
                if (has_actual_page_access(get_member(), 'admin_points')) {
                    require_code('points');
                    $num_points_currently = available_points($member_id);
                    $fields->attach(form_input_integer(do_lang_tempcode('CHARGED_POINTS'), do_lang_tempcode('DESCRIPTION_CHARGED_POINTS', escape_html(integer_format($num_points_currently))), 'charged_points', 0, true));
                }
            }
      
    }
}