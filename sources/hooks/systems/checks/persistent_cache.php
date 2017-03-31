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
 * @package    core
 */

/**
 * Hook class.
 */
class Hook_check_persistent_cache
{
    /**
     * Check various input var restrictions.
     *
     * @return array List of warnings
     */
    public function run()
    {
        $warning = array();

        if (running_script('install')) {
            if (
                (!class_exists('Memcached')) &&
                (!class_exists('Memcache')) &&
                (!function_exists('apc_fetch')) &&
                (!function_exists('eaccelerator_put')) &&
                (!function_exists('xcache_get')) &&
                (!function_exists('wincache_ucache_get'))
            ) {
                //$warning[] = do_lang_tempcode('NO_PERSISTENT_CACHE_SUPPORT'); Actually you can use the filesystem cache
            }
        }

        return $warning;
    }
}