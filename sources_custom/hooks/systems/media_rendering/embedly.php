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
 * @package    core_rich_media
 */
/*
Notes...
 - The cache_age property is not supported. It would significantly complicate the API and hurt performance, and we don't know a use case for it. The spec says it is optional to support.
 - Link/semantic-webpage rendering will not use passed description parameter, etc. This is intentional: the normal flow of rendering through a standardised media template is not used.
*/

/**
 * Hook class.
 */
class Hook_media_rendering_embedly extends Media_renderer_with_fallback
{
    /**
     * Get the label for this media rendering type.
     *
     * @return string The label
     */
    public function get_type_label()
    {
        return 'Embedly (can embed virtually any kind of content)';
    }

    /**
     * Find the media types this hook serves.
     *
     * @return integer The media type(s), as a bitmask
     */
    public function get_media_type()
    {
        return MEDIA_TYPE_ALL;
    }

    /**
     * See if we can recognise this mime type.
     *
     * @param  ID_TEXT $mime_type The mime type
     * @param  ?array $meta_details The media signature, so we can go on this on top of the mime-type (null: not known)
     * @return integer Recognition precedence
     */
    public function recognises_mime_type($mime_type, $meta_details = null)
    {
        if ($mime_type == 'text/html' || $mime_type == 'application/xhtml+xml') {
            if ($meta_details !== null) {
                if (($meta_details['t_json_discovery'] != '') || ($meta_details['t_xml_discovery'] != '')) {
                    return MEDIA_RECOG_PRECEDENCE_MEDIUM;
                }
            }
        }
        return MEDIA_RECOG_PRECEDENCE_NONE;
    }

    /**
     * See if we can recognise this URL pattern.
     *
     * @param  URLPATH $url URL to pattern match
     * @return integer Recognition precedence
     */
    public function recognises_url($url)
    {
        if (looks_like_url($url)) {
            return MEDIA_RECOG_PRECEDENCE_HIGH;
        }
        return MEDIA_RECOG_PRECEDENCE_NONE;
    }

    /**
     * Provide code to display what is at the URL, in the most appropriate way.
     *
     * @param  mixed $url URL to render
     * @param  mixed $url_safe URL to render (no sessions etc)
     * @param  array $attributes Attributes (e.g. width, height, length)
     * @param  boolean $as_admin Whether there are admin privileges, to render dangerous media types
     * @param  ?MEMBER $source_member Member to run as (null: current member)
     * @return Tempcode Rendered version
     */
    public function render($url, $url_safe, $attributes, $as_admin = false, $source_member = null)
    {
        if (is_object($url)) {
            $url = $url->evaluate();
        }
		require_code('files2');
		$meta_details = array();
		$meta_details = get_webpage_meta_details($url);

                return do_template('MEDIA_WEBPAGE_EMBEDLY', array(
                    'TITLE' => array_key_exists('t_title', $meta_details) ? $meta_details['t_title'] : '',
					'DESCRIPTION' => array_key_exists('t_description', $meta_details) ? $meta_details['t_description'] : '',
                    'URL' => $url,
                ));
    }
}
