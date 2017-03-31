{+START,IF,{$NOR,{$GET,login_screen},{$MATCH_KEY_MATCH,_WILD:login}}}
	<a class="button_screen_item menu__site_meta__user_actions__login" onclick="return open_link_as_overlay(this);" rel="nofollow" href="{FULL_LOGIN_URL*}" title="{!MORE}: {!LOGIN_JOIN}">{!LOGIN_JOIN}</a>
{+END}
