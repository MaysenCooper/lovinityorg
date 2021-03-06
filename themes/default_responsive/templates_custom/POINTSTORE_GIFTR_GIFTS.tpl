{TITLE}

{+START,INCLUDE,HANDLE_CONFLICT_RESOLUTION}{+END}
{+START,IF_PASSED,WARNING_DETAILS}
	{WARNING_DETAILS}
{+END}

{+START,IF_NON_EMPTY,{GIFTS}}
	{$, Old table style
		<div class="wide_table_wrap">
			<table class="wide_table results_table autosized_table responsive_table">
				<thead>
					<tr>
						<th></th>
						<th>{!GIFT}</th>
						<th width="33%">{!PRICE}</th>
						<th width="33%">{!ACTIONS}</th>
					</tr>
				</thead>

				<tbody>
					{+START,LOOP,GIFTS}
						<tr class="{$CYCLE,results_table_zebra,zebra_0,zebra_1}">
							<td>
								<img src="{$THUMBNAIL*,{IMAGE_URL},80}" />
							</td>
							<td>
								{NAME*}
							</td>
							<td width="33%" style="text-align: center">
								{!_GIFT_PRICE,{PRICE*}}
							</td>
							<td width="33%" style="text-align: center">
								<a title="{NAME*}" href="{GIFT_URL*}">{!GIFT_PURCHASE}</a>
							</td>
						</tr>
					{+END}
				</tbody>
			</table>
		</div>
	}

	<p>{!CHOOSE_YOUR_GIFT}</p>

	<div class="box box___pointstore_giftr_gifts"><div class="box_inner">
		{+START,LOOP,GIFTS}
			<div style="float: left; margin: 15px" onmouseover="if (typeof window.activate_tooltip!='undefined') activate_tooltip(this,event,'{NAME;^*}.&lt;br /&gt;&lt;br /&gt;{!GIFT_POPULARITY;^*,{POPULARITY}}','auto');">
				<a href="{GIFT_URL*}"><img title="{NAME*}" src="{$THUMBNAIL*,{IMAGE_URL},80x80,,,,pad,both,#FFFFFF00}" /></a>

				<p class="associated_links_block_group associated_link"><a title="{NAME*}" href="{GIFT_URL*}">{!_GIFT_PRICE,{PRICE*}}</a></p>
			</div>
		{+END}
	</div></div>

	<div class="float_surrounder">
		<form style="float: left; margin-top: 3px" title="{!CATEGORY}" action="{$SELF_URL*,,,,category=<null>,start=0}" method="post" autocomplete="off">
			{$INSERT_SPAMMER_BLACKHOLE}

			<div>
				<label for="category">{!CATEGORY}</label>
				<select id="category" name="category">
					<option value="">{!ALL_EM}</option>
					{+START,LOOP,CATEGORIES}
						<option{+START,IF,{$EQ,{_loop_var},{CATEGORY}}} selected="selected"{+END}>{_loop_var*}</option>
					{+END}
				</select><input onclick="disable_button_just_clicked(this);" class="button_micro buttons__filter" type="submit" value="{!FILTER}" />
			</div>
		</form>

		{+START,IF_NON_EMPTY,{PAGINATION}}
			{PAGINATION}
		{+END}
	</div>
{+END}

{+START,IF_EMPTY,{GIFTS}}
	<p class="no_entries">{!NO_GIFTS_TO_DISPLAY}</p>
{+END}
