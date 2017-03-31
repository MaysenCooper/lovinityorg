<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core
 */

// Find Composr base directory, and chdir into it
global $FILE_BASE, $RELATIVE_PATH;
$FILE_BASE = (strpos(__FILE__, './') === false) ? __FILE__ : realpath(__FILE__);
$FILE_BASE = dirname($FILE_BASE);
if (!is_file($FILE_BASE . '/sources/global.php')) {
    $RELATIVE_PATH = basename($FILE_BASE);
    $FILE_BASE = dirname($FILE_BASE);
} else {
    $RELATIVE_PATH = '';
}
if (!is_file($FILE_BASE . '/sources/global.php')) {
    $FILE_BASE = $_SERVER['SCRIPT_FILENAME']; // this is with symlinks-unresolved (__FILE__ has them resolved); we need as we may want to allow zones to be symlinked into the base directory without getting path-resolved
    $FILE_BASE = dirname($FILE_BASE);
    if (!is_file($FILE_BASE . '/sources/global.php')) {
        $RELATIVE_PATH = basename($FILE_BASE);
        $FILE_BASE = dirname($FILE_BASE);
    } else {
        $RELATIVE_PATH = '';
    }
}
@chdir($FILE_BASE);

global $FORCE_INVISIBLE_GUEST;
$FORCE_INVISIBLE_GUEST = false;
global $EXTERNAL_CALL;
$EXTERNAL_CALL = false;
if (!is_file($FILE_BASE . '/sources/global.php')) {
    exit('ERROR! Could not load core Composr kernel.');
}
require($FILE_BASE . '/sources/global.php');

// Put code that you temporarily want executed into the function. DELETE THE CODE WHEN YOU'RE DONE.
// This is useful when performing quick and dirty upgrades (e.g. adding tables to avoid a reinstall)

require_code('database_action');
require_code('config2');
require_code('menus2');

$token = filter_naughty(post_param_string('token', null), true);
$userid = filter_naughty(post_param_string('userid', null), true);
$username = filter_naughty(post_param_string('username', null), true);
$command = filter_naughty(post_param_string('command', 'AIML'), true);
$paramstring = filter_naughty(post_param_string('paramstring', null), true);
$params = array();
$paramstring = $command . '|' . $paramstring;
$embeds = array();
$embedfields = array();

if ($token != 'b2ad3106f205488ba8e166e3d00818e5')
	exit('Invalid Token.');

	$params = explode('|', $paramstring);
	$hook = $command;
    
if ($hook == 'help')
{
$hooks = find_all_hooks('systems', 'discord');
	$embeds['title'] = 'TLC+ Bot commands help';
	$embeds['color'] = 65535;
foreach (array_keys($hooks) as $hook1) { // We only expect one actually
       require_code('hooks/systems/discord/' . $hook1);
       $ob = object_factory('Hook_discord_' . $hook1);
	$results = $ob->help();
	foreach ($results as $key => $value)
	{
       		$embedfields[] = array('name' => $key, 'value' => $value);
	}
	}
	$embeds['fields'] = $embedfields;
	echo json_encode($embeds);
} else {
    
	if (!is_file($FILE_BASE . '/sources/hooks/systems/discord/' . $hook . '.php') && !is_file($FILE_BASE . '/sources_custom/hooks/systems/discord/' . $hook . '.php'))
		{
		$embeds['title'] = 'Command not found!';
		$embeds['color'] = 16711680;
		echo json_encode($embeds);
		exit();
		}
		require_code('hooks/systems/discord/' . $hook, true);
		$object = object_factory('Hook_discord_' . $hook);
		if ($object == null)
		{
		$embeds['title'] = 'Command not found!';
		$embeds['color'] = 16711680;
		echo json_encode($embeds);
		exit();
		}
		$results = $object->run($params, $userid, $username);
		$fields = $results['fields'];

	foreach ($fields as $key => $value)
		$embedfields[] = array('name' => $key, 'value' => $value);

	$embeds['title'] = $results['title'];
	$embeds['color'] = $results['color'];
	$embeds['fields'] = $embedfields;
	$thumbfields = array('url' => 'https://www.lovinity.org/uploads/filedump/tlcbot/' . $hook . '.png');
	$embeds['thumbnail'] = $thumbfields;
	echo json_encode($embeds);

}

if (!headers_sent()) {
    header('Content-type: application/json; charset=' . get_charset());
    safe_ini_set('ocproducts.xss_detect', '0');
}
