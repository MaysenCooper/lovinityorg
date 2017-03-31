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
class Hook_warnings_text_rule
{
    
    public function info()
    {
        return array(
            'active' => true,
            'order' => 2,
            );
    }
    
    /**
     * Run function for displaying UI fields for this punitive action in the warnings form.
     */
    public function UI(&$hidden, &$fields)
    {
        require_code('form_templates');
        $violatingrule='';
            
$fields->attach(form_input_text_comcode(do_lang_tempcode('VIOLATING_RULE'),do_lang_tempcode('DESCRIPTION_VIOLATING_RULE'),'violatingrule',$violatingrule,true));
      
    }
}