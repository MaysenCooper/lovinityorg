{$REQUIRE_JAVASCRIPT,RGraph.common.core}
{$REQUIRE_JAVASCRIPT,RGraph.common.dynamic}
{$REQUIRE_JAVASCRIPT,RGraph.common.tooltips}
{$REQUIRE_JAVASCRIPT,RGraph.common.csv}
{$REQUIRE_JAVASCRIPT,RGraph.drawing.background}
{$REQUIRE_JAVASCRIPT,RGraph.line}
<section class="box box___block_side_stats"><div class="box_inner">
<h3>Activity Index</h3>
		<p>{STOCK_INDEX} current index<br />
		${YANG_WORTH} = 1,000 Yang</p>
		<p>7-day activity trend:</p>
		<div style="display: none" id="myData">
		{TREND_DATA}
		</div>
		<canvas id="stock_trend" width="600" height="250" style="width: 175px;">
                [No canvas support]
                </canvas>
		<p><a href="https://lovinity.org/catalogues/entry/website-functionality/what-is-the-activity.htm" target="_blank">What is this?</a></p>
</div></section>
<script>
add_event_listener_abstract(window,'load',function() {

    new RGraph.CSV('id:myData', function (csv)
    {
        var data     = csv.getCol(1)

        new RGraph.Line({
            id: 'stock_trend',
            data: data,
            options: {
                linewidth: 5,
                backgroundGridVlines: true,
                backgroundGridBorder: false,
                backgroundGridAutofitNumhlines: 4,
                textSize: 20,
                noaxes: true,
                tickmarks: true,
                shadow: false,
                ylabelsCount: 4,
                gutterLeft: 50,
                gutterRight: 50,
                gutterTop: 25,
                ymin: {YMIN},
                colors: ['#000078'],
                textAccessible: false,
                labels: [
                    '','','6D',
                    '','','5D',
                    '','','4D',
                    '','','3D',
                    '','','2D',
                    '','','1D',
                    '','','Now'
                ]
            }
        }).trace();
    });

});
</script>