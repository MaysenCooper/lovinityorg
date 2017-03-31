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
class Hook_symbol_ENCRYPT_ID
{
    /**
     * Run function for symbol hooks. Searches for tasks to perform.
     *
     * @param  array $param Symbol parameters
     * @return string Result
     */
    public function run($param)
    {
        $value = '';

        if (!is_guest(get_member())) {
            require_code('encryption');
            if (is_encryption_enabled()) {
                $value = base64_decode(remove_id(remove_magic_encryption_marker(str_replace('<br />', '', $param[0])), $value));
		//die($param[0]);
            }
        } else {
	    $value = '<b>You are not logged in.</b> Guests cannot view encrypted text.';
	}

        return $value;
    }
}
