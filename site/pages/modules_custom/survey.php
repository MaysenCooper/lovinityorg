<?php

class Module_survey
{
    public function info()
    {
        $info                           = array();
        $info['author']                 = 'Patrick Schmalstig';
        $info['organisation']           = 'The Lovinity Community+';
        $info['hacked_by']              = NULL;
        $info['hack_version']           = NULL;
        $info['version']                = 1;
        $info['update_require_upgrade'] = 0;
        $info['locked']                 = false;
        return $info;
    }
    
    public function install($upgrade_from = NULL, $upgrade_from_hack = NULL)
    {
    }
    
    public function uninstall()
    {
    }
    
    public function run()
    {
        i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);
        $link     = "https://survey.lovinity.org/index.php";
        $surveyid = get_param_string('type', 'none');
        $language = get_param_string('lang', 'en');
        $token    = get_param_string('id', null);
        $userid   = get_member();
	$thefirst = true;
        
        if ($surveyid != 'none') {
            $link .= "?r=survey/index&sid=" . $surveyid;
            $thefirst = false;
        } //$surveyid != 'none'
        if ($thefirst) {
            $link .= "?";
        } //$thefirst
        else {
            $link .= "&";
            $thefirst = false;
        }
        $link .= "lang=" . $language;
        if ($token != null && $token != '') {
            if ($thefirst) {
                $link .= "?";
            } //$thefirst
            else {
                $link .= "&";
                $thefirst = false;
            }
            $link .= "token=" . $token;
        } //$token != null && $token != ''
        
        return do_template('LIMESURVEY', array(
            'SURVEYLINK' => strval($link)
        ));
    }
}