<a href="{PROFILE_URL*}" onblur="this.onmouseout(event);" onfocus="this.onmouseover(event);" onmouseover="if (typeof window.activate_tooltip!='undefined') activate_tooltip(this,event,'{POSTER_DETAILS;~*}','auto','{$IMG;*,icons/24x24/menu/social/members}',null,null,true{+START,IF,{$NOT,{ONLINE}}},true{+END});" class="fancy_user_link">{+START,IF_PASSED_AND_TRUE,HIGHLIGHT_NAME}<em>{+END}{$DISPLAYED_USERNAME*,{POSTER_USERNAME}}{+START,IF_PASSED_AND_TRUE,HIGHLIGHT_NAME}</em>{+END}</a>
