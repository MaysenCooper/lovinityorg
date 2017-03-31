<div class="flex-item-max" style="max-width: 200px; min-width: 168px;">
<p><div style="text-align: center;">
{$SET,displayed_thumb,0}

	{+START,SET,TOOLTIP}
		<p>Click to view entry.</p>
	{+END}

	{$SET,displayed_thumb,0}

	{$SET,view_url,{$?,{$AND,{$MOBILE},{$IS_NON_EMPTY,{FIELDS_GRID}}},{$PAGE_LINK,_SELF:catalogues:entry:{ID}},{VIEW_URL}}}

	{+START,IF_PASSED,FIELD_1_THUMB}
		{+START,IF_NON_EMPTY,{FIELD_1_THUMB}}
				{+START,IF_NON_EMPTY,{$GET,view_url}}
					<a onclick="return open_link_as_overlay(this);" onmouseover="if (typeof window.activate_tooltip!='undefined') activate_tooltip(this,event,'{$TRIM*;^,{$GET,TOOLTIP}}','500px');" href="{$GET*,view_url}">{FIELD_1_THUMB}</a>
				{+END}

				{+START,IF_EMPTY,{$GET,view_url}}
					<span onmouseover="if (typeof window.activate_tooltip!='undefined') activate_tooltip(this,event,'{$TRIM*;^,{$GET,TOOLTIP}}','500px');">{FIELD_1_THUMB}</span>
				{+END}

			{$SET,displayed_thumb,1}
		{+END}
{+END}

{+START,IF,{$NOT,{$GET,displayed_thumb}}}
		{+START,IF_NON_EMPTY,{$GET,view_url}}
				<a onclick="return open_link_as_overlay(this);" onmouseover="if (typeof window.activate_tooltip!='undefined') activate_tooltip(this,event,'{$TRIM*;^,{$GET,TOOLTIP}}','500px');" href="{$GET*,view_url}"><img src="{$IMG*,no_image}" width="168px" alt="Image Not Available" /></a>
		{+END}

		{+START,IF_EMPTY,{$GET,view_url}}
		{$GET,TOOLTIP}
		{+END}
{+END}
</div></p>

	
<p><div style="text-align: center;"><span style="font-size:1.3em;">
{+START,IF_EMPTY,{$GET,view_url}}
<span class="name">{$TRUNCATE_LEFT,{FIELD_0},32,1,1}</span>
{+END}
{+START,IF_NON_EMPTY,{$GET,view_url}}
<a onclick="return open_link_as_overlay(this);" onmouseover="if (typeof window.activate_tooltip!='undefined') activate_tooltip(this,event,'{$TRIM*;^,{$GET,TOOLTIP}}','500px');" href="{$GET*,view_url}"><span class="name">{$TRUNCATE_LEFT,{FIELD_0},32,1,1}</span></a>
{+END}
</span>
</div></p>
	

	
	{+START,IF_PASSED,FIELD_2}
		{+START,IF_NON_EMPTY,{FIELD_2}}
		<p><div style="text-align: center;">
				{$TRUNCATE_LEFT,{FIELD_2},192,1,1}
		</div></p>
		
		{+END}
	{+END}

	{+START,IF,{ALLOW_RATING}}
	<p><div style="text-align: center;">
		<div class="ratings">
			{RATING}
		</div>
		</div></p>
	{+END}
</div>
