<?php

/*
This can be used to find percentile of goal met for new members.

param[0] = timestamp when goal started.
param[1] = Activity target level.
*/

/**
 * Hook class.
 */
class Hook_symbol_WEBSITE_PASSPHRASE
{
    public function run($param)
    {
	return get_value('website_passphrase');
    }
}