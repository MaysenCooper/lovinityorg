{+START,IF_NON_EMPTY,{PRIZES_FORWARD}}
<p>
<div class="flex-container">
{+START,LOOP,PRIZES_FORWARD}
<div class="flex-item-max" style="max-width: 20px; min-width: 20px; padding: 0px; margin-top: 0px;">
	{+START,SET,TOOLTIP}
		<p>{PRIZENAME}</p>
	{+END}
<a onclick="return open_link_as_overlay(this);" onmouseover="if (typeof window.activate_tooltip!='undefined') activate_tooltip(this,event,'{$TRIM*;^,{$GET,TOOLTIP}}','500px');" href="{URL*}">{PRIZEIMAGE}</a>
</div>
{+END}
</div>
</p>
{+END}