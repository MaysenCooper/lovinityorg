<a class="user_link{+START,IF,{$IN_STR,{CAPTION},<img}} link_exempt{+END}" href="{$STRIP_TAGS,{URL*}}"{+START,IF,{$NOT,{$INLINE_STATS}}} onclick="return ga_track(this);"{+END} rel="{REL*}{+START,IF,{$EQ,{TARGET},_blank}} external{+END}" target="{TARGET*}"{+START,IF_NON_EMPTY,{TITLE}} title="{TITLE*}"{+END}>{$TRIM,{CAPTION}}</a>