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

require_code('resource_fs');

/**
 * Hook class.
 */
class Hook_commandr_fs_catalogues extends Resource_fs_base
{
    public $folder_resource_type = array('catalogue', 'catalogue_category');
    public $file_resource_type = 'catalogue_entry';

    /**
     * Standard Commandr-fs function for seeing how many resources are. Useful for determining whether to do a full rebuild.
     *
     * @param  ID_TEXT $resource_type The resource type
     * @return integer How many resources there are
     */
    public function get_resources_count($resource_type)
    {
        switch ($resource_type) {
            case 'catalogue_entry':
                return $GLOBALS['SITE_DB']->query_select_value('catalogue_entries', 'COUNT(*)');

            case 'catalogue_category':
                return $GLOBALS['SITE_DB']->query_select_value('catalogue_categories', 'COUNT(*)');

            case 'catalogue':
                return $GLOBALS['SITE_DB']->query_select_value('catalogues', 'COUNT(*)');
        }
        return 0;
    }

    /**
     * Standard Commandr-fs function for searching for a resource by label.
     *
     * @param  ID_TEXT $resource_type The resource type
     * @param  LONG_TEXT $label The resource label
     * @return array A list of resource IDs
     */
    public function find_resource_by_label($resource_type, $label)
    {
        switch ($resource_type) {
            case 'catalogue_entry':
                $fields = $GLOBALS['SITE_DB']->query_select('catalogue_fields', array('MIN(cf_order)', 'id', 'cf_type'), null, 'GROUP BY c_name ORDER BY id');
                $ret = array();
                require_code('fields');
                foreach ($fields as $field_bits) {
                    $ob = get_fields_hook($field_bits['cf_type']);
                    list(, , $storage_type) = $ob->get_field_value_row_bits(array('id' => null, 'cf_type' => $field_bits['cf_type'], 'cf_default' => ''));
                    if (strpos($storage_type, '_trans') !== false) {
                        $table = 'catalogue_entries a JOIN ' . get_table_prefix() . 'catalogue_efv_' . $storage_type . ' b ON a.id=b.ce_id AND b.cf_id=' . strval($field_bits['id']);
                        $_ret = $GLOBALS['SITE_DB']->query_select($table, array('a.id'), array($GLOBALS['SITE_DB']->translate_field_ref('cv_value') => $label), '', 1000/*reasonable search limit*/, null, false, array('cv_value' => 'SHORT_TRANS'));
                    } else {
                        $table = 'catalogue_entries a JOIN ' . get_table_prefix() . 'catalogue_efv_' . $storage_type . ' b ON a.id=b.ce_id AND b.cf_id=' . strval($field_bits['id']);
                        $_ret = $GLOBALS['SITE_DB']->query_select($table, array('a.id'), array('b.cv_value' => $label), '', 1000/*reasonable search limit*/);
                    }
                    foreach ($_ret as $r) {
                        $ret[] = strval($r['id']);
                    }
                }
                return $ret;

            case 'catalogue_category':
                $_ret = $GLOBALS['SITE_DB']->query_select('catalogue_categories', array('id'), array($GLOBALS['SITE_DB']->translate_field_ref('cc_title') => $label), 'ORDER BY id');
                $ret = array();
                foreach ($_ret as $r) {
                    $ret[] = strval($r['id']);
                }
                return $ret;

            case 'catalogue':
                $ret = $GLOBALS['SITE_DB']->query('SELECT c_name FROM ' . get_table_prefix() . 'catalogues WHERE ' . db_string_equal_to($GLOBALS['SITE_DB']->translate_field_ref('c_title'), $label) . ' OR ' . db_string_equal_to('c_name', $label), null, null, false, false, array('c_title' => 'SHORT_TRANS'));
                return collapse_1d_complexity('c_name', $ret);
        }
        return array();
    }

    /**
     * Find whether a kind of resource handled by this hook (folder or file) can be under a particular kind of folder.
     *
     * @param  ?ID_TEXT $above Folder resource type (null: root)
     * @param  ID_TEXT $under Resource type (may be file or folder)
     * @return ?array A map: The parent referencing field, the table it is in, and the ID field of that table (null: cannot be under)
     */
    protected function _has_parent_child_relationship($above, $under)
    {
        if (is_null($above)) {
            $above = '';
        }
        switch ($above) {
            case '':
                if ($under == 'catalogue') {
                    $folder_info = $this->_get_cma_info($under);
                    return array(
                        'cat_field' => null,
                        'linker_table' => null,
                        'id_field' => 'c_name',
                        'id_field_linker' => null,
                        'cat_field_numeric' => false,
                    );
                }
                break;
            case 'catalogue':
                if ($under == 'catalogue_category') {
                    $folder_info = $this->_get_cma_info($under);
                    return array(
                        'cat_field' => 'c_name',
                        'linker_table' => 'catalogue_categories',
                        'id_field' => 'id',
                        'id_field_linker' => 'id',
                        'cat_field_numeric' => false,
                    );
                }
                break;
            case 'catalogue_category':
                if (($under == 'catalogue_category') || ($under == 'catalogue_entry')) {
                    $sub_info = $this->_get_cma_info($under);
                    $folder_info = $this->_get_cma_info($above);
                    return array(
                        'cat_field' => $sub_info['parent_category_field'],
                        'linker_table' => ($under == 'catalogue_entry') ? null : $sub_info['parent_spec__table_name'],
                        'id_field' => $sub_info['parent_spec__field_name'],
                        'id_field_linker' => ($under == 'catalogue_entry') ? null : $sub_info['parent_spec__field_name'],
                        'cat_field_numeric' => $folder_info['id_field_numeric'],
                    );
                }
                break;
        }
        return null;
    }

    /**
     * Standard Commandr-fs date fetch function for resource-fs hooks. Defined when getting an edit date is not easy.
     *
     * @param  array $row Resource row (not full, but does contain the ID)
     * @return ?TIME The edit date or add date, whichever is higher (null: could not find one)
     */
    protected function _get_folder_edit_date($row)
    {
        $query = 'SELECT MAX(date_and_time) FROM ' . get_table_prefix() . 'actionlogs WHERE ' . db_string_equal_to('param_a', $row['c_name']) . ' AND  (' . db_string_equal_to('the_type', 'ADD_CATALOGUE') . ' OR ' . db_string_equal_to('the_type', 'EDIT_CATALOGUE') . ')';
        return $GLOBALS['SITE_DB']->query_value_if_there($query);
    }

    /**
     * Get the filename for a resource ID. Note that filenames are unique across all folders in a filesystem.
     *
     * @param  ID_TEXT $resource_type The resource type
     * @param  ID_TEXT $resource_id The resource ID
     * @return ?ID_TEXT The filename (null: could not find)
     */
    public function folder_convert_id_to_filename($resource_type, $resource_id)
    {
        if ($resource_type == 'catalogue') {
            $f = parent::folder_convert_id_to_filename('catalogue', $resource_id);
            if (is_null($f)) {
                return null;
            }
            return 'CATALOGUE-' . $f;
        }

        return parent::folder_convert_id_to_filename('catalogue_category', $resource_id);
    }

    /**
     * Get the resource ID for a filename. Note that filenames are unique across all folders in a filesystem.
     *
     * @param  ID_TEXT $filename The filename, or filepath
     * @param  ?ID_TEXT $resource_type The resource type (null: assumption of only one folder resource type for this hook; only passed as non-null from overridden functions within hooks that are calling this as a helper function)
     * @return array A pair: The resource type, the resource ID
     */
    public function folder_convert_filename_to_id($filename, $resource_type = null)
    {
        $filename = preg_replace('#^.*/#', '', $filename); // Paths not needed, as filenames are globally unique; paths would not be in alternative_ids table

        if (substr($filename, 0, 10) == 'CATALOGUE-') { // Must be defined first, to ensure prefix stripped
            return parent::folder_convert_filename_to_id(substr($filename, 10), 'catalogue');
        }

        if (!is_null($resource_type)) {
            return parent::folder_convert_filename_to_id($filename, $resource_type);
        }

        return parent::folder_convert_filename_to_id($filename, 'catalogue_category');
    }

    /**
     * Convert properties to variables for adding/editing catalogues.
     *
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return array Properties
     */
    protected function __folder_read_in_properties_catalogue($path, $properties)
    {
        $description = $this->_default_property_str($properties, 'description');
        $display_type = $this->_default_property_int($properties, 'display_type');
        $is_tree = $this->_default_property_int($properties, 'is_tree');
        $notes = $this->_default_property_str($properties, 'notes');
        $submit_points = $this->_default_property_int($properties, 'submit_points');
        $ecommerce = $this->_default_property_int($properties, 'ecommerce');
        $send_view_reports = $this->_default_property_int($properties, 'send_view_reports');
        $default_review_freq = $this->_default_property_int_null($properties, 'default_review_freq');
        $add_time = $this->_default_property_time($properties, 'add_date');

        return array($description, $display_type, $is_tree, $notes, $submit_points, $ecommerce, $send_view_reports, $default_review_freq, $add_time);
    }

    /**
     * Convert properties to variables for adding/editing catalogue categories.
     *
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @param  boolean $edit Is an edit
     * @return ~array Properties (false: error)
     */
    protected function __folder_read_in_properties_category($path, $properties, $edit)
    {
        if (strpos($path, '/') === false) {
            list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path, 'catalogue');

            $parent_id = mixed();
            $catalogue_name = $category;
        } else {
            list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path, 'catalogue_category');

            $parent_id = $this->_integer_category($category);
            $catalogue_name = $GLOBALS['SITE_DB']->query_select_value('catalogue_categories', 'c_name', array('id' => $parent_id));
            $is_tree = $GLOBALS['SITE_DB']->query_select_value('catalogues', 'c_is_tree', array('c_name' => $catalogue_name));
            if ($is_tree == 0) {
                return false;
            }
        }

        $description = $this->_default_property_str($properties, 'description');
        $notes = $this->_default_property_str($properties, 'notes');
        $rep_image = $this->_default_property_urlpath($properties, 'rep_image', $edit);
        $move_days_lower = $this->_default_property_int($properties, 'move_days_lower');
        $move_days_higher = $this->_default_property_int($properties, 'move_days_higher');
        $move_target = $this->_default_property_resource_id_null('catalogue_category', $properties, 'move_target');
        $order = $this->_default_property_int($properties, 'order');
        $add_date = $this->_default_property_time($properties, 'add_date');
        $meta_keywords = $this->_default_property_str($properties, 'meta_keywords');
        $meta_description = $this->_default_property_str($properties, 'meta_description');

        return array($catalogue_name, $description, $notes, $parent_id, $rep_image, $move_days_lower, $move_days_higher, $move_target, $order, $add_date, $meta_keywords, $meta_description);
    }

    /**
     * Standard Commandr-fs add function for resource-fs hooks. Adds some resource with the given label and properties.
     *
     * @param  LONG_TEXT $filename Filename OR Resource label
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return ~ID_TEXT The resource ID (false: error)
     */
    public function folder_add($filename, $path, $properties)
    {
        require_code('catalogues2');

        if ($path != '') { // Category
            list($properties, $label) = $this->_folder_magic_filter($filename, $path, $properties, 'catalogue_category');

            $_properties = $this->__folder_read_in_properties_category($path, $properties, false);
            if ($_properties === false) {
                return false;
            }
            list($catalogue_name, $description, $notes, $parent_id, $rep_image, $move_days_lower, $move_days_higher, $move_target, $order, $add_date, $meta_keywords, $meta_description) = $_properties;

            $id = actual_add_catalogue_category($catalogue_name, $label, $description, $notes, $parent_id, $rep_image, $move_days_lower, $move_days_higher, $move_target, $order, $add_date, null, $meta_keywords, $meta_description);

            $this->_resource_save_extend('catalogue_category', strval($id), $filename, $label, $properties);

            return strval($id);
        } else { // Catalogue
            list($properties, $label) = $this->_folder_magic_filter($filename, $path, $properties, 'catalogue');

            list($description, $display_type, $is_tree, $notes, $submit_points, $ecommerce, $send_view_reports, $default_review_freq, $add_time) = $this->__folder_read_in_properties_catalogue($path, $properties);

            $name = $this->_create_name_from_label($label);

            $name = actual_add_catalogue($name, $label, $description, $display_type, $is_tree, $notes, $submit_points, $ecommerce, $send_view_reports, $default_review_freq, $add_time, true);

            if ((array_key_exists('fields', $properties)) && ($properties['fields'] != '')) {
                $fields_data = $properties['fields'];
                foreach ($fields_data as $field_data) {
                    $type = $field_data['type'];
                    $order = $field_data['order'];
                    $defines_order = $field_data['defines_order'];
                    $visible = $field_data['visible'];
                    $searchable = $field_data['searchable'];
                    $default = $field_data['default'];
                    $required = $field_data['required'];
                    $put_in_category = $field_data['put_in_category'];
                    $put_in_search = $field_data['put_in_search'];
                    $options = $field_data['options'];

                    $_field_title = $field_data['field_title'];
                    $_description = $field_data['description'];
                    $field_title = mixed();
                    foreach ($_field_title as $lang => $val) {
                        $field_title = insert_lang('cf_name', $val, 2, null, false, $field_title, $lang);
                    }
                    $description = mixed();
                    foreach ($_description as $lang => $val) {
                        $description = insert_lang('cf_description', $val, 2, null, false, $description, $lang);
                    }

                    actual_add_catalogue_field($name, $field_title, $description, $type, $order, $defines_order, $visible, $searchable, $default, $required, $put_in_category, $put_in_search, $options);
                }
            } else {
                actual_add_catalogue_field($name, do_lang('TITLE'), '', 'short_text', 0, 1, 1, 1, '', 1, 1, 1);
            }

            $this->_resource_save_extend('catalogue', $name, $filename, $label, $properties);

            return $name;
        }
    }

    /**
     * Standard Commandr-fs load function for resource-fs hooks. Finds the properties for some resource.
     *
     * @param  SHORT_TEXT $filename Filename
     * @param  string $path The path (blank: root / not applicable). It may be a wildcarded path, as the path is used for content-type identification only. Filenames are globally unique across a hook; you can calculate the path using ->search.
     * @return ~array Details of the resource (false: error)
     */
    public function folder_load($filename, $path)
    {
        list($resource_type, $resource_id) = $this->folder_convert_filename_to_id($filename);

        if ($path != '') { // Category
            list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path, 'catalogue_category');

            $rows = $GLOBALS['SITE_DB']->query_select('catalogue_categories', array('*'), array('id' => intval($resource_id)), '', 1);
            if (!array_key_exists(0, $rows)) {
                return false;
            }
            $row = $rows[0];

            list($meta_keywords, $meta_description) = seo_meta_get_for('catalogue_category', strval($row['id']));

            $properties = array(
                'label' => get_translated_text($row['cc_title']),
                'description' => get_translated_text($row['cc_description']),
                'notes' => $row['cc_notes'],
                'rep_image' => remap_urlpath_as_portable($row['rep_image']),
                'move_days_lower' => $row['cc_move_days_lower'],
                'move_days_higher' => $row['cc_move_days_higher'],
                'move_target' => remap_resource_id_as_portable('catalogue_category', $row['cc_move_target']),
                'order' => $row['cc_order'],
                'meta_keywords' => $meta_keywords,
                'meta_description' => $meta_description,
                'add_date' => remap_time_as_portable($row['cc_add_date']),
            );
            $this->_resource_load_extend('catalogue_category', $resource_id, $properties, $filename, $path);
            return $properties;
        }

        // Catalogue
        list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path, 'catalogue');

        $rows = $GLOBALS['SITE_DB']->query_select('catalogues', array('*'), array('c_name' => $resource_id), '', 1);
        if (!array_key_exists(0, $rows)) {
            return false;
        }
        $row = $rows[0];

        $fields = array();
        $_fields = $GLOBALS['SITE_DB']->query_select('catalogue_fields', array('*'), array('c_name' => $resource_id), 'ORDER BY cf_order,' . $GLOBALS['FORUM_DB']->translate_field_ref('cf_name'));
        foreach ($_fields as $_field) {
            $fields[] = array(
                'field_title' => get_translated_text($_field['cf_name']),
                'description' => get_translated_text($_field['cf_description']),
                'type' => $_field['cf_type'],
                'order' => $_field['cf_order'],
                'defines_order' => $_field['cf_defines_order'],
                'visible' => $_field['cf_visible'],
                'searchable' => $_field['cf_searchable'],
                'default' => $_field['cf_default'],
                'required' => $_field['cf_required'],
                'put_in_category' => $_field['cf_put_in_category'],
                'put_in_search' => $_field['cf_put_in_search'],
                'options' => $_field['cf_options'],
            );
        }

        $properties = array(
            'label' => get_translated_text($row['c_title']),
            'description' => get_translated_text($row['c_description']),
            'display_type' => $row['c_display_type'],
            'is_tree' => $row['c_is_tree'],
            'notes' => $row['c_notes'],
            'submit_points' => $row['c_submit_points'],
            'ecommerce' => $row['c_ecommerce'],
            'send_view_reports' => $row['c_send_view_reports'],
            'default_review_freq' => $row['c_default_review_freq'],
            'fields' => $fields,
            'add_date' => remap_time_as_portable($row['c_add_date']),
        );
        $this->_resource_load_extend('catalogue', $resource_id, $properties, $filename, $path);
        return $properties;
    }

    /**
     * Standard Commandr-fs edit function for resource-fs hooks. Edits the resource to the given properties.
     *
     * @param  ID_TEXT $filename The filename
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return ~ID_TEXT The resource ID (false: error, could not create via these properties / here)
     */
    public function folder_edit($filename, $path, $properties)
    {
        list($resource_type, $resource_id) = $this->folder_convert_filename_to_id($filename);

        require_code('catalogues2');

        list($properties, $label) = $this->_folder_magic_filter($filename, $path, $properties, $resource_type);

        if ($resource_type == 'catalogue') {
            $label = $this->_default_property_str($properties, 'label');
            list($description, $display_type, $is_tree, $notes, $submit_points, $ecommerce, $send_view_reports, $default_review_freq, $add_time) = $this->__folder_read_in_properties_catalogue($path, $properties);

            $name = $this->_create_name_from_label($label);

            $name = actual_edit_catalogue($resource_id, $name, $label, $description, $display_type, $notes, $submit_points, $ecommerce, $send_view_reports, $default_review_freq, $add_time, true);

            // How to handle the fields
            if ((array_key_exists('fields', $properties)) && ($properties['fields'] != '')) {
                $_fields = $GLOBALS['SITE_DB']->query_select('catalogue_fields', array('id', 'cf_name', 'cf_description'), array('c_name' => $name), 'ORDER BY cf_order,' . $GLOBALS['SITE_DB']->translate_field_ref('cf_name'));

                $fields_data = $properties['fields'];
                foreach ($fields_data as $i => $field_data) {
                    $type = $field_data['type'];
                    $order = $field_data['order'];
                    $defines_order = $field_data['defines_order'];
                    $visible = $field_data['visible'];
                    $searchable = $field_data['searchable'];
                    $default = $field_data['default'];
                    $required = $field_data['required'];
                    $put_in_category = $field_data['put_in_category'];
                    $put_in_search = $field_data['put_in_search'];
                    $options = $field_data['options'];

                    $field_title = $field_data['field_title'];
                    $description = $field_data['description'];

                    if (array_key_exists($i, $_fields)) {
                        $id = $_fields[$i]['id'];

                        actual_edit_catalogue_field($id, $name, $field_title, $description, $order, $defines_order, $visible, $searchable, $default, $required, $put_in_category, $put_in_search, $options, $type);
                    } else {
                        actual_add_catalogue_field($name, $field_title, $description, $type, $order, $defines_order, $visible, $searchable, $default, $required, $put_in_category, $put_in_search, $options);
                    }
                }
            }

            $this->_resource_save_extend('catalogue', $name, $filename, $label, $properties);
        } else {
            $label = $this->_default_property_str($properties, 'label');

            $_properties = $this->__folder_read_in_properties_category($path, $properties, true);
            if ($_properties === false) {
                return false;
            }
            list($catalogue_name, $description, $notes, $parent_id, $rep_image, $move_days_lower, $move_days_higher, $move_target, $order, $add_date, $meta_keywords, $meta_description) = $_properties;

            actual_edit_catalogue_category(intval($resource_id), $label, $description, $notes, $parent_id, $meta_keywords, $meta_description, $rep_image, $move_days_lower, $move_days_higher, $move_target, $order, $add_date, $catalogue_name);

            $this->_resource_save_extend('catalogue_category', $resource_id, $filename, $label, $properties);
        }

        return $resource_id;
    }

    /**
     * Standard Commandr-fs delete function for resource-fs hooks. Deletes the resource.
     *
     * @param  ID_TEXT $filename The filename
     * @param  string $path The path (blank: root / not applicable)
     * @return boolean Success status
     */
    public function folder_delete($filename, $path)
    {
        list($resource_type, $resource_id) = $this->folder_convert_filename_to_id($filename);

        require_code('catalogues2');

        if ($resource_type == 'catalogue') {
            actual_delete_catalogue($resource_id);
        } else {
            actual_delete_catalogue_category(intval($resource_id));
        }

        return true;
    }

    /**
     * Standard Commandr-fs date fetch function for resource-fs hooks. Defined when getting an edit date is not easy.
     *
     * @param  array $row Resource row (not full, but does contain the ID)
     * @return ?TIME The edit date or add date, whichever is higher (null: could not find one)
     */
    protected function _get_file_edit_date($row)
    {
        $query = 'SELECT MAX(date_and_time) FROM ' . get_table_prefix() . 'actionlogs WHERE ' . db_string_equal_to('param_a', strval($row['id'])) . ' AND  (' . db_string_equal_to('the_type', 'ADD_CATALOGUE_CATEGORY') . ' OR ' . db_string_equal_to('the_type', 'EDIT_CATALOGUE_CATEGORY') . ')';
        return $GLOBALS['SITE_DB']->query_value_if_there($query);
    }

    /**
     * Find the best unique key index for the catalogue.
     *
     * @param  array $fields The catalogue fields
     * @return integer The key index
     */
    protected function _find_unique_key_num($fields)
    {
        foreach ($fields as $i => $f) {
            if ($f['cf_type'] == 'codename') {
                return $i;
            }
        }
        return 0;
    }

    /**
     * Convert properties to variables for adding/editing catalogue entries.
     *
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @param  SHORT_TEXT $category Category
     * @param  SHORT_TEXT $label Label
     * @return array Properties
     */
    protected function __file_read_in_properties($path, $properties, $category, $label)
    {
        $category_id = $this->_integer_category($category);

        $catalogue_name = $GLOBALS['SITE_DB']->query_select_value('catalogue_categories', 'c_name', array('id' => $category_id));
        $_fields = $GLOBALS['SITE_DB']->query_select('catalogue_fields', array('id', 'cf_type', 'cf_default', 'cf_name'), array('c_name' => $catalogue_name), 'ORDER BY cf_order,' . $GLOBALS['SITE_DB']->translate_field_ref('cf_name'));
        $unique_key_num = $this->_find_unique_key_num($_fields);
        $map = array();
        $props_already = array();
        foreach ($_fields as $i => $field_bits) {
            $field_id = $field_bits['id'];

            if ($i == $unique_key_num) {
                $map[$field_id] = $label;
            } else {
                $cf_name = get_translated_text($field_bits['cf_name']);
                $fixed_id = fix_id($cf_name);
                if (!array_key_exists($fixed_id, $props_already)) {
                    $key = $fixed_id;
                } else {
                    $key = 'field_' . strval($field_bits['id']);
                }
                $props_already[$key] = true;

                $value = $this->_default_property_str($properties, $key);
                if (is_null($value)) {
                    $value = $field_bits['cf_default'];
                }
                $map[$field_id] = $value;
            }
        }

        $validated = $this->_default_property_int_null($properties, 'validated');
        if (is_null($validated)) {
            $validated = 1;
        }
        $notes = $this->_default_property_str($properties, 'notes');
        $allow_rating = $this->_default_property_int_modeavg($properties, 'allow_rating', 'catalogue_entries', 1);
        $allow_comments = $this->_default_property_int_modeavg($properties, 'allow_comments', 'catalogue_entries', 1);
        $allow_trackbacks = $this->_default_property_int_modeavg($properties, 'allow_trackbacks', 'catalogue_entries', 1);
        $time = $this->_default_property_time($properties, 'add_date');
        $submitter = $this->_default_property_member($properties, 'submitter');
        $views = $this->_default_property_int($properties, 'views');
        $meta_keywords = $this->_default_property_str($properties, 'meta_keywords');
        $meta_description = $this->_default_property_str($properties, 'meta_description');

        return array($category_id, $validated, $notes, $allow_rating, $allow_comments, $allow_trackbacks, $map, $time, $submitter, $views, $meta_keywords, $meta_description);
    }

    /**
     * Standard Commandr-fs add function for resource-fs hooks. Adds some resource with the given label and properties.
     *
     * @param  LONG_TEXT $filename Filename OR Resource label
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return ~ID_TEXT The resource ID (false: error, could not create via these properties / here)
     */
    public function file_add($filename, $path, $properties)
    {
        list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path, 'catalogue_category');
        list($properties, $label) = $this->_file_magic_filter($filename, $path, $properties, $this->file_resource_type);

        if ($category == '') {
            return false;
        }
        if ($category_resource_type == 'catalogue') {
            return false;
        }

        require_code('catalogues2');

        list($category_id, $validated, $notes, $allow_rating, $allow_comments, $allow_trackbacks, $map, $time, $submitter, $views, $meta_keywords, $meta_description) = $this->__file_read_in_properties($path, $properties, $category, $label);

        $edit_date = $this->_default_property_time_null($properties, 'edit_date');

        $id = actual_add_catalogue_entry($category_id, $validated, $notes, $allow_rating, $allow_comments, $allow_trackbacks, $map, $time, $submitter, $edit_date, $views, null, $meta_keywords, $meta_description);

        $this->_resource_save_extend($this->file_resource_type, strval($id), $filename, $label, $properties);

        return strval($id);
    }

    /**
     * Standard Commandr-fs load function for resource-fs hooks. Finds the properties for some resource.
     *
     * @param  SHORT_TEXT $filename Filename
     * @param  string $path The path (blank: root / not applicable). It may be a wildcarded path, as the path is used for content-type identification only. Filenames are globally unique across a hook; you can calculate the path using ->search.
     * @return ~array Details of the resource (false: error)
     */
    public function file_load($filename, $path)
    {
        list($resource_type, $resource_id) = $this->file_convert_filename_to_id($filename);

        $rows = $GLOBALS['SITE_DB']->query_select('catalogue_entries', array('*'), array('id' => intval($resource_id)), '', 1);
        if (!array_key_exists(0, $rows)) {
            return false;
        }
        $row = $rows[0];

        list($meta_keywords, $meta_description) = seo_meta_get_for('catalogue_entry', strval($row['id']));

        $properties = array(
            'validated' => $row['ce_validated'],
            'notes' => $row['notes'],
            'allow_rating' => $row['allow_rating'],
            'allow_comments' => $row['allow_comments'],
            'allow_trackbacks' => $row['allow_trackbacks'],
            'views' => $row['ce_views'],
            'meta_keywords' => $meta_keywords,
            'meta_description' => $meta_description,
            'submitter' => remap_resource_id_as_portable('member', $row['ce_submitter']),
            'add_date' => remap_time_as_portable($row['ce_add_date']),
            'edit_date' => remap_time_as_portable($row['ce_edit_date']),
        );
        $this->_resource_load_extend($resource_type, $resource_id, $properties, $filename, $path);

        require_code('catalogues');
        $fields = get_catalogue_entry_field_values($row['c_name'], intval($resource_id));

        require_code('fields');
        $unique_key_num = $this->_find_unique_key_num($fields);
        foreach ($fields as $field_num => $field) {
            $ob = get_fields_hook($field['cf_type']);
            $val = $field['cf_default'];
            if (array_key_exists('effective_value_pure', $field)) {
                $val = $field['effective_value_pure'];
            } elseif (array_key_exists('effective_value', $field)) {
                $val = $field['effective_value'];
            }

            if ($field_num == $unique_key_num) {
                $properties['label'] = $val;
            } else {
                $cf_name = get_translated_text($field['cf_name']);
                $fixed_id = fix_id($cf_name);
                if (!array_key_exists($fixed_id, $properties)) {
                    $key = $fixed_id;
                } else {
                    $key = 'field_' . strval($field['id']);
                }

                $properties[$key] = $val;
            }
        }

        return $properties;
    }

    /**
     * Standard Commandr-fs edit function for resource-fs hooks. Edits the resource to the given properties.
     *
     * @param  ID_TEXT $filename The filename
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return ~ID_TEXT The resource ID (false: error, could not create via these properties / here)
     */
    public function file_edit($filename, $path, $properties)
    {
        list($resource_type, $resource_id) = $this->file_convert_filename_to_id($filename);
        list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path, 'catalogue_category');
        list($properties,) = $this->_file_magic_filter($filename, $path, $properties, $this->file_resource_type);

        if ($category == '') {
            return false;
        }
        if ($category_resource_type == 'catalogue') {
            return false;
        }

        require_code('catalogues2');

        $label = $this->_default_property_str($properties, 'label');
        list($category_id, $validated, $notes, $allow_rating, $allow_comments, $allow_trackbacks, $map, $time, $submitter, $views, $meta_keywords, $meta_description) = $this->__file_read_in_properties($path, $properties, $category, $label);

        $edit_date = $this->_default_property_time($properties, 'edit_date');

        actual_edit_catalogue_entry(intval($resource_id), $category_id, $validated, $notes, $allow_rating, $allow_comments, $allow_trackbacks, $map, $meta_keywords, $meta_description, $edit_date, $time, $views, $submitter, true);

        $this->_resource_save_extend($this->file_resource_type, $resource_id, $filename, $label, $properties);

        return $resource_id;
    }

    /**
     * Standard Commandr-fs delete function for resource-fs hooks. Deletes the resource.
     *
     * @param  ID_TEXT $filename The filename
     * @param  string $path The path (blank: root / not applicable)
     * @return boolean Success status
     */
    public function file_delete($filename, $path)
    {
        list($resource_type, $resource_id) = $this->file_convert_filename_to_id($filename);

        require_code('catalogues2');
        actual_delete_catalogue_entry(intval($resource_id));

        return true;
    }
}