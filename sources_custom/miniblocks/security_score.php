<?php

$securityscore = get_value('security_score');

if ($securityscore < 25)
{
$output = '<span style="color: DarkGreen;">LOW RISK</span>';
} else if ($securityscore < 50)
{
$output = '<span style="color: GoldenRod;">MODERATE RISK</span>';	
} else if ($securityscore < 75) 
{
$output = '<span style="color: OrangeRed;">ELEVATED RISK</span>';	
} else {
$output = '<span style="color: Red;">HIGH RISK</span>';
}

echo $output;