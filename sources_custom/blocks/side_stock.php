<?php
class Block_side_stock
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
        return $info;
    }
    

    public function run()
    {

        i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);
        $day1 = (60 * 60 * 24);
        $day7 = (60 * 60 * 24 * 7);
        $day30 = (60 * 60 * 24 * 30);
        
	$yangworth = get_value('yang_worth');
        $currentstock = $GLOBALS['FORUM_DB']->query_select_value_if_there('stock_index', 'stock_index', array(), ' ORDER BY `timestamp` DESC');
	$ymin = $GLOBALS['FORUM_DB']->query_select_value_if_there('stock_index', 'MIN(stock_index)', array(), ' WHERE `timestamp` >= ' . (time() - $day7));
	$_trenddata = $GLOBALS['FORUM_DB']->query_select('stock_index', array('*'), null, ' WHERE `timestamp` >= ' . (time() - $day7) . ' ORDER BY `timestamp` ASC');
	$trenddata = "";
	foreach ($_trenddata as $data)
	{
		$trenddata .= $data['timestamp'] . ',' . $data['stock_index'] . "\n";
	}
        //$day1stock = $GLOBALS['FORUM_DB']->query_value_if_there('SELECT avg(stock_index) FROM `43SO_stock_index` WHERE `timestamp` >= ' . (time() - $day1));
        //$day7stock = $GLOBALS['FORUM_DB']->query_value_if_there('SELECT avg(stock_index) FROM `43SO_stock_index` WHERE `timestamp` >= ' . (time() - $day7));
        //$day30stock = $GLOBALS['FORUM_DB']->query_value_if_there('SELECT avg(stock_index) FROM `43SO_stock_index` WHERE `timestamp` >= ' . (time() - $day30));
        $currentstock = float_format($currentstock);
        //$day1trend = float_format((($currentstock/$day1stock)*100)-100);
        //$day7trend = float_format((($currentstock/$day7stock)*100)-100);
        //$day30trend = float_format((($currentstock/$day30stock)*100)-100);

	/*$day1percent = float_format(sqrt(abs($day1trend))/10);
	if ($day1percent > 1)
		$day1percent = 1;
        $day7percent = float_format(sqrt(abs($day7trend))/10);
	if ($day7percent > 1)
		$day7percent = 1;
	$day30percent = float_format(sqrt(abs($day30trend))/10);
	if ($day30percent > 1)
		$day30percent = 1;
	*/

        return do_template('BLOCK_SIDE_STOCK', array(
            'STOCK_INDEX' => '<span style="color: MediumBlue"><b>' . $currentstock . '</b></span>',
	    /*
            'TREND_1' => ($day1trend > 0) ? '<span style="color: green"><b>' . $day1trend . '%</b></span>' : '<span style="color: red"><b>' . $day1trend . '%</b></span>',
            'TREND_7' => ($day7trend > 0) ? '<span style="color: green"><b>' . $day7trend . '%</b></span>' : '<span style="color: red"><b>' . $day7trend . '%</b></span>',
            'TREND_30' => ($day30trend > 0) ? '<span style="color: green"><b>' . $day30trend . '%</b></span>' : '<span style="color: red"><b>' . $day30trend . '%</b></span>',
	    'TREND_1_COLOR' => ($day1trend > 0) ? 'rgba(0,128,0,1)' : 'rgba(255,0,0,1)',
	    'TREND_7_COLOR' => ($day7trend > 0) ? 'rgba(0,128,0,1)' : 'rgba(255,0,0,1)',
	    'TREND_30_COLOR' => ($day30trend > 0) ? 'rgba(0,128,0,1)' : 'rgba(255,0,0,1)',
	    'TREND_1_VALUE' => strval($day1percent),
	    'TREND_7_VALUE' => strval($day7percent),
	    'TREND_30_VALUE' => strval($day30percent),
	    */
	    'TREND_DATA' => $trenddata,
	    'YMIN' => strval(integer_format($ymin)),
	    'YANG_WORTH' => '<span style="color: DarkGreen"><b>' . $yangworth . '</b></span>',
        ));
        
    }
}
