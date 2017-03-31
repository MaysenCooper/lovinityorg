{$REQUIRE_JAVASCRIPT,dyn_comcode}
{$REQUIRE_JAVASCRIPT,jquery}
{$REQUIRE_JAVASCRIPT,timecircles}

{$SET,countdown_id,countdown_{$RAND}}

<div id="{$GET,countdown_id}" data-date="{TARGET}"></div>

<script>
window.onload = function(){
$("#{$GET,countdown_id}").TimeCircles({
"count_past_zero": true,
    "animation": "smooth",
    "bg_width": 0.2,
    "fg_width": 0.03666666666666667,
    "circle_bg_color": "#90989F",
    "time": {
        "Days": {
            "text": "Days",
            "color": "#40484F",
            "show": true
        },
        "Hours": {
            "text": "Hours",
            "color": "#40484F",
            "show": true
        },
        "Minutes": {
            "text": "Minutes",
            "color": "#40484F",
            "show": true
        },
        "Seconds": {
            "text": "Seconds",
            "color": "#40484F",
            "show": true
        }
    }
});
};
</script>