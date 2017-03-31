<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    password_censor
 */

/**
 * Hook class.
 */
class Hook_startup_messages_global
{
    public function run()
    {
/*
    require_code('site');
    $messages = $GLOBALS['SITE_DB']->query_select('messages_global', array('message', 'type'), NULL, 'WHERE `begin` <= ' . time() . ' AND (`end` > ' . time() . ' OR `end` = 0) ORDER BY begin DESC');
        foreach ($messages as $message) {
            if ($GLOBALS['XSS_DETECT']) {
                ocp_mark_as_escaped($message['message']);
            }
            attach_message(protect_from_escaping($message['message']), $message['type']);
        }
*/
    }
}