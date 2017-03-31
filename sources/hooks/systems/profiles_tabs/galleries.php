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
 * @package    galleries
 */

/**
 * Hook class.
 */
class Hook_profiles_tabs_galleries
{
    /**
     * Find whether this hook is active.
     *
     * @param  MEMBER $member_id_of The ID of the member who is being viewed
     * @param  MEMBER $member_id_viewing The ID of the member who is doing the viewing
     * @return boolean Whether this hook is active
     */
    public function is_active($member_id_of, $member_id_viewing)
    {
        if (!has_privilege($member_id_of, 'have_personal_category', 'cms_galleries')) {
            return false;
        }
        if ($member_id_of == $member_id_viewing) {
            if (!is_null($GLOBALS['SITE_DB']->query_select_value_if_there('galleries', 'is_member_synched', array('is_member_synched' => 1)))) {
                return true; // Can have one, even if don't know
            }
        }
        return ($this->find_num_personal_galleries($member_id_of) > 0); // Does have one
    }

    /**
     * Find number of personal galleries of a member.
     *
     * @param  MEMBER $member_id_of The ID of the member
     * @return integer Number of personal galleries
     */
    private function find_num_personal_galleries($member_id_of)
    {
        static $result = array();
        if (!isset($result[$member_id_of])) {
            $sql = 'SELECT COUNT(*) FROM ' . get_table_prefix() . 'galleries WHERE name LIKE \'' . db_encode_like('member\_' . strval($member_id_of) . '\_%') . '\'';
            $result[$member_id_of] = $GLOBALS['SITE_DB']->query_value_if_there($sql);
        }
        return $result[$member_id_of];
    }

    /**
     * Render function for profile tab hooks.
     *
     * @param  MEMBER $member_id_of The ID of the member who is being viewed
     * @param  MEMBER $member_id_viewing The ID of the member who is doing the viewing
     * @param  boolean $leave_to_ajax_if_possible Whether to leave the tab contents null, if tis hook supports it, so that AJAX can load it later
     * @return array A tuple: The tab title, the tab contents, the suggested tab order, the icon
     */
    public function render_tab($member_id_of, $member_id_viewing, $leave_to_ajax_if_possible = false)
    {
        require_lang('galleries');

        $title = ($this->find_num_personal_galleries($member_id_of) <= 1) ? do_lang_tempcode('GALLERY') : do_lang_tempcode('GALLERIES');

        $order = 30;

        if ($leave_to_ajax_if_possible) {
            return array($title, null, $order, 'menu/rich_content/galleries');
        }

        $galleries = do_block('main_personal_galleries_list', array('member_id' => strval($member_id_of)));

        // Render
        $content = do_template('CNS_MEMBER_PROFILE_GALLERIES', array(
            '_GUID' => 'ede1039bfd9bacfabb1d38e9e6821465',
            'MEMBER_ID' => strval($member_id_of),
            'GALLERIES' => $galleries,
        ));

        return array($title, $content, $order, 'menu/rich_content/galleries');
    }
}
