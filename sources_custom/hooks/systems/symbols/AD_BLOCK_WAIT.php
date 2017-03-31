<?php

/*
This can be used to find percentile of goal met for new members.
*/

/**
 * Hook class.
 */
class Hook_symbol_AD_BLOCK_WAIT
{
    public function run($param)
    {

		sleep(2);
		attach_message(protect_from_escaping('The Lovinity Community+ has detected that you are using an ad blocker. Ads help financially support our community. Our ads are designed not to interfere with the user experience. Please help us out and disable ad blocker. Those who allow ads get page load speeds 2 seconds faster.'), 'warn');
		
		return null;
    }
}
