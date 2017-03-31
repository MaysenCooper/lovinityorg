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
class Hook_content_meta_aware_member
{
    /**
     * Get content type details. Provides information to allow task reporting, randomisation, and add-screen linking, to function.
     *
     * @param  ?ID_TEXT $zone The zone to link through to (null: autodetect).
     * @return ?array Map of award content-type info (null: disabled).
     */
    public function info($zone = null)
    {
        if (get_forum_type() != 'cns' || !isset($GLOBALS['FORUM_DB'])) {
            return null;
        }

        return array(
            'support_custom_fields' => false,

            'content_type_label' => 'global:MEMBER',
            'content_type_universal_label' => 'Profile',

            'connection' => $GLOBALS['FORUM_DB'],
            'table' => 'f_members',
            'id_field' => 'id',
            'id_field_numeric' => true,
            'parent_category_field' => null,
            'parent_category_field__resource_fs' => 'm_primary_group',
            'parent_category_meta_aware_type' => 'group',
            'is_category' => false,
            'is_entry' => true,
            'category_type' => null, // For category permissions
            'parent_spec__table_name' => null,
            'parent_spec__parent_name' => null,
            'parent_spec__field_name' => null,
            'category_field' => 'm_primary_group', // For category permissions
            'category_is_string' => false,

            'title_field' => 'm_username',
            'title_field_dereference' => false,
            'description_field' => 'm_title',
            'thumb_field' => addon_installed('cns_member_avatars') ? 'm_avatar_url' : null,
            'thumb_field_is_theme_image' => false,
            'alternate_icon_theme_image' => null,

            'view_page_link_pattern' => '_SEARCH:members:view:_WILD',
            'edit_page_link_pattern' => '_SEARCH:members:view:_WILD',
            'edit_page_link_pattern_post' => '_SEARCH:members:view:_WILD:only_tab=edit:only_subtab=settings',
            'edit_page_link_field' => 'edit_username',
            'view_category_page_link_pattern' => null,
            'add_url' => has_actual_page_access(get_member(), 'admin_cns_members') ? '_SEARCH:admin_cns_members:step1' : null,
            'archive_url' => ((!is_null($zone)) ? $zone : get_module_zone('members')) . ':members',

            'support_url_monikers' => (get_option('username_profile_links') == '0'),

            'views_field' => null,
            'order_field' => null,
            'submitter_field' => 'id',
            'author_field' => null,
            'add_time_field' => 'm_join_time',
            'edit_time_field' => null,
            'date_field' => 'm_join_time',
            'validated_field' => 'm_validated',

            'seo_type_code' => null,

            'feedback_type_code' => null,

            'permissions_type_code' => null, // null if has no permissions

            'search_hook' => 'cns_members',
            'rss_hook' => 'cns_members',
            'attachment_hook' => null,
            'unvalidated_hook' => 'cns_members',
            'notification_hook' => 'cns_new_member',
            'sitemap_hook' => 'member',

            'addon_name' => 'core_cns',

            'cms_page' => 'admin_cns_members',
            'module' => 'members',

            'commandr_filesystem_hook' => 'groups',
            'commandr_filesystem__is_folder' => false,

            'support_revisions' => false,

            'support_privacy' => false,

            'support_content_reviews' => true,

            'actionlog_regexp' => '\w+_MEMBER',

            'filtercode' => 'cns_members2::_members_filtercode',
            'filtercode_protected_fields' => array('m_pass_hash_salted', 'm_pass_salt', 'm_password_change_code'), // These are ones even some staff should never know
        );
    }

    /**
     * Run function for content hooks. Renders a content box for an award/randomisation.
     *
     * @param  array $row The database row for the content
     * @param  ID_TEXT $zone The zone to display in
     * @param  boolean $give_context Whether to include context (i.e. say WHAT this is, not just show the actual content)
     * @param  boolean $include_breadcrumbs Whether to include breadcrumbs (if there are any)
     * @param  ?ID_TEXT $root Virtual root to use (null: none)
     * @param  boolean $attach_to_url_filter Whether to copy through any filter parameters in the URL, under the basis that they are associated with what this box is browsing
     * @param  ID_TEXT $guid Overridden GUID to send to templates (blank: none)
     * @return Tempcode Results
     */
    public function run($row, $zone, $give_context = true, $include_breadcrumbs = true, $root = null, $attach_to_url_filter = false, $guid = '')
    {
        require_code('cns_members');
        require_code('cns_members2');

        $GLOBALS['CNS_DRIVER']->MEMBER_ROWS_CACHED[$row['id']] = $row;

        return render_member_box($row['id'], false, null, null, true, null, $give_context, $guid);
    }
}