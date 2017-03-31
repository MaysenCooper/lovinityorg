<?php

function generateRandomString($length = 15) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function makeToken($surveyid, $anonymous = false)
{
	$userid = get_member();
	if(is_guest($userid))
	{
		return "ERROR: You are not logged in. Therefore, we could not give you a survey token.";
	}
		$isactive = $GLOBALS['SITE_DB']->query_select_value_if_there('ls_surveys', 'active', array('sid' => $surveyid));
		if ($isactive == null || $isactive == '')
			return "ERROR: We could not find a survey with the ID you specified.";
		if ($isactive != 'Y')
			return "ERROR: The survey you provided is not yet active. We cannot generate a token until the survey becomes active.";
			
		$token = $GLOBALS['SITE_DB']->query_select_value_if_there('ls_tokens_' . $surveyid, 'token', array('firstname' => $userid));
		if ($token != null && $token != '')
			return "ERROR: There already exists a token under your username for the specified survey.";
			
		$token = generateRandomString();
			$GLOBALS['SITE_DB']->query_insert('ls_tokens_' . $surveyid, array(
            'firstname' => !$anonymous ? $userid : '1',
            'lastname' => !$anonymous ? $GLOBALS['FORUM_DRIVER']->get_username($userid) : 'Anonymous',
            'email' => !$anonymous ? $GLOBALS['FORUM_DRIVER']->get_member_row_field($userid, 'm_email_address') : 'anonymous@example.com',
            'emailstatus' => 'OK',
            'token' => $token,
            'language' => 'en',
            'sent' => 'N',
            'remindersent' => 'N',
            'remindercount' => 0,
            'completed' => 'N',
            'usesleft' => '1',
        ));
			return 'SUCCESS: Your token for that survey is ' . $token . '. Access the survey with this token by going to <a href="https://lovinity.org/survey/' . $surveyid . '/' . $token . '.htm">This link</a>';
}

function checkSurveyCompletion($surveyid,$userid)
{
$completion = $GLOBALS['SITE_DB']->query_select_value_if_there('ls_tokens_' . $surveyid, 'completed', array('firstname' => $userid));

if ($completion == null || $completion == '')
	return "ERROR: We could not find your username in the tokens table. Either the survey doesn't exist or was deactivated, or you did not yet create a token and participate in the survey.";
	
if ($completion == 'N')
	return "ERROR: According to our records, you did not complete the survey. Therefore, no rewards have been given.";
	
	log_it('COMPLETED_SURVEY', $surveyid);
	return 1;
}
