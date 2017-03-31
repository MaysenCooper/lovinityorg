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
 * @package    tickets
 */

/**
 * Hook class.
 */
class Hook_change_detection_topicview
{
    /**
     * Run function for change_detection hooks. They see if their own something has changed in comparison to sample data.
     *
     * @param  string $data The sample data, serialised and then MD5'd
     * @return boolean Whether the something has changed
     */
    public function run($data)
    {

        $id = get_param_integer('id', null);
        $start = get_param_integer('start_comments', 0);
        $default_max = intval(get_option('forum_posts_per_page'));
        $max = get_param_integer('topic_max', $default_max);
        $num_to_show_limit = get_param_integer('max_comments', intval(get_option('comments_to_show_in_thread')));
        $max_thread_depth = get_param_integer('max_thread_depth', intval(get_option('max_thread_depth')));
        if ($max == 0) {
            $max = $default_max;
        }
        if ($max == 0) {
            $max = 1;
        }
        if (($max > 30) && (!has_privilege(get_member(), 'remove_page_split'))) {
            $max = $default_max;
        }
        
        $view_poll_results = get_param_integer('view_poll_results', 0);
        
		if (get_forum_type() != 'cns') {
            warn_exit(do_lang_tempcode('NO_CNS'));
        } else {
            cns_require_all_forum_stuff();
        }
        require_code('cns_topicview');

            require_code('topics');
            $threaded_topic_ob = new CMS_Topic();
            $topic_info = cns_read_in_topic($id, $start, $max, $view_poll_results == 1);

            // Render posts
            require_code('crypt');
            return md5(serialize($topic_info['num_posts'])) != $data;
	}
}