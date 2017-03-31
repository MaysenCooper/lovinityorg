{$SET,ident,{$RAND}}	
	<p>The following is a list of charms this member has earned. Click a charm for more information. To see a list of charms a user can earn, <a href="https://www.lovinity.org/catalogues/index/charms.htm">click here</a></p>
{+START,IF_NON_EMPTY,{PRIZES_FORWARD}}
<div class="flex-container" style="align-items: baseline;">
		{+START,LOOP,PRIZES_FORWARD}
<div class="flex-item-max" style="max-width: 200px; min-width: 168px;">
<a href="{URL*}">{PRIZEIMAGE}</a>
<p><div style="text-align: center;"><span style="font-size:1.3em;">
<span class="name">{$TRUNCATE_LEFT,{PRIZENAME},25,1,1}</span></span>
</div></p>
</div>
		{+END}
</div>
{+END}

{+START,IF_EMPTY,{PRIZES_FORWARD}}
	<p class="nothing_here">
		{!NO_ENTRIES}
	</p>
{+END}