{TITLE}

<h2>{!ERRORS_IN_ERROR_LOG}</h2>

{ERROR}

<h2>{!ERRORS_IN_PERMISSIONS_LOG}</h2>

<p>{!FULL_PERMISSION_LIST_SEE_FILE}</p>

{+START,IF_NON_EMPTY,{PERMISSION}}
	<div class="permissions_failed">{PERMISSION*}</div>
{+END}
