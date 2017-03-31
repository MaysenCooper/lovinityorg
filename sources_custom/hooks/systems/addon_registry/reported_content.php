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
 * Hook class.
 */
class Hook_addon_registry_reported_content
{
    /**
     * Get a list of file permissions to set
     *
     * @param  boolean $runtime Whether to include wildcards represented runtime-created chmoddable files
     * @return array File permissions to set
     */
    public function get_chmod_array($runtime = false)
    {
        return array();
    }

    /**
     * Get the version of Composr this addon is for
     *
     * @return float Version number
     */
    public function get_version()
    {
        return cms_version_number();
    }

    /**
     * Get the addon category
     *
     * @return string The category
     */
    public function get_category()
    {
        return 'New Features';
    }

    /**
     * Get the addon author
     *
     * @return string The author
     */
    public function get_author()
    {
        return 'Chris Graham / Patrick Schmalstig';
    }

    /**
     * Find other authors
     *
     * @return array A list of co-authors that should be attributed
     */
    public function get_copyright_attribution()
    {
        return array();
    }

    /**
     * Get the addon licence (one-line summary only)
     *
     * @return string The licence
     */
    public function get_licence()
    {
        return 'Licensed on the same terms as Composr';
    }

    /**
     * Get the description of the addon
     *
     * @return string Description of the addon
     */
    public function get_description()
    {
        return 'This addon installs reporting content functionality into your Composr website, which is essential for staff who wish to have their community help guide them to content that may be in violation of your website policies. The functionality integrates with Composr\'s support tickets system, opposed to versions 3 and prior of this addon which utilized reported posts forum. 
        
        The addon achieves its goal by editing a couple templates so that non-obtrusive report links are displayed throughout your website at the bottom. If a user finds, say, a gallery image to be offensive or in violation, they can click the report link, which brings up an overlay window. In the overlay, they can provide information about the content, such as why they feel it should be removed or edited. A logged in user will also be given the option to report anonymously, which causes the support ticket to be created under user ID 1, or Guest. In addition, non-logged-in users may be asked to enter a CAPTCHA to prevent spam, and may optionally provide an email address for staff follow-up.
        
        When a user submits a report, the addon will then first check to see if the same user (based on session ID) already reported that content. If so, it checks to see if there is still an open ticket associated with the report. 
        
        If the session did not already report the content, there are no open tickets associated with any reports the session made on that content, or cns_forums is not installed (which is required by the duplicate report checking), a new support ticket is created. In the support ticket post (by default, unless you edit the lang file), information will contain the content title (as a link to the content), content type (meta_aware), content ID, content creator (member who created it), and a rendering of the content via. content meta aware hooks built into Composr. It will also contain the information supplied by the reporter.
        
        If the session did already report the content and there is still an open ticket associated with it, instead of making another support ticket, the addon will append the additional information the reporter supplied in the form of a reply on the original support ticket.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array();
    }

    /**
     * Get a mapping of dependency types
     *
     * @return array File permissions to set
     */
    public function get_dependencies()
    {
        return array(
            'requires' => array('tickets'),
            'recommends' => array('cns_forum'),
            'conflicts_with' => array('cns_reported_posts')
        );
    }

    /**
     * Explicitly say which icon should be used
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/menu/_generic_admin/component.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'sources_custom/hooks/systems/addon_registry/reported_content.php',
            'sources_custom/cns_topicview.php',
            'lang_custom/EN/report_content.ini',
            'site/pages/modules_custom/report_content.php',
            'themes/default/templates_custom/REPORTED_CONTENT_FCOMCODE.tpl',
            'themes/default/templates_custom/CNS_TOPIC_SCREEN.tpl',
            'themes/default/templates_custom/CNS_USER_MEMBER.tpl',
            'themes/default/templates_custom/STAFF_ACTIONS.tpl',
            'sources_custom/hooks/systems/config/reported_times.php',
        );
    }
}
