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
 * @package    core_cns
 */

/**
 * Hook class.
 */
class Hook_profiles_tabs_edit_delete
{
    /**
     * Find whether this hook is active.
     *
     * @param  MEMBER $member_id_of The ID of the member who is being viewed
     * @param  MEMBER $member_id_viewing The ID of the member who is doing the viewing
     * @return boolean Whether this hook is active
     */
    public function is_active($member_id_of, $member_id_viewing)
    {
        //return has_privilege($member_id_viewing, 'delete_account') && (($member_id_of == $member_id_viewing) || (has_privilege($member_id_viewing, 'assume_any_member')));
        return true;
    }

    /**
     * Render function for profile tabs edit hooks.
     *
     * @param  MEMBER $member_id_of The ID of the member who is being viewed
     * @param  MEMBER $member_id_viewing The ID of the member who is doing the viewing
     * @param  boolean $leave_to_ajax_if_possible Whether to leave the tab contents null, if tis hook supports it, so that AJAX can load it later
     * @return ?array A tuple: The tab title, the tab body text (may be blank), the tab fields, extra JavaScript (may be blank) the suggested tab order, hidden fields (optional) (null: if $leave_to_ajax_if_possible was set), the icon
     */
    public function render_tab($member_id_of, $member_id_viewing, $leave_to_ajax_if_possible = false)
    {
        $title = do_lang_tempcode('DELETE');

        $order = 200;

        // Actualiser
        if (has_privilege($member_id_viewing, 'delete_account') && (($member_id_of == $member_id_viewing) || (has_privilege($member_id_viewing, 'assume_any_member'))))
        {
        $delete_account = post_param_integer('delete', 0);
        if ($delete_account == 1) {
            if (is_guest($member_id_of)) {
                warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
            }

            require_code('cns_members_action');
            require_code('cns_members_action2');

            cns_delete_member($member_id_of);

            inform_exit(do_lang_tempcode('SUCCESS'));
        }
        }

        if ($leave_to_ajax_if_possible) {
            return null;
        }

        // UI fields
        $username = $GLOBALS['FORUM_DRIVER']->get_username($member_id_of);
if (has_privilege($member_id_viewing, 'delete_account') && (($member_id_of == $member_id_viewing) || (has_privilege($member_id_viewing, 'assume_any_member'))))
{
        $text = paragraph(do_lang_tempcode('_DELETE_MEMBER' . (($member_id_of == $member_id_viewing) ? '_SUICIDAL' : ''), escape_html($username)));

        if ($member_id_of != $member_id_viewing) {
            $merge_url = build_url(array('page' => 'admin_cns_merge_members', 'from' => $username, 'to' => $GLOBALS['FORUM_DRIVER']->get_username(get_member())), get_module_zone('admin_cns_merge_members'));
            $text->attach(paragraph(do_lang_tempcode('_DELETE_MEMBER_MERGE', escape_html($merge_url->evaluate()))));

            if (has_privilege($member_id_of, 'comcode_dangerous')) {
                $text->attach(paragraph(do_lang_tempcode('_DELETE_MEMBER_ADMIN', escape_html($merge_url->evaluate()))));
            }

            if (addon_installed('search')) {
                $search_url = build_url(array('page' => 'search', 'type' => 'results', 'author' => $username), get_module_zone('search'));
                $text->attach(paragraph(do_lang_tempcode('_DELETE_MEMBER_SEARCH', escape_html($search_url->evaluate()))));
            }
        }

        $fields = new Tempcode();
        require_code('form_templates');
        $fields->attach(form_input_tick(do_lang_tempcode(($member_id_of != $member_id_viewing) ? 'DELETE_WITHOUT_MERGING' : 'DELETE'), do_lang_tempcode('DESCRIPTION_DELETE'), 'delete', false));

        require_code('tempcode_compiler');
        $javascript = static_evaluate_tempcode(template_to_tempcode("
			window.load_tab__edit__{\$LCASE,{!DELETE_MEMBER|*}}=function() {
				var submit_button=document.getElementById('submit_button');
				var delete_checkbox=document.getElementById('delete');
				var tab=document.getElementById('t_edit__{\$LCASE,{!DELETE_MEMBER|*}}');

				submit_button.disabled=!delete_checkbox.checked;

				window.setInterval(function() {
					submit_button.disabled=!delete_checkbox.checked && tab.className.indexOf('tab_active')!=-1;
				},100);
			}
		"));
		} else {
		$fields = new Tempcode();
		$javascript = "";
		$text = paragraph("To request for account deletion, please create a support ticket under Help -> Tickets / Contact. In order to have an account deleted, you must have 0 demerits and have never been suspended nor banned. Otherwise, your account will exist but will be made inactive and the content wiped. If you insist on an account deletion under this case, we can delete your account, but we would also have to permanently ban you in order to prevent you from creating new accounts with clean slates in terms of moderation.");
		
		}

        return array($title, $fields, $text, $javascript, $order, null, 'tabs/member_account/edit/delete');
    }
}