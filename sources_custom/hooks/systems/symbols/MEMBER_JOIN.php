<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    facebook_support
 */

/**
 * Hook class.
 */
class Hook_symbol_MEMBER_JOIN
{
    public function run($param)
    {
        
	return $GLOBALS['FORUM_DRIVER']->get_member_join_timestamp(get_member());
	
    }
}
