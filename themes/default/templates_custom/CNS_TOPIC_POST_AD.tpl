{$SET,Bcnsresp,{$BANNER,cnsresp}} {$,This is to avoid evaluating the banner parameter twice}
{+START,IF_NON_EMPTY,{$GET,Bcnsresp}}
<div>
	<div class="cns_forum_box_left">
		<h2 class="accessibility_hidden">
			Advertisement
		</h2>
	</div>

	<div class="cns_forum_box_right cns_post_details" role="note">
		<div class="cns_post_details_date">
			Advertisement
		</div>
	</div>
</div>

<div>
	<div class="cns_topic_post_member_details" role="note">
	</div>

	<div class="cns_topic_post_area cns_post_main_column">
		<div class="float_surrounder">
		{$GET,Bcnsresp}
		</div>
	</div>
</div>

<div>
	<div class="cns_left_post_buttons">
				<div class="cns_post_back_to_top">
					<a href="#" rel="back_to_top"><img title="{!BACK_TO_TOP}" alt="{!BACK_TO_TOP}" src="{$IMG*,icons/24x24/tool_buttons/top}" srcset="{$IMG*,icons/48x48/tool_buttons/top} 2x" /></a>
				</div>
	</div>

	<div class="buttons_group post_buttons cns_post_main_column">
	</div>
</div>

{+END}