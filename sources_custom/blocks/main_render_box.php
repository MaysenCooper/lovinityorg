<?php

class Block_main_render_box
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Patrick Schmalstig';
        $info['organisation'] = 'The Lovinity Community+';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 1;
        $info['locked'] = false;
        $info['parameters'] = array('content_type','content_id');
        return $info;
    }
    

    public function run($map)
    {
        i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);
        require_code('content');
        $ob=get_content_object($map['content_type']); 
list($content_title, $poster_id, $cma_info, $content_row, $content_url) = content_get_details($map['content_type'], $map['content_id']);
        return $ob->run($content_row, get_module_zone($cma_info['module']));
    }
}

?>