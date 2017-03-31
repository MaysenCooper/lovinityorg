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
class Hook_discord_yomomma
{
	public function help()
	{
		return array(
		'!yomomma' => 'Yo momma is so worthless she deserves an insult.'
		);
	}

	public function run($params, $userid, $username = 'guest')
	{
global $FILE_BASE, $RELATIVE_PATH;
$f_contents = file($FILE_BASE . '/data_custom/yomomma.txt');
$line = $f_contents[array_rand($f_contents)];
$fields = array('Yo Momma' => $line);
return array('title' => '!yomomma', 'color' => '16744703', 'fields' => $fields);

	}
	
}