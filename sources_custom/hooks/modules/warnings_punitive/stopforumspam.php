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
class Hook_warnings_punitive_stopforumspam
{
    /**
     * Run function for displaying UI fields for this punitive action in the warnings form.
     */
    public function UI(&$hidden, &$fields)
    {
        require_code('form_templates');

            if (addon_installed('securitylogging')) {

                $stopforumspam_api_key = get_option('stopforumspam_api_key');
                if (is_null($stopforumspam_api_key)) {
                    $stopforumspam_api_key = '';
                }
                $tornevall_api_username = get_option('tornevall_api_username');
                if (is_null($tornevall_api_username)) {
                    $tornevall_api_username = '';
                }
                if ($stopforumspam_api_key . $tornevall_api_username != '') {
                    require_lang('submitban');
                    $fields->attach(form_input_tick(do_lang_tempcode('SYNDICATE_TO_STOPFORUMSPAM'), do_lang_tempcode('DESCRIPTION_SYNDICATE_TO_STOPFORUMSPAM'), 'stopforumspam', false));
                }
            }
      
    }
}