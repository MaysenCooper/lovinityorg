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
 * @package    stats
 */

/**
 * Hook class.
 */
class Hook_realtime_rain_stats
{
    /**
     * Run function for realtime-rain hooks.
     *
     * @param  TIME $from Start of time range.
     * @param  TIME $to End of time range.
     * @return array A list of template parameter sets for rendering a 'drop'.
     */
    public function run($from, $to)
    {
        $drops = array();

        if (has_actual_page_access(get_member(), 'admin_stats')) {
            require_lang('stats');

            $rows = $GLOBALS['SITE_DB']->query('SELECT browser,referer,the_page,ip,member_id,date_and_time AS timestamp FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'stats WHERE date_and_time BETWEEN ' . strval($from) . ' AND ' . strval($to));

            foreach ($rows as $row) {
                $timestamp = $row['timestamp'];
                $member_id = $row['member_id'];

                $page_link = str_replace(':', ': ', page_path_to_page_link($row['the_page']));
                if ($row['the_page'] == '/access_denied') {
                    $page_link = do_lang('ACCESS_DENIED_SCREEN');
                }
                if ($row['the_page'] == '/closed') {
                    $page_link = do_lang('CLOSED_SITE_SCREEN');
                }
                if ($row['the_page'] == '/flood') {
                    $page_link = do_lang('FLOOD_CONTROL_SCREEN');
                }

                $title = rain_truncate_for_title(do_lang('HIT', $page_link));

                // Show referer domain or Google search keyword
                $referer = @parse_url($row['referer']);
                $base_url = @parse_url(get_base_url());
                if ($referer !== false) {
                    if (!array_key_exists('host', $referer)) {
                        $referer['host'] = do_lang('UNKNOWN');
                    }

                    if ($referer['host'] != $base_url['host']) {
                        $matches = array();
                        if (preg_match('#(&|\?)q=([^&]*)#', $row['referer'], $matches) != 0) {
                            $title = rain_truncate_for_title(do_lang('HIT', $page_link, $matches[2], $referer['host']));
                        } else {
                            $title = rain_truncate_for_title(do_lang('HIT', $page_link, $referer['host']));
                        }
                    }
                }

                $drops[] = rain_get_special_icons($row['ip'], $timestamp, $row['browser']) + array(
                        'TYPE' => 'stats',
                        'FROM_MEMBER_ID' => strval($member_id),
                        'TO_MEMBER_ID' => null,
                        'TITLE' => $title,
                        'IMAGE' => is_guest($member_id) ? rain_get_country_image($row['ip']) : $GLOBALS['FORUM_DRIVER']->get_member_avatar_url($member_id),
                        'TIMESTAMP' => strval($timestamp),
                        'RELATIVE_TIMESTAMP' => strval($timestamp - $from),
                        'TICKER_TEXT' => null,
                        'URL' => (addon_installed('securitylogging')) ? build_url(array('page' => 'admin_lookup', 'id' => $row['ip']), '_SEARCH') : null,
                        'IS_POSITIVE' => false,
                        'IS_NEGATIVE' => false,

                        // These are for showing connections between drops. They are not discriminated, it's just three slots to give an ID code that may be seen as a commonality with other drops.
                        'FROM_ID' => 'member_' . strval($member_id),
                        'TO_ID' => null,
                        'GROUP_ID' => 'page_' . $page_link,
                    );
            }
        }

        return $drops;
    }
}
