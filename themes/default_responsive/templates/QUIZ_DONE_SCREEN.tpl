{TITLE}

<p class="quiz_result_headline">
	{RESULT}
</p>

{+START,IF_NON_EMPTY,{MESSAGE}}
	{$PARAGRAPH,{MESSAGE}}
{+END}

{+START,IF,{REVEAL_ANSWERS}}
	{+START,INCLUDE,QUIZ_RESULTS}{+END}
{+END}

{+START,IF,{$NOT,{REVEAL_ANSWERS}}}
	{+START,IF_NON_EMPTY,{CORRECTIONS}{AFFIRMATIONS}}
		<ul class="spaced_list">
			{CORRECTIONS}{AFFIRMATIONS}
		</ul>
	{+END}
{+END}
