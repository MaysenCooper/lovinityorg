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
 * @package    core_cns
 */

/**
 * Add a multi moderation to the system.
 *
 * @param  SHORT_TEXT $name The name of the multi moderation.
 * @param  LONG_TEXT $post_text The post text to add when applying (blank: don't add a post).
 * @param  ?AUTO_LINK $move_to The forum to move the topic when applying (null: do not move).
 * @param  ?BINARY $pin_state The pin state after applying (null: unchanged).
 * @param  ?BINARY $sink_state The sink state after applying (null: unchanged).
 * @param  ?BINARY $open_state The open state after applying (null: unchanged).
 * @param  SHORT_TEXT $forum_multi_code The forum multi code for where this multi moderation may be applied.
 * @param  SHORT_TEXT $title_suffix The title suffix.
 * @return AUTO_LINK The ID of the multi moderation just added.
 */
function cns_make_multi_moderation($name, $post_text, $move_to, $pin_state, $sink_state, $open_state, $forum_multi_code = '*', $title_suffix = '')
{
    if (!addon_installed('cns_multi_moderations')) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    require_code('global4');
    prevent_double_submit('ADD_MULTI_MODERATION', null, $name);

    $map = array(
        'mm_post_text' => $post_text,
        'mm_move_to' => $move_to,
        'mm_pin_state' => $pin_state,
        'mm_sink_state' => $sink_state,
        'mm_open_state' => $open_state,
        'mm_forum_multi_code' => $forum_multi_code,
        'mm_title_suffix' => $title_suffix,
    );
    $map += insert_lang('mm_name', $name, 3, $GLOBALS['FORUM_DB']);
    $id = $GLOBALS['FORUM_DB']->query_insert('f_multi_moderations', $map, true);

    log_it('ADD_MULTI_MODERATION', strval($id), $name);

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        generate_resource_fs_moniker('multi_moderation', strval($id), null, null, true);
    }

    return $id;
}