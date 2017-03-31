<?php

$userid = get_member();
$success=true;
$code = $_REQUEST['code'];
$points = 0;
$reason = "";
  if ($code != "")
  {
echo "<p><strong>RESULTS GIVEN BY CODE SUBMISSION SYSTEM:</strong></p>";
if(is_guest($userid)) {
echo "<p>STATUS RECEIVED: User must be logged in to submit codes. Please log in or register an account.</p>";
$success=false;
  }
  
if ($success)
{
echo "<p>ENTERED CODE: " . $code . "</p>";
$prizeid = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_short', 'ce_id', array('cf_id' => '102', 'cv_value' => $code));
if ($prizeid === null || !$prizeid)
{
	echo "<p>STATUS RECEIVED: The code submitted was not found or had expired.</p>";
	$success = false;
}
}

if ($success)
{
$usesleft = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_integer', 'cv_value', array('cf_id' => '105', 'ce_id' => $prizeid));
if ($usesleft === null || !$usesleft || $usesleft === 0)
{
	echo "<p>STATUS RECEIVED: This code could only be redeemed a limited number of times by the community, and that limit has been reached.</p>";
	$success = false;
}
}

if ($success)
{
	$membersused = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_long', 'cv_value', array('cf_id' => '106', 'ce_id' => $prizeid));
	$membersused2 = preg_split("/[\r\n]+/", $membersused);
			foreach ($membersused2 as $member)
			{
				if ($member == $userid)
				{
					$success = false;
					echo "<p>STATUS RECEIVED: The logged-in user has already redeemed this code once and is therefore not allowed to redeem it again.</p>";
				}
			}
}

if ($success)
{
	if ($membersused === null)
	{
		$toupdate = $userid;
	} else {
		$toupdate = $membersused . '\n' . $userid;
	}
	$GLOBALS['FORUM_DB']->query_update('catalogue_efv_integer', array('cv_value' => ($usesleft - 1)), array('cf_id' => '105', 'ce_id' => $prizeid), '', 1);
	$GLOBALS['SITE_DB']->query("UPDATE `43SO_catalogue_efv_long` SET `cv_value` = '" . $toupdate . "' WHERE `cf_id` = 106 AND `ce_id` = " . $prizeid);
	echo "<p>STATUS RECEIVED: Code was submitted!</p><p>";
	echo eval($GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_long', 'cv_value', array('cf_id' => '104', 'ce_id' => $prizeid)));
	echo "</p>";

}	
}

  echo "<p><strong>Submit a code:</strong></p>";
  echo '<p><form>CODE:<br><input type="text" name="code" value=""><br><input type="submit" class="button_page" value="Submit"></form></p>';