{$REQUIRE_JAVASCRIPT,RGraph.common.core}
{$REQUIRE_JAVASCRIPT,RGraph.common.dynamic}
{$REQUIRE_JAVASCRIPT,RGraph.common.tooltips}
{$REQUIRE_JAVASCRIPT,RGraph.pie}
{$REQUIRE_JAVASCRIPT,RGraph.gauge}
{$REQUIRE_JAVASCRIPT,RGraph.meter}
<div class="float_surrounder">
	<div class="cns_profile_column">
		{+START,IF_NON_EMPTY,{AVATAR_URL}}
			<div class="cns_member_profile_avatar">
				<img src="{$ENSURE_PROTOCOL_SUITABILITY*,{AVATAR_URL}}" alt="{!AVATAR}" />
			</div>
		{+END}
		<br />
		<div class="flex-container">
<div class="flex-item-max" style="max-width: 150px; min-width: 150px; padding-right: 0px; padding-left: 0px; margin-top: 0px;">
<canvas id="online_meter" width="600" height="300" style="width: 150px; height: 75px;">
    [No canvas support]
</canvas>	
</div>
		<div class="flex-item-max" style="max-width: 75px; min-width: 75px; padding-right: 0px; padding-left: 0px; margin-top: 0px;">
<canvas id="pop_{MEMBER_ID*}" width="250" height="250" style="width: 75px; height: 75px;">
    [No canvas support]
</canvas>
		</div>
{+START,IF_NON_EMPTY,{DEMERIT_INT}}
		<div class="flex-item-max" style="max-width: 75px; min-width: 75px; padding-right: 0px; padding-left: 0px; margin-top: 0px;">
<canvas id="demerit_{MEMBER_ID*}" width="250" height="250" style="width: 75px; height: 75px;">
    [No canvas support]
</canvas>
</div>
{+END}		
		<div class="flex-item-max" style="max-width: 75px; min-width: 75px; padding-right: 0px; padding-left: 0px; margin-top: 0px;">
		<div style="text-align: center;"><canvas id="can_{MEMBER_ID*}" width="75" height="75" /></div>
</div>
</div>	
		
		<script>
		window.onload = function(){
		jQuery('.skillbar').each(function(){
	jQuery(this).find('.skillbar-bar').animate({
		width:jQuery(this).attr('data-percent')
	},2000);
});
};
		</script>
		<script>
		
add_event_listener_abstract(window,'load',function() {
        var pie = new RGraph.Pie({
            id: 'can_{MEMBER_ID*}',
            data: [{$CPF_VALUE,65,{MEMBER_ID}},{$CPF_VALUE,66,{MEMBER_ID}},{$CPF_VALUE,67,{MEMBER_ID}},{$CPF_VALUE,68,{MEMBER_ID}}],
            options: {
                radius: 35,
                title: 'Credibility:\n{$CPF_VALUE,64,{MEMBER_ID}}',
                titleSize: 7,
                titleY: 37,
                colors: ['red','coral','lightgreen','green'],
                tooltips: ['Bad credibility - Staff (-{$CPF_VALUE,65,{MEMBER_ID}})','Bad credibility - Users (-{$CPF_VALUE,66,{MEMBER_ID}})','Good credibility - Users ({$CPF_VALUE,67,{MEMBER_ID}})','Good Credibility - Staff ({$CPF_VALUE,68,{MEMBER_ID}})'],
                textAccessible: true,
                shadow: false,
            }
        }).draw();
        
        var gauge = new RGraph.Gauge({
            id: 'pop_{MEMBER_ID*}',
            min: 0,
            max: 100,
            value: {POP_MULT},
            options: {
            titleBottom: 'ACTIVITY',
            titleBottomSize: '6',
            titleBottomColor: '#000',
            titleBottomPos: 1,
            textAccessible: true,
            textSize: 6,
            labelsOffsetRadius: 4,
            labelsOffsetx: 0,
            labelsOffsety: 0,
            colorsRanges: [[0,5,'rgba(255,0,0,1)'], [5,20,'rgba(255,255,0,1)'], [20,50,'rgba(128,255,128,1'], [50,100,'rgba(0,128,0,1']]
            }
        }).grow({frames: 250});
var textwrap = document.getElementById('pop_{MEMBER_ID*}_rgraph_domtext_wrapper');
textwrap.style.width  = '75px';
textwrap.style.height = '75px';

{+START,IF_NON_EMPTY,{DEMERIT_INT}}
        var gauge2 = new RGraph.Gauge({
            id: 'demerit_{MEMBER_ID*}',
            min: 0,
            max: 100,
            value: {DEMERIT_INT},
            options: {
            titleBottom: 'DEMERITS',
            titleBottomSize: '6',
            titleBottomColor: '#000',
            titleBottomPos: 1,
            textAccessible: true,
            textSize: 6,
            labelsOffsetRadius: 4,
            labelsOffsetx: 0,
            labelsOffsety: 0,
            colorsRanges: [[0,5,'rgba(0,128,0,1)'], [5,25,'rgba(128,255,128,1)'], [25,75,'rgba(255,255,0,1)'], [75,100,'rgba(255,0,0,1']]
            }
        }).grow({frames: 250});
var textwrap = document.getElementById('demerit_{MEMBER_ID*}_rgraph_domtext_wrapper');
textwrap.style.width  = '75px';
textwrap.style.height = '75px';
{+END}

    var meter = new RGraph.Meter({
        id: 'online_meter',
        min: 0,
        max: 30,
        value: {ONLINE_STATE},
        options: {
            anglesStart: RGraph.PI,
            anglesEnd: RGraph.TWOPI,
            linewidthSegments: 15,
            textSize: 28,
            strokestyle: 'white',
            segmentRadiusStart: 205,
            border: 0,
            tickmarksSmallNum: 0,
            tickmarksBigNum: 0,
            adjustable: true,
            labelsSpecific: [['Offline',7],['Idle',20],['Online',28]],
            redEnd: 15,
            yellowEnd: 25,
            textAccessible: false
        }
    }).draw();

});
</script>

		<h2>{!USERGROUPS}</h2>

		<ul class="compact_list">
			<li><span class="role"{+START,IF_PASSED,ON_PROBATION} style="text-decoration: line-through"{+END}>{PRIMARY_GROUP}</span></li>
			{+START,LOOP,SECONDARY_GROUPS}
				<li{+START,IF_PASSED,ON_PROBATION}{+START,IF,{$NEQ,{$CONFIG_OPTION,probation_usergroup},{_loop_key},{_loop_var}}} style="text-decoration: line-through"{+END}{+END}><a href="{$PAGE_LINK*,_SEARCH:groups:view:{_loop_key}}">{_loop_var*}</a></li>
			{+END}
		</ul>

		<div class="cns_account_links">
			{+START,IF,{VIEW_PROFILES}}
				{+START,LOOP,CUSTOM_FIELDS}
					{$SET,is_messenger_field,{$EQ,{NAME},{!cns_special_cpf:DEFAULT_CPF_im_skype_NAME},{!cns_special_cpf:DEFAULT_CPF_im_jabber_NAME},{!cns_special_cpf:DEFAULT_CPF_sn_twitter_NAME},{!cns_special_cpf:DEFAULT_CPF_sn_facebook_NAME},{!cns_special_cpf:DEFAULT_CPF_sn_google_NAME}}}
					{+START,IF,{$GET,is_messenger_field}}
						{+START,SET,messenger_fields}{+START,IF_NON_EMPTY,{RAW_VALUE}}
							{$GET,messenger_fields}
							{+START,IF,{$EQ,{NAME},{!cns_special_cpf:DEFAULT_CPF_im_skype_NAME}}}<li><img alt="" src="{$IMG*,icons/24x24/links/skype}" srcset="{$IMG*,icons/48x48/links/skype} 2x"/> <a title="{!PHONE_THEM_UP} {!LINK_NEW_WINDOW}" href="skype:{RAW_VALUE*}?call">{!PHONE_THEM_UP}</a> (Skype)</li>{+END}
							{+START,IF,{$EQ,{NAME},{!cns_special_cpf:DEFAULT_CPF_im_jabber_NAME}}}<li><img alt="" src="{$IMG*,icons/24x24/links/xmpp}" srcset="{$IMG*,icons/48x48/links/xmpp} 2x"/> <a title="{!MESSAGE_THEM} {!LINK_NEW_WINDOW}" href="xmpp:{RAW_VALUE*}">{!MESSAGE_THEM}</a> (Jabber/XMPP)</li>{+END}
							{+START,IF,{$EQ,{NAME},{!cns_special_cpf:DEFAULT_CPF_sn_twitter_NAME}}}<li><img alt="" src="{$IMG*,icons/24x24/links/twitter}" srcset="{$IMG*,icons/48x48/links/twitter} 2x"/> <a title="{!MESSAGE_THEM} {!LINK_NEW_WINDOW}" href="http://twitter.com/{RAW_VALUE*}" rel="me">@{RAW_VALUE*}</a> (Twitter)</li>{+END}
							{+START,IF,{$EQ,{NAME},{!cns_special_cpf:DEFAULT_CPF_sn_facebook_NAME}}}<li><img alt="" src="{$IMG*,icons/24x24/links/facebook}" srcset="{$IMG*,icons/48x48/links/facebook} 2x"/> <a title="{!MESSAGE_THEM} {!LINK_NEW_WINDOW}" href="{RAW_VALUE*}" rel="me">Facebook</a></li>{+END}
							{+START,IF,{$EQ,{NAME},{!cns_special_cpf:DEFAULT_CPF_sn_google_NAME}}}<li><img alt="" src="{$IMG*,icons/24x24/links/google_plus}" srcset="{$IMG*,icons/48x48/links/google_plus} 2x"/> <a title="{!MESSAGE_THEM} {!LINK_NEW_WINDOW}" href="{RAW_VALUE*}" rel="me">Google+</a></li>{+END}
						{+END}{+END}
					{+END}
				{+END}
			{+END}
			{+START,IF_NON_EMPTY,{ACTIONS_contact}{$GET,messenger_fields}}
				<div>
					<h2>
						<a class="toggleable_tray_button" href="#" onclick="return toggleable_tray(this.parentNode.parentNode);"><img alt="{!CONTRACT}: {!CONTACT}" title="{!CONTRACT}" src="{$IMG*,1x/trays/contract}" srcset="{$IMG*,2x/trays/contract} 2x" /></a>
						<a class="toggleable_tray_button" href="#" onclick="return toggleable_tray(this.parentNode.parentNode);">{!CONTACT}</a>
					</h2>

					<nav class="toggleable_tray" style="display: block">
						<ul class="nl">
							{ACTIONS_contact}
							<li><img alt="" src="{$IMG*,icons/24x24/menu/social/chat/chat}" srcset="{$IMG*,icons/48x48/menu/social/chat/chat} 2x" /> <a href="javascript:;" onClick="jqac.arrowchat.chatWith('{MEMBER_ID}');">Start Instant Message</a></li>
							{$GET,messenger_fields}
						</ul>
					</nav>
				</div>
			{+END}

			{+START,IF_NON_EMPTY,{ACTIONS_content}}
				<div>
					<h2>
						<a class="toggleable_tray_button" href="#" onclick="return toggleable_tray(this.parentNode.parentNode);"><img alt="{!EXPAND}: {!CONTENT}" title="{!EXPAND}" src="{$IMG*,1x/trays/expand}" srcset="{$IMG*,2x/trays/expand} 2x" /></a>
						<a class="toggleable_tray_button" href="#" onclick="return toggleable_tray(this.parentNode.parentNode);">{!CONTENT}</a>
					</h2>

					<nav class="toggleable_tray" style="display: {$JS_ON,none,block}" aria-expanded="false">
						<ul class="nl">
							{ACTIONS_content}
						</ul>
					</nav>
				</div>
			{+END}

			{+START,IF_NON_EMPTY,{ACTIONS_views}{ACTIONS_profile}}
				<div>
					<h2>
						<a class="toggleable_tray_button" href="#" onclick="return toggleable_tray(this.parentNode.parentNode);"><img alt="{!EXPAND}: {!ACCOUNT}" title="{!EXPAND}" src="{$IMG*,1x/trays/expand}" srcset="{$IMG*,2x/trays/expand} 2x" /></a>
						<a class="toggleable_tray_button" href="#" onclick="return toggleable_tray(this.parentNode.parentNode);">{!ACCOUNT}</a>
					</h2>

					<nav class="toggleable_tray" style="display: {$JS_ON,none,block}" aria-expanded="false">
						<ul class="nl">
							{ACTIONS_views}
							{ACTIONS_profile}
						</ul>
					</nav>
				</div>
			{+END}

			{+START,IF_NON_EMPTY,{ACTIONS_audit}}
				<div>
					<h2>
						<a class="toggleable_tray_button" href="#" onclick="return toggleable_tray(this.parentNode.parentNode);"><img alt="{!EXPAND}: {!AUDIT}" title="{!EXPAND}" src="{$IMG*,1x/trays/expand}" srcset="{$IMG*,2x/trays/expand} 2x" /></a>
						<a class="toggleable_tray_button" href="#" onclick="return toggleable_tray(this.parentNode.parentNode);">{!AUDIT}</a>
					</h2>

					<nav class="toggleable_tray" style="display: {$JS_ON,none,block}" aria-expanded="false">
						<ul class="nl">
							{ACTIONS_audit}
						</ul>
					</nav>
				</div>
			{+END}
		</div>

		{$REVIEW_STATUS,member,{MEMBER_ID}}
	</div>

	<div class="cns_profile_main">
		{+START,IF,{$NOT,{VIEW_PROFILES}}}
			<p class="red_alert" role="alert">
				{!ACCESS_DENIED}
			</p>
		{+END}

		{+START,IF,{$OR,{$AND,{VIEW_PROFILES},{$IS_NON_EMPTY,{CUSTOM_FIELDS}}},{$IS_NON_EMPTY,{$TRIM,{SIGNATURE}}}}}
			<h2>{!ABOUT}</h2>

			<div class="wide_table_wrap">
				<table class="map_table wide_table cns_profile_fields cns_profile_about_section">
					{+START,IF,{$NOT,{$MOBILE}}}
						<colgroup>
							<col class="cns_profile_about_field_name_column" />
							<col class="cns_profile_about_field_value_column" />
						</colgroup>
					{+END}

					<tbody>
						{+START,IF,{VIEW_PROFILES}}
							{+START,LOOP,CUSTOM_FIELDS}
								{$SET,is_point_field,{$EQ,{NAME},{!SPECIAL_CPF__cms_points_used},{!SPECIAL_CPF__cms_gift_points_used},{!SPECIAL_CPF__cms_points_gained_chat},{!SPECIAL_CPF__cms_points_gained_given},{!SPECIAL_CPF__cms_points_gained_visiting},{!SPECIAL_CPF__cms_points_gained_rating},{!SPECIAL_CPF__cms_points_gained_voting},{!SPECIAL_CPF__cms_points_gained_wiki}}}
								{$SET,is_messenger_field,{$EQ,{NAME},{!cns_special_cpf:DEFAULT_CPF_im_skype_NAME},{!cns_special_cpf:DEFAULT_CPF_im_jabber_NAME},{!cns_special_cpf:DEFAULT_CPF_sn_twitter_NAME},{!cns_special_cpf:DEFAULT_CPF_sn_facebook_NAME},{!cns_special_cpf:DEFAULT_CPF_sn_google_NAME}}}

								{+START,IF,{$NOR,{$GET,is_point_field},{$GET,is_messenger_field}}}
									<tr id="cpf_{NAME|*}" class="cpf_{FIELD_ID|*}">
										<th class="de_th">
											{NAME*}:
										</th>

										<td>
											<span>
												{+START,IF_EMPTY,{ENCRYPTED_VALUE}}
													{+START,IF_PASSED,EDITABILITY}
														{$SET,edit_type,{EDIT_TYPE}}
														{+START,FRACTIONAL_EDITABLE,{RAW_VALUE},field_{FIELD_ID},_SEARCH:members:view:{MEMBER_ID}:only_tab=edit:only_subtab=settings,{EDITABILITY}}{VALUE}{+END}
													{+END}
													{+START,IF_NON_PASSED,EDITABILITY}
														{VALUE}
													{+END}
												{+END}
												{+START,IF_NON_EMPTY,{ENCRYPTED_VALUE}}
													{+START,IF,{$JS_ON}}{!encryption:DATA_ENCRYPTED} <a href="javascript:decrypt_data('{ENCRYPTED_VALUE;^*}');" title="{!encryption:DECRYPT_DATA}: {$STRIP_TAGS,{!encryption:DESCRIPTION_DECRYPT_DATA}}">{!encryption:DECRYPT_DATA}</a>{+END}
													{+START,IF,{$NOT,{$JS_ON}}}{ENCRYPTED_VALUE*}{+END}
												{+END}
												<!-- {$,Break out of non-terminated comments in CPF} -->
											</span>
										</td>
									</tr>
								{+END}
							{+END}
						{+END}

						{+START,IF,{$IS_NON_EMPTY,{$TRIM,{SIGNATURE}}}}
							<tr>
								<th class="de_th">
									{!SIGNATURE}:
								</th>

								<td>
									{SIGNATURE}
								</td>
							</tr>
						{+END}
					</tbody>
				</table>
			</div>
		{+END}

		{+START,IF,{VIEW_PROFILES}}
			{+START,IF_PASSED,CUSTOM_FIELDS_SECTIONS}
				{+START,LOOP,CUSTOM_FIELDS_SECTIONS}
					<h2>{_loop_key*}</h2>

					<div class="wide_table_wrap">
						<table class="map_table wide_table cns_profile_fields cns_profile_about_section">
							{+START,IF,{$NOT,{$MOBILE}}}
								<colgroup>
									<col class="cns_profile_about_field_name_column" />
									<col class="cns_profile_about_field_value_column" />
								</colgroup>
							{+END}

							<tbody>
								{+START,LOOP,CUSTOM_FIELDS_SECTION}
									<tr id="cpf_{NAME|*}">
										<th class="de_th">
											{NAME*}:
										</th>

										<td>
											<span>
												{+START,IF_EMPTY,{ENCRYPTED_VALUE}}
													{+START,IF,{$EQ,{!ADDRESS}: {NAME},{!cns_special_cpf:SPECIAL_CPF__cms_country}}}
														{$COUNTRY_CODE_TO_NAME,{RAW_VALUE}}
													{+END}

													{+START,IF,{$NEQ,{!ADDRESS}: {NAME},{!cns_special_cpf:SPECIAL_CPF__cms_country}}}
														{+START,IF_PASSED,EDITABILITY}
															{$SET,edit_type,{EDIT_TYPE}}
															{+START,FRACTIONAL_EDITABLE,{RAW_VALUE},field_{FIELD_ID},_SEARCH:members:view:{MEMBER_ID}:only_tab=edit:only_subtab=settings,{EDITABILITY}}{VALUE}{+END}
														{+END}
														{+START,IF_NON_PASSED,EDITABILITY}
															{VALUE}
														{+END}
													{+END}
												{+END}
												{+START,IF_NON_EMPTY,{ENCRYPTED_VALUE}}
													{+START,IF,{$JS_ON}}{!encryption:DATA_ENCRYPTED} <a href="javascript:decrypt_data('{ENCRYPTED_VALUE;^*}');" title="{!encryption:DECRYPT_DATA}: {$STRIP_TAGS,{!encryption:DESCRIPTION_DECRYPT_DATA}}">{!encryption:DECRYPT_DATA}</a>{+END}
													{+START,IF,{$NOT,{$JS_ON}}}{ENCRYPTED_VALUE*}{+END}
												{+END}
												<!-- {$,Break out of non-terminated comments in CPF} -->
											</span>
										</td>
									</tr>
								{+END}
							</tbody>
						</table>
					</div>
				{+END}
			{+END}
		{+END}

		{+START,IF,{VIEW_PROFILES}}
			<h2>{!DETAILS}</h2>

			<meta class="fn given-name" itemprop="name" content="{$DISPLAYED_USERNAME*,{USERNAME}}" />

			<div class="wide_table_wrap">
				<table class="map_table wide_table cns_profile_details cns_profile_about_section">
					{+START,IF,{$NOT,{$MOBILE}}}
						<colgroup>
							<col class="cns_profile_about_field_name_column" />
							<col class="cns_profile_about_field_value_column" />
						</colgroup>
					{+END}

					<tbody>
						{+START,IF_NON_EMPTY,{$CONFIG_OPTION,display_name_generator}}
							<tr>
								<th class="de_th">{!USERNAME}:</th>
								<td>{USERNAME*}</td>
							</tr>
						{+END}

						<tr>
							<th class="de_th">{!ONLINE_NOW}:</th>
							<td>{ONLINE_NOW*} <span class="associated_details">({$DATE_AND_TIME*,1,0,0,{LAST_VISIT_TIME_RAW}})</span></td>
						</tr>

						{+START,IF_NON_EMPTY,{JOIN_DATE}}
							<tr>
								<th class="de_th">{!JOIN_DATE}:</th>
								<td>
									<time datetime="{$FROM_TIMESTAMP*,Y-m-d\TH:i:s\Z,{JOIN_DATE_RAW}}" itemprop="datePublished">{JOIN_DATE*}</time>
								</td>
							</tr>
						{+END}

						{+START,IF_PASSED,ON_PROBATION}
							<tr>
								<th class="de_th">{!ON_PROBATION_UNTIL}:</th>
								<td>{$DATE_AND_TIME*,1,0,0,{ON_PROBATION}}</td>
							</tr>
						{+END}

						<tr>
							<th class="de_th">{!TIME_FOR_THEM}:</th>
							<td>{TIME_FOR_THEM*}</td>
						</tr>

						<tr>
							<th class="de_th">{!TIMEZONE}:</th>
							<td>{USERS_TIMEZONE*}</td>
						</tr>

						{+START,IF_NON_EMPTY,{BANNED}}
							<tr>
								<th class="de_th">{!BANNED}:</th>
								<td>{BANNED*}</td>
							</tr>
						{+END}

						{+START,IF_NON_EMPTY,{DOB}}
							<tr>
								<th class="de_th">{!DATE_OF_BIRTH}:</th>
								<td><span class="bday">{DOB*}</span></td>
							</tr>
						{+END}

						{+START,IF,{$HAS_PRIVILEGE,member_maintenance}}{+START,IF_NON_EMPTY,{EMAIL_ADDRESS}}
							<tr>
								<th class="de_th">{!EMAIL_ADDRESS}:</th>
								<td><a class="email" href="mailto:{EMAIL_ADDRESS*}">{EMAIL_ADDRESS*}</a></td>
							</tr>
						{+END}{+END}

						{+START,LOOP,EXTRA_INFO_DETAILS}
							<tr>
								<th class="de_th">{_loop_key*}:</th>
								<td><span>{_loop_var*}</span></td>
							</tr>
						{+END}
					</tbody>
				</table>
			</div>

			{+START,IF_NON_EMPTY,{PHOTO_URL}}
				<h2>{!PHOTO}</h2>

				<div class="cns_member_profile_photo">
					<a rel="lightbox" href="{PHOTO_URL*}"><img src="{PHOTO_THUMB_URL*}" alt="{!PHOTO}" class="photo" itemprop="primaryImageOfPage" /></a>
				</div>
			{+END}
		{+END}

		{+START,LOOP,EXTRA_SECTIONS}
			{_loop_var}
		{+END}

		{+START,IF,{VIEW_PROFILES}}
		<div class="stats_overwrap">
			<h2>{!TRACKING}</h2>

			<div class="wide_table_wrap">
				<table class="map_table wide_table cns_profile_tracking cns_profile_about_section">
					{+START,IF,{$NOT,{$MOBILE}}}
						<colgroup>
							<col class="cns_profile_about_field_name_column" />
							<col class="cns_profile_about_field_value_column" />
						</colgroup>
					{+END}

					<tbody>
						{+START,IF,{$ADDON_INSTALLED,cns_forum}}
							<tr>
								<th class="de_th">{!COUNT_POSTS}:</th>
								<td>{COUNT_POSTS*}</td>
							</tr>
						{+END}

						{+START,IF_NON_EMPTY,{MOST_ACTIVE_FORUM}}
							<tr>
								<th class="de_th">{!MOST_ACTIVE_FORUM}:</th>
								<td>{MOST_ACTIVE_FORUM*}</td>
							</tr>
						{+END}

						<tr>
							<th class="de_th">{!LAST_SUBMIT_TIME}:</th>
							<td>{!DAYS_AGO,{SUBMIT_DAYS_AGO}}</td>
						</tr>

						{+START,IF,{$ADDON_INSTALLED,securitylogging}}
							{+START,IF_NON_EMPTY,{IP_ADDRESS}}
								<tr>
									<th class="de_th">{!IP_ADDRESS}:</th>
									<td>
										{+START,IF,{$HAS_ACTUAL_PAGE_ACCESS,admin_lookup}}
											<a href="{$PAGE_LINK*,_SEARCH:admin_lookup:param={IP_ADDRESS&}}">{$TRUNCATE_SPREAD,{IP_ADDRESS*},40,1,1}</a>
										{+END}
										{+START,IF,{$NOT,{$HAS_ACTUAL_PAGE_ACCESS,admin_lookup}}}
											{$TRUNCATE_SPREAD,{IP_ADDRESS*},40,1,1}
										{+END}
									</td>
								</tr>
							{+END}
						{+END}

						{+START,IF_PASSED,USER_AGENT}
							<tr>
								<th class="de_th"><abbr title="{!USER_AGENT}">{$PREG_REPLACE*, \([^\(\)]*\),,{!USER_AGENT}}</abbr>:</th>
								<td><abbr title="{USER_AGENT*}">{$PREG_REPLACE*, \([^\(\)]*\),,{$PREG_REPLACE,\.\d+,,{$REPLACE,({OPERATING_SYSTEM}),,{USER_AGENT}}}}</abbr></td>
							</tr>
						{+END}

						{+START,IF_PASSED,OPERATING_SYSTEM}
							<tr>
								<th class="de_th">{!USER_OS}:</th>
								<td>{OPERATING_SYSTEM*}</td>
							</tr>
						{+END}

						{+START,LOOP,EXTRA_TRACKING_DETAILS}
							<tr>
								<th class="de_th">{_loop_key*}:</th>
								<td><span>{_loop_var*}</span></td>
							</tr>
						{+END}
					</tbody>
				</table>
			</div>
		</div>
		{+END}
	</div>
</div>