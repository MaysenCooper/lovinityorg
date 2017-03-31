{TITLE}

{+START,IF,{$NOT,{$ISSET,SVG_ONCE}}}
	<div class="box"><p class="box_inner">{!SVG_EXPLANATION}</p></div>
	{$SET,SVG_ONCE,1}
{+END}

<h2>{!PAGES_STATISTICS}</h2>
{+START,IF_EMPTY,{STATS_VIEWS}}
	<p>{!NO_DATA}</p>
{+END}
{STATS_VIEWS}

<h2>{!VIEWS_PER_HOUR}</h2>
{+START,IF_EMPTY,{STATS_VIEWS_HOURLY}}
	<p>{!NO_DATA}</p>
{+END}
{GRAPH_VIEWS_HOURLY}
{STATS_VIEWS_HOURLY}

<h2>{!VIEWS_PER_DAY}</h2>
{+START,IF_EMPTY,{STATS_VIEWS_DAILY}}
	<p>{!NO_DATA}</p>
{+END}
{GRAPH_VIEWS_DAILY}
{STATS_VIEWS_DAILY}

<h2>{!VIEWS_PER_WEEK}</h2>
{+START,IF_EMPTY,{STATS_VIEWS_WEEKLY}}
	<p>{!NO_DATA}</p>
{+END}
{GRAPH_VIEWS_WEEKLY}
{STATS_VIEWS_WEEKLY}

<h2>{!VIEWS_PER_MONTH}</h2>
{+START,IF_EMPTY,{STATS_VIEWS_MONTHLY}}
	<p>{!NO_DATA}</p>
{+END}
{GRAPH_VIEWS_MONTHLY}
{STATS_VIEWS_MONTHLY}

<h2>{!KEYWORDS_SHARE}</h2>
{+START,IF_EMPTY,{STATS_KEYWORDS}}
	<p>{!NO_DATA}</p>
{+END}
{GRAPH_KEYWORDS}
{STATS_KEYWORDS}

<h2>{!REGIONALITY_SHARE}</h2>
{+START,IF_EMPTY,{STATS_REGIONALITY}}
	<p>{!NEED_GEOLOCATION_DATA}</p>
{+END}
{GRAPH_REGIONALITY}
{STATS_REGIONALITY}

<h2>{!BROWSER_SHARE}</h2>
{+START,IF_EMPTY,{STATS_BROWSER}}
	<p>{!NO_DATA}</p>
{+END}
{GRAPH_BROWSER}
{STATS_BROWSER}

<h2>{!REFERRER_SHARE}</h2>
{+START,IF_EMPTY,{STATS_REFERRER}}
	<p>{!NO_DATA}</p>
{+END}
{GRAPH_REFERRER}
{STATS_REFERRER}

<h2>{!OS_SHARE}</h2>
{+START,IF_EMPTY,{STATS_OS}}
	<p>{!NO_DATA}</p>
{+END}
{GRAPH_OS}
{STATS_OS}

<h2>{!IP_ADDRESS_DISTRIBUTION}</h2>
{+START,IF_EMPTY,{STATS_IP}}
	<p>{!NO_DATA}</p>
{+END}
{GRAPH_IP}
{STATS_IP}
