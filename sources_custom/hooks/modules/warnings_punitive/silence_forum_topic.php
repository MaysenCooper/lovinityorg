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
class Hook_warnings_punitive_silence_forum_topic
{
    /**
     * Run function for displaying UI fields for this punitive action in the warnings form.
     */
    public function UI(&$hidden, &$fields)
    {
        require_code('form_templates');

            $member_id = get_param_integer('member_id', get_member());

            $post_id = get_param_integer('post_id', null);
        
        
                 if (!is_null($post_id)) {
                $topic_id = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_posts', 'p_topic_id', array('id' => $post_id));
                if (!is_null($topic_id)) {
                    $forum_id = $GLOBALS['FORUM_DB']->query_select_value('f_topics', 't_forum_id', array('id' => $topic_id));
                    $hidden->attach(form_input_hidden('topic_id', strval($topic_id)));
                    $hidden->attach(form_input_hidden('forum_id', strval($forum_id)));
                    $silence_topic_time = null;//time()+60*60*24*7;
                    $silence_forum_time = null;//time()+60*60*24*7;
                    $active_until = $GLOBALS['SITE_DB']->query_select_value_if_there('member_privileges', 'active_until', array(
                        'member_id' => $member_id,
                        'privilege' => 'submit_lowrange_content',
                        'the_page' => '',
                        'module_the_name' => 'topics',
                        'category_name' => strval($topic_id),
                    ));
                    if (!is_null($active_until)) {
                        $silence_topic_time = $active_until;
                    }
                    $active_until = $GLOBALS['SITE_DB']->query_select_value_if_there('member_privileges', 'active_until', array(
                        'member_id' => $member_id,
                        'privilege' => 'submit_lowrange_content',
                        'the_page' => '',
                        'module_the_name' => 'forums',
                        'category_name' => strval($forum_id),
                    ));
                    if (!is_null($active_until)) {
                        $silence_forum_time = $active_until;
                    }
                    $fields->attach(form_input_date(do_lang_tempcode('SILENCE_FROM_TOPIC'), do_lang_tempcode('DESCRIPTION_SILENCE_FROM_TOPIC'), 'silence_from_topic', false, true, true, $silence_topic_time, 2));
                    $fields->attach(form_input_date(do_lang_tempcode('SILENCE_FROM_FORUM'), do_lang_tempcode('DESCRIPTION_SILENCE_FROM_FORUM'), 'silence_from_forum', false, true, true, $silence_forum_time, 2));
                }
            }
    }
}