<div class="calendar_month_entry">
	<a title="{TITLE*}{+START,IF,{$LT,{$LENGTH,{ID}},10}}: #{ID*}{+END}" href="{URL*}"><img src="{$IMG*,{ICON}}" title="{+START,IF_NON_EMPTY,{TIME}}{TIME*} &ndash; {+END}{TITLE*}" alt="{+START,IF_NON_EMPTY,{TIME}}{TIME*} &ndash; {+END}{TITLE*}" /></a>{+START,IF,{RECURRING}} {!REPEAT_SUFFIX}{+END}
</div>
