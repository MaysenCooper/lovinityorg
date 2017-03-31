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
class Hook_discord_doge
{
	public function help()
	{
		return array(
		'!doge' => 'Such meme. Much doge. Wow.'
		);
	}

	public function run($params, $userid, $username = 'guest')
	{
global $FILE_BASE, $RELATIVE_PATH;
$f_contents = file($FILE_BASE . '/data_custom/doge1.txt');
$doge1 = $f_contents[array_rand($f_contents)];
$doge2 = $f_contents[array_rand($f_contents)];
$doge3 = $f_contents[array_rand($f_contents)];
$f_contents = file($FILE_BASE . '/data_custom/doge2.txt');
$doge1b = $f_contents[array_rand($f_contents)];
$doge2b = $f_contents[array_rand($f_contents)];
$doge3b = $f_contents[array_rand($f_contents)];
$string = preg_replace("/[\n\r]/","","$doge1 $doge1b. $doge2 $doge2b. $doge3 $doge3b. Wow.");  
$fields = array('Doge' => $string);
return array('title' => '!doge', 'color' => '16744703', 'fields' => $fields);

	}
	
}