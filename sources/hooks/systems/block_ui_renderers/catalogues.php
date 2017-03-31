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
 * @package    catalogues
 */

/**
 * Hook class.
 */
class Hook_block_ui_renderers_catalogues
{
    /**
     * See if a particular block parameter's UI input can be rendered by this.
     *
     * @param  ID_TEXT $block The block
     * @param  ID_TEXT $parameter The parameter of the block
     * @param  boolean $has_default Whether there is a default value for the field, due to this being an edit
     * @param  string $default Default value for field
     * @param  Tempcode $description Field description
     * @return ?Tempcode Rendered field (null: not handled).
     */
    public function render_block_ui($block, $parameter, $has_default, $default, $description)
    {
        if (($parameter == 'param') && (in_array($block, array('main_contact_catalogues')))) {
            require_code('catalogues');
            $structured_list = create_selection_list_catalogues($default);

            return form_input_list(titleify($parameter), escape_html($description), $parameter, $structured_list, null, false, false);
        }

        if ((($default == '') || (is_numeric($default))) && ($parameter == 'param') && (in_array($block, array('main_cc_embed')))) {
            $num_categories = $GLOBALS['SITE_DB']->query_select_value('catalogue_categories', 'COUNT(*)');
            $num_categories_top = $GLOBALS['SITE_DB']->query_select_value('catalogue_categories', 'COUNT(*)', array('cc_parent_id' => null));
            if (($num_categories_top < 300) && ((!$has_default) || ($num_categories < 300))) { // catalogue category
                $list = new Tempcode();
                $structured_list = new Tempcode();
                $categories = $GLOBALS['SITE_DB']->query_select('catalogue_categories', array('id', 'cc_title', 'c_name'), ($num_categories >= 300) ? array('cc_parent_id' => null) : null, 'ORDER BY cc_order,' . $GLOBALS['SITE_DB']->translate_field_ref('cc_title'));
                $last_cat = mixed();
                foreach ($categories as $cat) {
                    if (substr($cat['c_name'], 0, 1) == '_') {
                        continue;
                    }

                    if ((is_null($last_cat)) || ($cat['c_name'] != $last_cat)) {
                        if (!$list->is_empty()) {
                            $structured_list->attach(form_input_list_group($last_cat, $list));
                        }
                        $list = new Tempcode();
                        $last_cat = $cat['c_name'];
                    }
                    $list->attach(form_input_list_entry(strval($cat['id']), $has_default && strval($cat['id']) == $default, get_translated_text($cat['cc_title'])));
                }
                if (!$list->is_empty()) {
                    $structured_list->attach(form_input_list_group($last_cat, $list));
                }
                return form_input_list(titleify($parameter), escape_html($description), $parameter, $structured_list, null, false, false);
            }
        }

        return null;
    }
}