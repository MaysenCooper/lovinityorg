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
 * @package    core_form_interfaces
 */

/**
 * Hook class.
 */
class Hook_symbol_CUSTOM_FONTS_CHAT
{
    /**
     * Run function for symbol hooks. Searches for tasks to perform.
     *
     * @param  array $param Symbol parameters
     * @return string Result
     */
    public function run($param)
    {
        
        $out = '';
        /*
        $dir = get_file_base() . '/data/fonts';
        $dh = @scandir($dir);
         if ($dh !== false) {
        foreach ($dh as $file) {
            $basename = basename($file, '.ttf');
            if (($file[0] != '.') && ($file == $basename . '.ttf')) {
               $out .= $this->font_name($basename);
            }
        }
    }
*/
        
        if ((!isset($GLOBALS['DOING_USERS_INIT'])) && (!in_safe_mode())) { // The !isset is because of if the user init causes a DB query to load sessions which loads DB hooks which checks for safe mode which leads to a permissions check for safe mode and thus a failed user check (as sessions not loaded yet)
        $dir = get_file_base() . '/data_custom/fonts';
        $dh = @scandir($dir);
        if ($dh !== false) {
            foreach ($dh as $file) {
                $basename = basename($file, '.ttf');
                if (($file[0] != '.') && ($file == $basename . '.ttf')/* && (preg_match('#^[\w\-]*$#', $basename) != 0) Let's trust - performance*/) {
                    $out .= $this->font_name($basename);
                }
            }
        }
    }
     return $out;   
    }
    
    public function font_name($basename)
    {
        
        $output = preg_replace('#[^\w]#', '-', strtolower($basename)) . ',';
        return $output;
    }
}