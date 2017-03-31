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
 * @package    core
 */

/**
 * Declare that an action succeeded - delete safety autosave cookies.
 */
function clear_cms_autosave()
{
    static $done_once = false;
    if ($done_once) {
        return;
    }

    if (!function_exists('get_member')) {
        return;
    }

    foreach (array_keys($_COOKIE) as $key) {
        if (substr($key, 0, 13) == 'cms_autosave_') {
            if (strpos($key, get_page_name()) !== false || strpos($key, str_replace('_', '-', get_page_name())) !== false) {
                // Has to do both, due to inconsistencies with how PHP reads and sets cookies -- reading de-urlencodes (although not strictly needed), while setting does not urlencode; may differ between versions
                require_code('users_active_actions');
                cms_setcookie(urlencode($key), '0', false, false, 0);
                cms_setcookie($key, '0', false, false, 0);
            }
        }
    }

    $sql = 'DELETE FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'autosave WHERE a_time<' . strval(time() - 60 * 60 * 24) . ' OR (a_member_id=' . strval(intval(get_member())) . ' AND (a_key LIKE \'%' . db_encode_like(get_page_name()) . '%\'))';
    $GLOBALS['SITE_DB']->query($sql);

    $done_once = true;
}
