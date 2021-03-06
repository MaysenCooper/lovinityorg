<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    idolisr
 */

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

require_code('cns_groups');
require_code('cns_members');
require_lang('cns');

$stars = array();
		// popularity
$maxpopularity = intval(get_value('max_star_index', 0));
$thelimit = ($maxpopularity * 0.2);
$sql = 'SELECT mf_member_id,field_44 FROM ' . get_table_prefix() . 'f_member_custom_fields WHERE field_44 >= ' . $thelimit . ' AND mf_member_id != 1 AND mf_member_id != 2';
$sql .= ' ORDER BY field_44 DESC';
$gifts = $GLOBALS['SITE_DB']->query($sql, 5);

$count = 0;
foreach ($gifts as $gift) {
    $member_id = $gift['mf_member_id'];
    $username = $GLOBALS['FORUM_DRIVER']->get_username($member_id, true);
    if (!is_null($username)) {
        $url = $GLOBALS['FORUM_DRIVER']->member_profile_url($member_id);
        $avatar_url = $GLOBALS['FORUM_DRIVER']->get_member_avatar_url($member_id);
        $signature = get_translated_tempcode('gifts', $GLOBALS['FORUM_DRIVER']->get_member_row($member_id), 'm_signature', $GLOBALS['FORUM_DB']);
        $points = $gift['field_44'];
        $rank = get_translated_text(cns_get_group_property(cns_get_member_primary_group($member_id), 'name'), $GLOBALS['FORUM_DB']);

        $stars[] = array(
            'MEMBER_ID' => strval($member_id),
            'USERNAME' => $username,
            'URL' => $url,
            'AVATAR_URL' => $avatar_url,
            'POINTS' => float_format($points),
            'RANK' => $rank,
            'SIGNATURE' => $signature,
        );

        $count++;
    }
}

return do_template('BLOCK_MAIN_STARS', array('_GUID' => '298e81f1062087de02e30d77ff61305d', 'STARS' => $stars));
