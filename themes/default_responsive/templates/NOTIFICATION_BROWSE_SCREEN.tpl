{TITLE}

<p>{!NOTIFICATION_FOR_DAYS,{$NUMBER_FORMAT,{$CONFIG_OPTION,notification_keep_days}}}</p>

{+START,IF_NON_EMPTY,{NOTIFICATIONS}}
	{NOTIFICATIONS}
{+END}
{+START,IF_EMPTY,{NOTIFICATIONS}}
	<p class="nothing_here">
		{!NO_ENTRIES}
	</p>
{+END}

{PAGINATION}
