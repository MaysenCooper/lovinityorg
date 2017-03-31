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
class Hook_symbol_DECRYPT
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

        if ((isset($param[0])) && ($param[0] != '') && (!is_guest(get_member()))) {
            require_code('encryption');
            if (is_encryption_enabled()) {
                $value = decrypt_html($param[0]);
		//die($param[0]);
            }
        } else {
	    $value = '<b>You are not logged in.</b> Guests cannot view encrypted text.';
	}

        return $value;
    }
}
