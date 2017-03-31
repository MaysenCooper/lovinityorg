<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    reported_content
 */

/**
 * Module page class.
 */
class Module_report_content
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham; Patrick Schmalstig';
        $info['organisation'] = 'ocProducts; The Lovinity Community+';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 4;
        $info['update_require_upgrade'] = true;
        $info['locked'] = false;
        return $info;
    }

    /**
     * Uninstall the module.
     */
    public function uninstall()
    {
        $GLOBALS['SITE_DB']->drop_table_if_exists('reported_content');
    }

    /**
     * Install the module.
     *
     * @param  ?integer $upgrade_from What version we're upgrading from (null: new install)
     * @param  ?integer $upgrade_from_hack What hack version we're upgrading from (null: new-install/not-upgrading-from-a-hacked-version)
     */
    public function install($upgrade_from = null, $upgrade_from_hack = null)
    {
        if (is_null($upgrade_from)) {
            $GLOBALS['SITE_DB']->create_table('reported_content', array(
                'r_session_id' => '*ID_TEXT',
                'r_content_type' => '*ID_TEXT',
                'r_content_id' => '*ID_TEXT',
                'r_ticket_id' => 'ID_TEXT',
                'r_counts' => 'BINARY', // If the content is marked unvalidated, r_counts is set to 0 for each row for it, so if it's revalidated the counts apply elsewhere
            ));
            $GLOBALS['SITE_DB']->create_index('reported_content', 'reported_already', array('r_content_type', 'r_content_id'));
        }

        if ((!is_null($upgrade_from)) && ($upgrade_from < 3)) {
            $GLOBALS['SITE_DB']->alter_table_field('reported_content', 'r_session_id', 'ID_TEXT');
        }
        
        // in version 4, reported content uses support tickets instead of reported posts forum. Ticket IDs are matched to reports for duplicate ticket prevention. It will also be used in a future UI.
        if ((!is_null($upgrade_from)) && ($upgrade_from < 4)) {
            $GLOBALS['SITE_DB']->add_table_field('reported_content', 'r_ticket_id', 'ID_TEXT');
        }
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run()
    {
        i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

        $type = get_param_string('type', 'browse');

        require_lang('report_content');

        attach_to_screen_header('<meta name="robots" content="noindex" />'); // XHTMLXHTML

        if ($type == 'browse') {
            $this->title = get_screen_title('REPORT_CONTENT');
        }

        if ($type == 'actual') {
            $this->title = get_screen_title('REPORT_CONTENT');
        }

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution.
     */
    public function run()
    {
        require_lang('cns');

        // Decide what we're doing
        $type = get_param_string('type', 'browse');

        if ($type == 'browse') {
            return $this->form();
        }
        if ($type == 'actual') {
            return $this->actualiser();
        }

        return new Tempcode();
    }

    /**
     * Display the UI for reporting content.
     *
     * @return Tempcode The form.
     */
    public function form()
    {
    	// Include code
        require_code('form_templates');
        require_code('cns_forums');
        require_code('tickets');
        require_code('tickets2');
        require_code('content');
        require_lang('tickets');

	// Input parameters safely into variables
        $url = get_param_string('url', false, true);
        $content_type = get_param_string('content_type'); // Equates to a content_meta_aware hook
        $content_id = get_param_string('content_id');

        list($content_title, $poster_id, $cma_info, $content_row) = content_get_details($content_type, $content_id);


        $hidden_fields = build_keep_form_fields('', true);

        $text = paragraph(do_lang_tempcode('DESCRIPTION_REPORT_CONTENT', escape_html($content_title), escape_html(integer_format(intval(get_option('reported_times'))))));

        url_default_parameters__enable();

	// If the reporter is a guest user, ask for, but do not require, an email address for further communication.
        $specialisation = new Tempcode();
        if (is_guest()) {
            $ticket_type_details = get_ticket_type(null);
            $specialisation->attach(form_input_email(do_lang("EMAIL_ADDRESS"), do_lang("DESCRIPTION_REPORT_EMAIL"), "email", null, false, null));
        }
        
        // Include CAPTCHA in form if settings require it.
        if (addon_installed('captcha')) {
            require_code('captcha');
            if (use_captcha()) {
                $specialisation->attach(form_input_captcha());
                $text->attach(paragraph(do_lang_tempcode('FORM_TIME_SECURITY')));
            }
        }
	
	// If points addon is installed, tell the reporter they're missing out on some points by reporting as a guest.
        if (addon_installed('points')) {
            $login_url = build_url(array('page' => 'login', 'type' => 'browse', 'redirect' => get_self_url(true, true)), get_module_zone('login'));
            $_login_url = escape_html($login_url->evaluate());
            if ((is_guest()) && ((get_forum_type() != 'cns') || (has_actual_page_access(get_member(), 'join')))) {
                $text->attach(paragraph(do_lang_tempcode('NOT_LOGGED_IN_NO_CREDIT', $_login_url)));
            }
        }
        
        // If session already reported this content and the associated ticket is still open, tell the user that report will go as another post in same ticket.
        $assoc_ticket = $this->check_duplicate_reports(get_session_id(), $content_type, $content_id);
        if (!is_null($assoc_ticket))
        {
        $text->attach(paragraph(do_lang_tempcode('DUPLICATE_REPORT')));
        } else { // We do not want to allow choosing ticket type if there is already an active report, since we're just adding another post to the original ticket.
        
            // Build a list of, and ask for, ticket type.
            $types = build_types_list(null);
            $list_entries = new Tempcode();
            foreach ($types as $type) {
            $list_entries->attach(form_input_list_entry($type['TICKET_TYPE_ID'], $type['SELECTED'], $type['NAME']));
            }
            $specialisation->attach(form_input_list(do_lang("TICKET_TYPE"), "Choose ticket type", "ticket_type_id", $list_entries));
            
	}
	
	    // If user reporting is not a guest, provide option to report as a guest (anonymous)
            if (!(is_guest()))
            $specialisation->attach(form_input_tick(do_lang("REPORT_ANONYMOUS"), do_lang("DESCRIPTION_REPORT_ANONYMOUS"), "anonymous", false));
            
	// Build the form
        $post_url = build_url(array('page' => '_SELF', 'type' => 'actual'), '_SELF');
        $post = "";
        $posting_form = get_posting_form(do_lang('REPORT_CONTENT'), 'buttons__send', $post, $post_url, $hidden_fields, $specialisation, '', '', null, null, null, null, true, false, true);

        url_default_parameters__disable();

        return do_template('POSTING_SCREEN', array('_GUID' => '92a0a35a7c07edd0d3f8a960710de608', 'TITLE' => $this->title, 'JAVASCRIPT' => function_exists('captcha_ajax_check') ? captcha_ajax_check() : '', 'TEXT' => $text, 'POSTING_FORM' => $posting_form));
    }

     /**
     * Process the reported content form.
     *
     * @return Redirect Success message, if we succeeded of course.
     */
    public function actualiser()
    {
    	// Require codes
        require_code('content');
        require_code('tickets');
        require_code('tickets2');
        require_lang('tickets');
        
        // Test CAPTCHA
        if (addon_installed('captcha')) {
            require_code('captcha');
            enforce_captcha();
        }

	// Safely put passed parameters into variables
        $content_type = post_param_string('content_type'); // Equates to a content_meta_aware hook
        $content_id = post_param_string('content_id');
        $ticket_type_id = post_param_integer('ticket_type_id',null);
        $anonymous = post_param_integer('anonymous',0);
        
        
        // Grab information about the content being reported.
        list($content_title, $poster_id, $cma_info, $content_row, $content_url) = content_get_details($content_type, $content_id);
        if ($content_title == '') {
        $content_title = $content_type . ' #' . $content_id;
        }
        
        $ob=get_content_object($content_type); 
        
        $poster = do_lang('UNKNOWN');
        $member = do_lang('UNKNOWN');
        if ((!is_null($poster_id)) && (!is_guest($poster_id))) {
            $poster = $GLOBALS['FORUM_DRIVER']->get_username($poster_id);
            if (!is_null($poster)) {
                $member = '{{' . $poster . '}}';
            }
        }

	// Change working user depending on whether or not the anonymous reporting option was checked
	if ($anonymous == 1)
	{
	$member_id = 1;
	$email = '';
	} else {
	$member_id = get_member();
        $email=trim(post_param_string('email',''));
        	if ($email == '') {
        		if (!(is_guest()))
        		$email = $GLOBALS['FORUM_DRIVER']->get_member_email_address(get_member());
        	}
        }	
	
	// If there is already an open ticket for this report, let's make a post inside that ticket instead of making a new ticket and report. So, get the ticket ID.
        $assoc_ticket = $this->check_duplicate_reports(get_session_id(), $content_type, $content_id);
        if (!is_null($assoc_ticket))
        {
        $ticket_type_details = get_ticket_type(null);
        $id = $assoc_ticket;
        check_ticket_access($id);
        $post = do_lang("REPORTED_CONTENT_EXTRA",post_param_string('post'));
        } else { //If there is no ticket open for this report, let's make a new one
        
        $ticket_type_details = get_ticket_type(null);
        $id = get_param_string('id', strval($member_id) . '_' . uniqid('', false)/*random new ticket ID*/);
        check_ticket_access($id);
        $post = do_lang("REPORTED_CONTENT",strval($ob->run($content_row, get_module_zone($cma_info['module']))),strval(post_param_string('post')),array(strval($member),strval($content_type),strval($content_id),strval($content_url),strval($content_title)));
        
        // Add to reported_content table
        $GLOBALS['SITE_DB']->query_insert('reported_content', array(
            'r_session_id' => get_session_id(),
            'r_content_type' => $content_type,
            'r_content_id' => $content_id,
            'r_ticket_id' => $id,
            'r_counts' => 1,
        ));
        }
        
        $_ticket_url = build_url(array('page' => 'tickets', 'type' => 'ticket', 'id' => $id, 'redirect' => null), get_module_zone('tickets'), null, false, true, true);
        $ticket_url = $_ticket_url->evaluate();
        
        // Make the ticket
        ticket_add_post($member_id, $id, $ticket_type_id, "Reported Content: " . $content_type . " #" . $content_id, $post, $ticket_url, $staff_only = false, $time_post = null);

	// Auto monitor this ticket for all support operators if auto assign is enabled
       if (has_privilege($member_id, 'support_operator') && get_option('ticket_auto_assign') == '1') {
            require_code('notifications');
            enable_notifications('ticket_assigned_staff', $id);
        }

        // Find true ticket title
        list($__title, $_topic_id) = get_ticket_details($id);

        // Send email, if email address was provided either by guest field or logged in user.        
        if ($email != '')
            send_ticket_email($id, $__title, $post, $ticket_url, $email, $ticket_type_id, null);


        // If hit threshold, unvalidate the content
        $count = $GLOBALS['SITE_DB']->query_select_value('reported_content', 'COUNT(*)', array(
            'r_content_type' => $content_type,
            'r_content_id' => $content_id,
            'r_counts' => 1,
        ));
        if ($count >= intval(get_option('reported_times'))) {
            // Mark as unvalidated
            if ((!is_null($cma_info['validated_field'])) && (strpos($cma_info['table'], '(') === false)) {
                $db = $GLOBALS[(substr($cma_info['table'], 0, 2) == 'f_') ? 'FORUM_DB' : 'SITE_DB'];
                $db->query_update($cma_info['table'], array($cma_info['validated_field'] => 0), get_content_where_for_str_id($content_id, $cma_info));
            }
        }

        // Done
        $_url = post_param_string('url', '', true);
        if ($_url != '') {
            $content_url = make_string_tempcode($_url);
        }
        return redirect_screen($this->title, $content_url, do_lang_tempcode('SUCCESS'));
    }
    
     /**
     * Check if there is a duplicate report in reported_content table. This function also does a little cleaning up if a ticket was closed but the report wasn't yet un-counted.
     *
     * @param  ?String $session_id The session ID of the reporting user.
     * @param  ?String $content_type The type of the content being reported, as defined by content_meta_aware hooks.
     * @param  ?Integer $content_id The ID of the content being reported.  
     * @return String the ID of the support ticket that is already opened for this report (null if no duplicates were found that contain a still-open support ticket).
     */
    
    public function check_duplicate_reports($session_id, $content_type, $content_id)
    {
    
    // The check_duplicate_reports function will not work if cns_forum is not installed. If it isn't installed, bail out of the function immediately and assume no duplicates.
    if (!addon_installed('cns_forum'))
    	return null;
    	
    	// Code requires
        require_code('content');
        require_code('tickets');
        require_code('tickets2');
        
        // Declare initial variable
        $is_open = 0;
        
        // Find any matching support tickets for same content by same session for reports meant to count. There should never be more than one with how this function is set up.
        $ticket_id = $GLOBALS['SITE_DB']->query_select_value_if_there('reported_content', 'r_ticket_id', array(
            'r_session_id' => $session_id,
            'r_content_type' => $content_type,
            'r_content_id' => $content_id,
            'r_counts' => 1,
        ));
        
        // If we DID find a duplicate
        if (!is_null($ticket_id))
        {
        	// get the topic ID for the forum associated with the ticket for the report already filed
        	list(,$topic_id,,) = get_ticket_details($ticket_id, false);
        	
        	// proceed if a topic ID was located. If none was, something went wrong, so we bail out and assume no duplicate reports.
        	if(!is_null($topic_id))
        	{
        		// Get the open status of the forum topic associated with the ticket
        		$is_open = ($GLOBALS['FORUM_DB']->query_select_value_if_there('f_topics', 't_is_open', array('id' => $topic_id)) == 1);
        		// If the topic is still open, thus the ticket is still open, that means this is a duplicate report. Return the ticket ID.
        		if ($is_open == 1) {
        			return $ticket_id;
        		// If this if condition passes, it means there was a duplicate report, but the ticket was closed. Assume resolved, so let's clean up by marking the report as not counting.
        		} else if ($is_open == 0) {
        		        $GLOBALS['SITE_DB']->query_update('reported_content', array('r_counts' => 0), array(
        		        'r_session_id' => $session_id,
                		'r_content_type' => $content_type,
                		'r_content_id' => $content_id,
            			));
           		}
        	}
        }
        // If we got here, it means either there was no duplicate reports found, there were duplicate reports but the tickets were closed, or something went wrong so we assume no duplicates.
        return null;
    
    }
}
