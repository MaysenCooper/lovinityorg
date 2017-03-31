<?php

/* REQUIREMENTS FOR CHARACTERS
 * 
 * *If a character has wings, you MUST add if ($p['hook'] == 'info') { $p['wings'] = true; } to the character's special code.
 * *If a character can fly or hover off ground, you MUST add if ($p['hook'] == 'info') { $p['cangooffground'] = true; } to the character's special code.
 * *If a character is fire based, you MUST add if ($p['hook'] == 'info') { $p['firecharacter'] = true; } to the character's special code.
 * 
 */

/* NOTE ABOUT ATTACKS
 * Make sure they contain the following code for the rp_do_damage function:
 * if ($results['fainted'])
	$output .= "\n" . '***' . rp_get_character_name($results['targetcharacter']) . ' HAS FAINTED!***' . "\n";
*/

/* CHARACTER HOOKS
 * 
 * receiveattack
 * Used for characters that are receiving attack. Executed before an attack code is executed. Modifiers are attackid, class, and evalcode.
 * 
 * preailment
 * Executed prior to adding an ailment to change the effects of an ailment or determine a character is immune to that ailment.
 * Modifiers: 'rp_id', 'character_id', 'ailment_type', 'alignment', 'frequency', 'time_left', 'stats_text', 'code', 'doailment'
 */
 
/* CALCULATING ATTACK ENERGY REQUIREMENTS
 * 
 * Here is a general guide of how much EP an attack should require:
 * 
 * 1 EP for every 2 HP max damage caused against a single target, or 1 EP for every 1 HP max damage if the attack affects multiple targets.
 * For HP poison ailments that run every 5 RP minutes, 1 EP for every 1 max HP damage. If it runs every 10 RP minutes, 1 EP for every 2 HP. 15 = 1 EP for every 3 HP. And so on.
 * For stat modifiers, X = 0.5 for every 5 RP minutes the ailment lasts. Take 1/2 the amount the stat is modified by, and multiply by X. Example, +50 modifier for 15 minutes = 25 * 1.5 = 38 EP.
 * For shields that protect against any attacks dealing under a specific HP damage: X = 0.5 for every 5 RP minutes. Take the max HP damage the shield protects against and multiply by X.
 * For shields that reduce HP damage by a percent, X = 0.5 for every 5 RP minutes. Take the percent reduction in damage (as a decimal) and multiply 50 by it. Then multiply that by X.
 * For stunning attacks that stun a character from being able to attack: 30 EP for every 5 RP minutes of stunning (maximum 20 minutes for 100 EP).
 * For negative ailment curing attacks, 30 EP.
 * For health regenerating attacks, 25 EP for 25 HP healing. No other amount permitted without being a healer class or using an item.
 * For energy regenerating attacks, that is completely pointless; energy can only be regenerated via. Healer or items.
 * For health leeching attacks, 1 EP for every 1 max HP leaching. Maximum 50 HP permitted.
 * For attacks that boost max HP damage of all future attacks, X = 1 for every 5 RP minutes. Take 1/2 of the max HP boost and multiply it by X.
 * 
 */

/* AILMENT TYPE REFERENCE
 * Use these type names for ailments.
 * 
 * WARNING! rp_add_ailment returns a string of extra bot output if a hook caused changes to an ailment's effects. It also returns 0 if the ailment failed.
 * 
 * strength, defense, speed, accuracy, endurance, immunity, intuition, XP, HP, energy
 * These types IMMEDIATELY modify what is RETURNED via. rp_get_stat (but does not apply modification permanently).
 * RETURN INT indicating how much to modify the stat by (negatives reduce the stat).
 * 
 * strengthX, defenseX, speedX, accuracyX, enduranceX, immunityX, intuitionX, XPX, HPX, energyX
 * Same as above, but once it is executed, it is immediately deleted (useful for one-time ailments).
 * RETURN INT how much to modify the stat by.
 * 
 * HPpoison
 * Used for ailments which reduce HP over time. 
 * RETURN STRING for the rp_pass_time function (use $output because of the code note below).
 * Make sure they contain the following code from the $fainted = rp_do_damage function:
	if ($fainted)
		$output .= "\n" . \'***' . rp_get_character_name($results['targetcharacter']) . ' HAS FAINTED!***\';
 * 
 * stun
 * Used to cause a character to be unable to attack (but they can still use items and item-based weapons)
 * RETURN 1 to indicate the character is stunned.
 * Stun should always have a time_left which equates to the number of RP minutes the character is stunned.
 * 
 * maxhpS, maxhpSX, maxhpT, and maxhpTX
 * These are ailments that modify max HP damage of an attack. maxhpS are ailments from source character, maxhpT are from target character.
 * Ailments with an X at the end only run one time.
 * PASSES maxhp the max HP damage currently for the attack.
 * RETURN INT by how much the max HP should be modified.
 * 
 * passtimecode
 * These ailments simply execute code. They run during the passtime depending on set frequency.
 * PASSES NOTHING; you must include everything inside the eval code.
 * RETURN string something for the bot for passtime.
 * 
 * 
 * protection
 * Used for ailments applied to the self for reducing the amount of HP damage taken (shields).
 * PARAM $p['damage'] passed to indicate original HP damage calculation.
 * RETURN INT a modified HP damage calculation.
 * 
 * protectionA
 * Applied by a protector to a character to take their HP damage (with a +25 defense) on the next attack dealt to them.
 * RETURN INT the character ID of the protector who will take the damage.
 * 
 * protectionC
 * Same as protection, but instead is a one-time use ailment only.
 * RETURN INT the modified HP damage calculation.
 * 
 */


/* FUNCTION rp_damage
 * Calculate how much damage an attack should do.
 * $sourcecharacter INT the ID of the character dealing the attack
 * $targetcharacter INT the ID of the character taking the attack
 * $maxhp INT the maximum HP damage possible
 * $rp INT (optional) the RP ID
 * RETURN INT the amount of damage to deal in HP
 */
function rp_damage($sourcecharacter, $targetcharacter, $maxhp, $rp = null)
{
$strength = rp_get_stat('strength', $sourcecharacter, $rp);
$defense = rp_get_stat('defense', $targetcharacter, $rp);

$x = (float)mt_rand()/(float)getrandmax();
if (($strength + $defense) == 0)
{
	$y = 1;
} else {
	$y = (($strength / ($strength + $defense) * 0.75) + 0.25);
}
$damage = (pow($x,($y / $y / $y))*$maxhp);
return integer_format($damage);
}

/* FUNCTION rp_miss
 * Determine if an attack misses.
 * $sourcecharacter INT the ID of the character dealing the attack
 * $targetcharacter INT the ID of the character taking the attack
 * RETURN BOOL whether or not the attack missed.
 */
function rp_miss($sourcecharacter, $targetcharacter, $rp = null)
{
	$accuracy = rp_get_stat('accuracy', $sourcecharacter, $rp);
	$speed = rp_get_stat('speed', $targetcharacter, $rp);
		
	$x = (float)mt_rand()/(float)getrandmax();
	if (($accuracy + $speed) == 0)
	{
		$y = 0.5;
	} else {
		$y = (($accuracy / ($accuracy + $speed)) * 0.4) + 0.1;
	}
	return ($x < $y);
}

/* FUNCTION rp_immune
 * Determine if an attempted ailment, spell, curse, etc. was successful.
 * $targetcharacter INT the ID of the character taking the attack
 * RETURN BOOL whether or not the spell, curse, etc. was evaded.
 */
function rp_immune($targetcharacter, $rp = null)
{
	$immunity = rp_get_stat('immunity', $targetcharacter, $rp);
			
	$x = (float)mt_rand()/(float)getrandmax();
	$y = ($immunity / 100) * 0.5;
	return ($x < $y);
}

/* FUNCTION rp_info
 * Determine if information was obtained via. intuition stat.
 * $sourcecharacter the ID of the character retrieving the information
 * RETURN BOOL whether or not the information was obtained.
 */
function rp_info($sourcecharacter, $rp = null)
{
	$intuition = rp_get_stat('intuition', $sourcecharacter);

	$x = (float)mt_rand()/(float)getrandmax();
	$y = (($intuition / 100) * 0.5) + 0.1;
	return ($x < $y);
}

/* FUNCTION rp_regeneration
 * Determine how much energy should be regenerated based on endurance and randomness.
 * $sourcecharacter INT the ID of the character being regenerated.
 * RETURN INT the number of energy points being regenerated.
 */
function rp_regeneration($sourcecharacter, $rp = null)
{
	$endurance = rp_get_stat('endurance', $sourcecharacter, $rp);

	$x = (float)mt_rand()/(float)getrandmax();
	$y = 10 + ((($endurance / 100) * $x) * 40);
	return integer_format($y);
}


/* FUNCTION rp_get_stat
 * Get a stat of a character, including HP and energy.
 * $stat STRING the name of the stat to get.
 * $sourcecharacter INT the ID of the character we're getting the stat of.
 * $rp INT (Optional, unless getting energy or HP) the ID of the RP. If provided, modifiers will be factered in.
 * RETURN INT the stat.
 */
function rp_get_stat($stat, $sourcecharacter, $rp = null)
{
	
	$field = rp_get_field($stat);
	
	if ($field != 0)
	{
		$thestat = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_integer', 'cv_value', array('cf_id' => $field, 'ce_id' => $sourcecharacter));
		if ($rp != null) // factor in the modifiers from ailments
			$thestat += rp_execute_ailments_type($sourcecharacter, $rp, $stat);
	}
	
	if ($stat == 'energy' || $stat == 'HP')
	{
		if ($rp == null)
			return -1;
			
		$thestat = $GLOBALS['SITE_DB']->query_select_value_if_there('rp_participants', $stat, array('character_id' => $sourcecharacter, 'rp_id' => $rp));
	}
	
	// Leader class characters get a permanent +25 intuition. Instead of having to add that as an ailment, we'll just hard code it here.
	if ($stat == 'intuition')
	{
		$class = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_short', 'cv_value', array('cf_id' => '83', 'ce_id' => $sourcecharacter));
		if ($class == 'Leader')
			$thestat += 25;
	}
	// Execute all one-time stat modifiers and delete them
		$thestat += rp_execute_ailments_type($sourcecharacter, $rp, $stat . 'X', true, true);

	return $thestat;
}

/* FUNCTION rp_modify_stat
 * PERMANENTLY modify a stat of a user (Does not work for energy nor HP)
 * $stat STRING the stat to modify
 * $sourcecharacter INT the ID of the character we're modifying the stat of
 * $newstat INT the new stat that should be assigned to the character
 * RETURN BOOL whether or not the modification was successful.
 */
function rp_modify_stat($stat, $sourcecharacter, $newstat)
{
		
	$field = rp_get_field($stat);
	if ($field == 0)
		return false;
		
	return $GLOBALS['SITE_DB']->query_update('catalogue_efv_integer', array('cv_value' => $newstat), array('cf_id' => $field, 'ce_id' => $sourcecharacter), '', 1);	
}

/* FUNCTION rp_add_ailment
 * Adds an ailment or a temporary stat modifier to a character.
 * $type STRING the type of ailment.
 * $sourcecharacter INT the ID of the character we're applying the ailment to.
 * $alignment CHOICE whether this ailment is considered to be positive, neutral, or negative.
 * $rp INT the ID of the rp this ailment should apply to.
 * $name STRING what appears in the rp_get_stats function for this ailment (eg. "Stampede wound (removes 1-10 HP every 5 RP minutes)")
 * $frequency INT (optional) execute this ailment every frequency RP minutes.
 * $time_left INT (optional) Specify how long, in RP minutes, this ailment will last.
 * $code EVALSTRING (optional) The PHP code to execute. It should return something.
 * RETURN STRING extra output for the bot, if applicable, or 0 if the ailment failed.
 */
function rp_add_ailment($type, $sourcecharacter, $alignment, $rp, $name, $frequency = null, $time_left = null, $code = null)
{
	// execute preailment hooks
	$p = array('rp_id' => $rp, 'character_id' => $sourcecharacter, 'ailment_type' => $type, 'alignment' => $alignment, 'frequency' => $frequency, 'time_left' => $time_left, 'stats_text' => $name, 'code' => $code, 'doailment' => true);
	$p2 = rp_do_character_hook($sourcecharacter, 'preailment', $rp, $p);
	$extraoutput = '';
	if (is_array($p2) && array_key_exists('ailment_type',$p2))
	{
	$rp = $p2['rp_id'];
	$sourcecharacter = $p2['character_id'];
	$type = $p2['ailment_type'];
	$alignment = $p2['alignment'];
	$frequency = $p2['frequency'];
	$time_left = $p2['time_left'];
	$name = $p2['stats_text'];
	$code = $p2['code'];
	$doailment = $p2['doailment'];
	$extraoutput = $p2['extraoutput'];
	
		if (!$doailment)
		return 0;
	}
	
	// add the ailment	
	$GLOBALS['SITE_DB']->query_insert('rp_ailments', array(
            'rp_id' => $rp,
            'character_id' => $sourcecharacter,
            'ailment_type' => $type,
            'alignment' => $alignment,
            'frequency' => $frequency,
            'time_left' => $time_left,
            'stats_text' => $name,
            'code' => $code
    ));	
    return $extraoutput;
}

/* FUNCTION rp_has_ailment
 * Check to see if a character has a specified ailment active, by name.
 * $name STRING the name of the ailment (stats_text).
 * $character INT the ID of the character we're checking for.
 * $rp INT the ID of the RP we're checking for.
 * RETURN BOOL whether or not the specified character has the specified ailment active.
 */
function rp_has_ailment($name, $character, $rp)
{
	$ailments = $GLOBALS['SITE_DB']->query_select('rp_ailments', array('*'), array('rp_id' => $rp, 'character_id' => $character, 'stats_text' => $name), ' AND ((time_left IS NOT NULL AND time_left > 0) OR time_left IS NULL)');	
	if (count($ailments) > 0)
		return true;
	return false;
}

/* FUNCTION rp_cure_ailments
 * Cures ailments of a certain type, alignment, combination of both, or all.
 * $sourcecharacter INT the ID of the character we're curing the ailments of.
 * $rp INT the ID of the rp we're curing the ailments from.
 * $type STRING (optional) if provided, will add a condition to only cure ailments matching this type.
 * $alignment STRING (optional) if provided, will add a condition to only cure ailments matching this alignment.
 * RETURN BOOL whether or not the ailments were cured.
 */
function rp_cure_ailments($sourcecharacter, $rp, $type = null, $alignment = null)
{
	$where = array();
	$where['rp_id'] = $rp;
	$where['character_id'] = $sourcecharacter;
	if ($type != null)
		$where['ailment_type'] = $type;
	if ($alignment != null)
		$where['alignment'] = $alignment;
		
	return $GLOBALS['SITE_DB']->query_delete('rp_ailments', $where);
}


/* FUNCTION rp_get_field
 * Get the catalogue field ID for the stat provided.
 * $stat STRING the stat
 * RETURN INT the field ID, or 0 if it was not found.
 */
function rp_get_field($stat)
{
	if ($stat == 'strength')
		return 76;
	if ($stat == 'defense')
		return 77;
	if ($stat == 'speed')
		return 78;
	if ($stat == 'accuracy')
		return 79;
	if ($stat == 'endurance')
		return 80;
	if ($stat == 'immunity')
		return 81;
	if ($stat == 'intuition')
		return 82;
	if ($stat == 'XP')
		return 84;
		
	return 0;
}

/* FUNCTION rp_modify_HP
 * Modify the HP of a character.
 * $sourcecharacter INT the ID of the character we're modifying the HP of.
 * $amount INT If not explicit, change the HP by this amount (use negative to reduce). If explicit, set HP exactly to this.
 * $rp INT the ID of the rp we're modifying HP of.
 * $nolimits BOOL (optional) whether we should ignore the max HP cap on the character.
 * $explicit BOOL (optional) whether the HP amount provided is a change (false), or the exact HP to set to (true).
 * RETURN BOOL true means the character has fainted.
 */
function rp_modify_HP($sourcecharacter, $amount, $rp, $nolimits = false, $explicit = false)
{
	$hp = rp_get_stat('HP', $sourcecharacter, $rp);
	$maxhp = rp_get_max_HP($sourcecharacter, $rp);
	$modifiedhp = ($explicit) ? $amount : ($hp + $amount);
	if (!$nolimits)
	{
		if ($modifiedhp < 0)
			$modifiedhp = 0;
		if ($modifiedhp > $maxhp && $amount > 0)
			$modifiedhp = ($hp > $maxhp) ? $hp : $maxhp;
			return $GLOBALS['SITE_DB']->query_update('rp_participants', array('HP' => $modifiedhp), array('rp_id' => $rp, 'character_id' => $sourcecharacter), '', 1);	
	} else {
		return $GLOBALS['SITE_DB']->query_update('rp_participants', array('HP' => $modifiedhp), array('rp_id' => $rp, 'character_id' => $sourcecharacter), '', 1);
	}
	if ($modifiedhp <= 0)
		return true;
	return false;
}


/* FUNCTION rp_modify_energy
 * Modify the energy of a character.
 * $sourcecharacter INT the ID of the character we're modifying the energy of.
 * $amount INT If not explicit, change energy by this amount (use negative to reduce). If explicit, set energy exactly to this.
 * $rp INT the ID of the rp we're modifying energy of.
 * $nolimits BOOL (optional) whether we should ignore the max energy cap on the character.
 * $explicit BOOL (optional) whether the energy amount provided is a change (false), or the exact energy to set to (true).
 * RETURN BOOL whether or not the energy was modified.
 */
function rp_modify_energy($sourcecharacter, $amount, $rp, $nolimits = false, $explicit = false)
{
	$energy = rp_get_stat('energy', $sourcecharacter, $rp);
	$maxenergy = rp_get_max_energy($sourcecharacter, $rp);
	$modifiedenergy = ($explicit) ? $amount : ($energy + $amount);
	if (!$nolimits)
	{
		if ($modifiedenergy > $maxenergy && $amount > 0)
			$modifiedenergy = ($energy > $maxenergy) ? $energy : $maxenergy;
		if ($modifiedenergy < 0)
			$modifiedenergy = 0;
		return $GLOBALS['SITE_DB']->query_update('rp_participants', array('energy' => $modifiedenergy), array('rp_id' => $rp, 'character_id' => $sourcecharacter), '', 1);	
	} else {
		return $GLOBALS['SITE_DB']->query_update('rp_participants', array('energy' => $modifiedenergy), array('rp_id' => $rp, 'character_id' => $sourcecharacter), '', 1);
	}
	return false;
}


/* FUNCTION rp_get_max_HP
 * Get the maximum HP a user can have (100 + 1 for every 50 XP. Also account any ailments that change this cap.).
 * $sourcecharacter INT the ID of the character we're getting the max HP of.
 * $rp INT the ID of the rp we're getting the max HP of.
 * RETURN INT the maximum HP the user can have.
 */
function rp_get_max_HP($sourcecharacter, $rp)
{
	$xp = rp_get_stat('XP', $sourcecharacter);
	$maxhp = 100 + (integer_format($xp / 50));
	$maxhp += rp_execute_ailments_type($sourcecharacter, $rp, 'maxHP');
	return $maxhp;
}

/* FUNCTION rp_get_max_energy
 * Get the maximum energy a user can have (100, unless ailments modify it.).
 * $sourcecharacter INT the ID of the character we're getting the max energy of.
 * $rp INT the ID of the rp we're getting the max energy of.
 * RETURN INT the maximum energy the user can have.
 */
function rp_get_max_energy($sourcecharacter, $rp)
{
	$maxenergy = 100;
	$maxenergy += rp_execute_ailments_type($sourcecharacter, $rp, 'maxenergy');
	return $maxenergy;
}

/* FUNCTION rp_execute_ailments_type
 * Execute all of the ailments of a given type.
 * $sourcecharacter INT the ID of the character we're executing the ailments of.
 * $rp INT the ID of the rp we're involved in.
 * $type STRING the type of ailments we're executing.
 * $doall BOOL (optional) set to false to only execute one of the ailments, else execute all of them.
 * $delete BOOL (optional) set to true to remove the ailments after execution, regardless of expiration.
 * $p MIXED (optional) pass additional parameters to the ailment code. Use arrays for multiple parameters.
 * RETURN MIXED can return results of execution, the amount of a stat all the ailments combined modifies, or 0 for nothing.
 */
function rp_execute_ailments_type($sourcecharacter, $rp, $type, $doall = true, $delete = false, $p = null)
{
	$thestat = 0;
	$id = 0;
	$modifier = $GLOBALS['SITE_DB']->query_select('rp_ailments', array('*'), array('rp_id' => $rp, 'character_id' => $sourcecharacter, 'ailment_type' => $type), ' AND (`time_left` IS NOT NULL AND `time_left` > 0)');
	foreach ($modifier as $mod)
	{
		if ($doall)
		{
			$thestat += eval($mod['code']);
		} else {
			$id = $mod['ID'];
			$code = $mod['code'];
		}
	}
	if ($id != 0)
		$thestat = eval($code);
		
	if ($delete && $doall)
	{
		$GLOBALS['SITE_DB']->query_delete('rp_ailments', array('rp_id' => $rp, 'character_id' => $sourcecharacter, 'ailment_type' => $type));
	} elseif ($delete)
	{
		$GLOBALS['SITE_DB']->query_delete('rp_ailments', array('ID' => $id));
	}
	
	return $thestat;
}


/* FUNCTION rp_pass_time
 * Advance the RP by a specified number of minutes.
 * $rp INT the ID of the RP we're advancing the time of.
 * $time_passed INT the number of minutes that passed.
 * $doregeneration BOOL (optional) set to false to not perform energy regeneration every 5 RP minutes.
 * $doailments BOOL (optional) set to false to not execute ailments when they need to.
 * RETURN STRING a response to be sent to the bot.
 */
function rp_pass_time($rp, $time_passed, $doregeneration = true, $doailments = true)
{
	$returntext = '';
	$returntext2 = '';
	$rptime = $GLOBALS['SITE_DB']->query_select_value_if_there('rp', 'time_advanced', array('ID' => $rp));
	$GLOBALS['SITE_DB']->query_update('rp', array('time_advanced' => ($rptime + $time_passed)), array('ID' => $rp), '', 1);
	
	// We got to perform actions for every minute tick to ensure accurate regeneration and ailment execution.
	$clock = $rptime;
	while ($clock < ($rptime + $time_passed))
	{
		$clock++;
		if ($clock % 5 == 0 && $doregeneration) // energy regeneration every 5 RP minutes
		{
			$characters = $GLOBALS['SITE_DB']->query_select('rp_participants', array('*'), array('rp_id' => $rp, 'verified' => '1'), '');
			if (count($characters) > 0)
				$returntext .= '=====ENERGY REGENERATION=====
				';
			foreach ($characters as $char)
			{
				$regeneration = rp_regeneration($char['character_id'], $rp);
				$success = rp_modify_energy($char['character_id'], $regeneration, $rp);
				$returntext .= rp_get_character_name($char['character_id']) . ' has re-generated [up to] ' . $regeneration . ' energy. Character now has ' . rp_get_stat('energy', $char['character_id'], $rp) . ' energy.' . "\n";
			}
			if (count($characters) > 0)
				$returntext .= '==========
				
				';
		}
		
		if ($doailments)
		{
			$ailments = $GLOBALS['SITE_DB']->query_select('rp_ailments', array('*'), array('rp_id' => $rp), '');	
			$ailmentsused = 0;		
			foreach ($ailments as $ailment)
			{
				$timeleft = $ailment['time_left'];
				if ($timeleft != null)
				{
					$timeleft--;
					$GLOBALS['SITE_DB']->query_update('rp_ailments', array('time_left' => $timeleft), array('ID' => $ailment['ID']), '', 1);
					if ($timeleft == 0)
						$GLOBALS['SITE_DB']->query_delete('rp_ailments', array('ID' => $ailment['ID']));
					$comparison = $timeleft; // Our frequency of execution is compared to the time left if the ailment has time on it.
				} else {
					$comparison = $clock; // If the ailment does not expire, the frequency of execution is compared to the RP time.
				}
				$frequency = $ailment['frequency'];
				if ($frequency != null && $comparison % $frequency == 0)
				{
					$returntext2 .= eval($ailment['code']) . '
					';
					$ailmentsused++;
				}
			}
			if ($ailmentsused > 0)
				$returntext .= '=====AILMENTS=====
				' . $returntext2 . '
				==========
				';		
			}
	}
	
	$returntext .= 'The time is now ' . ($rptime + $time_passed) . ' minutes into the role play.';
	return $returntext;
	
}


/* FUNCTION rp_get_character_name
 * Get the name of a character ID.
 * $character INT the ID of the character we want to get the name of
 * RETURN STRING the name of the character.
 */
function rp_get_character_name($character)
{
	return $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_short', 'cv_value', array('cf_id' => '69', 'ce_id' => $character));
}

/* FUNCTION rp_get_character_owner
 * Get the user ID who owns the given character
 * $character ID the ID of the character we're getting the owner of.
 * RETURN ID the user ID who owns that character.
 */
function rp_get_character_owner($character)
{
	return $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_entries', 'ce_submitter', array('id' => $character));	
}


/* FUNCTION rp_do_protection
 * Check to see if a character being attacked was granted protection by another character, such as a Protector.
 * $sourcecharacter INT the ID of the character that is being attacked.
 * $rp INT the RP ID.
 * RETURN INT returns either the ID of the character being attacked if that character has no protection, or the ID of the character that should be dealt the damage if protection was granted.
 */
function rp_do_protection($sourcecharacter, $rp)
{
	$newcharacter = rp_execute_ailments_type($sourcecharacter, $rp, 'protectionA', false, true);
	if ($newcharacter != 0)
	{
		rp_add_ailment('defenseX', $newcharacter, 'positive', $rp, 'defenseX', null, 1, 'return 25;');
		return $newcharacter;
	}
		
	return $sourcecharacter;
}

/* FUNCTION rp_do_attack
 * Process an attack on a character.
 * $sourcecharacter INT the ID of the character launching the attack.
 * $targetcharacter INT the ID of the character receiving the attack. Can be 0 if the attack code determines who to attack (eg. all warriors), or if the attack does not target other characters (such as a shield).
 * $rp INT the ID of the rp.
 * $attack STRING the name of the attack.
 * $p ARRAY (optional) parameters to pass to the attack code. Usually not used.
 * RETURN STRING the result of the attack, to be echoed by the bot.
 */
 
function rp_do_attack($sourcecharacter, $targetcharacter, $rp, $attack, $p = null)
{
	
	$attackid = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_short', 'ce_id', array('cf_id' => '123', 'cv_value' => $attack));
	if ($attackid == null)
		return 'Error! We could not find an attack with that name.';
		
	if ($targetcharacter != 0 && !rp_in_rp($targetcharacter, $rp))
		return 'Error! You cannot attack a character that is not involved in a role play.';
		
	$availableattacks = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_long', 'cv_value', array('cf_id' => '125', 'ce_id' => $sourcecharacter));
	$_availableattacks = preg_split("/[\r\n]+/", $availableattacks);
	if (!in_array($attackid, $_availableattacks))
		return 'Error! ' . rp_get_character_name($sourcecharacter) . ' is not able to use the provided attack.';
	if (rp_get_stat('HP', $sourcecharacter, $rp) < 1)
		return 'Error! ' . rp_get_character_name($sourcecharacter) . ' is fainted and cannot attack.';
	if ($targetcharacter != 0 && rp_get_stat('HP', $targetcharacter, $rp) < 1)
		return 'Error! ' .  rp_get_character_name($targetcharacter) . ' has already fainted. No need to attack them again.';
	
	$temp = rp_execute_ailments_type($sourcecharacter, $rp, 'stun', true, false, array());
	if ($temp !== 0 && $temp !== null)
		return 'Error! ' . rp_get_character_name($targetcharacter) . ' is stunned and cannot attack! You can however use items.';
		
	$class = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_long', 'cv_value', array('ce_id' => $attackid, 'cf_id' => '128'));
	$evalcode = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_long', 'cv_value', array('ce_id' => $attackid, 'cf_id' => '124'));
	
	// process attack receiving hooks if source is not target
	$extraoutput = '';
	if ($sourcecharacter !== $targetcharacter)
	{
	$p = array('attackid' => $attackid, 'class' => $class, 'evalcode' => $evalcode, 'sourcecharacter' => $sourcecharacter, 'targetcharacter' => $targetcharacter, 'extraoutput' => '');
	$p2 = rp_do_character_hook($targetcharacter, 'receiveattack', $rp, $p);
	if (is_array($p2) && array_key_exists('class',$p2))
	{
	$attackid = $p2['attackid'];
	$class = $p2['class'];
	$evalcode = $p2['evalcode'];
	$extraoutput = $p2['extraoutput'];
	}
}
	
	return eval($evalcode) . $extraoutput;
}

/* FUNCTION rp_do_damage
 * Process HP damage on a character from an attack.
 * $sourcecharacter INT the ID of the character dealing the damage
 * $targetcharacter INT the ID of the character the damage is directed towards.
 * $maxhp INT the maximum possible HP damage the attack can deal.
 * $rp INT the RP ID.
 * $avoidprotection BOOL (optional) set to true to avoid all protection ailments.
 * RETURN ARRAY (targetcharacter => the ID of the character the attack officially affected, damage => the amount of HP damage dealt, fainted => True if the target fainted.)
 */
function rp_do_damage($sourcecharacter, $targetcharacter, $maxhp, $rp, $avoidprotection = false)
{
	// First, determine if protectionA was given by another character, and if so, reroute the attack.
	$_targetcharacter = rp_do_protection($targetcharacter, $rp);
	
	// Next, process any max damage modifying ailments that the source character has
	$maxhp += rp_execute_ailments_type($sourcecharacter, $rp, 'maxhpSX', true, true, array('maxhp' => $maxhp));
	$maxhp += rp_execute_ailments_type($sourcecharacter, $rp, 'maxhpS', true, false, array('maxhp' => $maxhp));
	
	// Process any max damage modifying ailments that the target character has
	$maxhp += rp_execute_ailments_type($_targetcharacter, $rp, 'maxhpTX', true, true, array('maxhp' => $maxhp));
	$maxhp += rp_execute_ailments_type($_targetcharacter, $rp, 'maxhpT', true, false, array('maxhp' => $maxhp));
	
	if ($maxhp < 0)
		$maxhp = 0;
	
	// Third, calculate HP damage done
	$damage = rp_damage($sourcecharacter, $_targetcharacter, $maxhp, $rp);
	
	
	// Fourth, process any one-time HP damage modifiers via. protectionC
	$damage += rp_execute_ailments_type($_targetcharacter, $rp, 'protectionC', true, true, array('damage' => $damage));
	if ($damage <= 0)
		$damage = 0;
	
	// Fifth, process any self-protection ailments applied, and modify the HP damage accordingly
	$damage += rp_execute_ailments_type($_targetcharacter, $rp, 'protection', true, false, array('damage' => $damage));
	if ($damage <= 0)
		$damage = 0;
	
	// Sixth, apply the damage
	$fainted = rp_modify_HP($_targetcharacter, -$damage, $rp);
	
	// Finally, return some response
	return array('sourcecharacter' => $sourcecharacter, 'targetcharacter' => $_targetcharacter, 'maxhp' => $maxhp, 'damage' => $damage, 'fainted' => $fainted);
	
}

/* FUNCTION rp_do_item
 * Use an item that is in the possession of a character in a role play.
 * $item STRING the name of the item being used.
 * $character INT the ID of the character using the item.
 * $rp INT the ID of the RP we're involved in.
 * $p ARRAY (optional) an array of parameters to pass to the item code ('charge' will ALWAYS be passed through $p within this function).
 * RETURN ARRAY ('color' => $color, 'fields' => $fields) data for bot output.
 */
function rp_do_item($item, $sourcecharacter, $rp, $p = array())
{
	$response = array();
	$itemid = $GLOBALS['SITE_DB']->query_select_value_if_there('rp_items', 'item_id', array('ID' => $item));
	if ($itemid == null)
	{
		$response = array(
		'color' => '16711680',
		'fields' => array(
			'ERROR' => 'The specified item could not be found.'
			)
		);
		return $response;
	}
		
	// the 1 at the end means we only want to execute 1 instance of the item if, say, the character possesses more than one of the same item in the same role play.
	$possessions = $GLOBALS['SITE_DB']->query_select('rp_items', array('*'), array('ID' => $item, 'character_id' => $sourcecharacter, 'rp_id' => $rp), '', 1);
	if (count($possessions) < 1)
	{
		$response = array(
		'color' => '16711680',
		'fields' => array(
			'ERROR' => 'The specified item is not in possession of ' . rp_get_character_name($sourcecharacter) . ' for the specified role play.'
			)
		);
		return $response;
	}
		
	foreach ($possessions as $possession)
	{
		$color = 16777215;
		$fields = array();
		$p['charge'] = $possession['charge'];
		$evalcode = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_long', 'cv_value', array('ce_id' => $itemid, 'cf_id' => '126'));
		$response = array();
		$response = eval($evalcode);
		if ($p['charge'] < 1)
		{
			$GLOBALS['SITE_DB']->query_delete('rp_items', array('ID' => $possession['ID']));
			$fields['Charge Left'] = 'The item charge has been fully used. The item was discarded.';
		} else {
			$GLOBALS['SITE_DB']->query_update('rp_items', array('charge' => $response['charge']), array('ID' => $possession['ID']), '', 1);
			$fields['Charge Left'] = $p['charge'];
		}
	}
	return array('color' => $color, 'fields' => $fields);
}

/* FUNCTION rp_stash_item
 * Stash an item in a user's item repository to be later passed to characters in role play. Used for when members buy new items.
 * $item STRING the name of the item being stashed.
 * $user STRING the TLC+ username getting the item.
 * RETURN STRING the result, sent to the bot for echoing.
 */
function rp_stash_item($item, $user)
{
		$itemid = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_short', 'ce_id', array('cf_id' => '94', 'cv_value' => $item));
	if ($itemid == null)
		return 'Error! The item specified could not be found';
		
		$userid = $GLOBALS['FORUM_DRIVER']->get_member_from_username($user);

	if (is_null($userid))
		return 'The member ' . $user . ' does not exist on TLC+.';
		
		$charge = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_integer', 'cv_value', array('cf_id' => '127', 'ce_id' => $itemid));
		
		$result = $GLOBALS['SITE_DB']->query_insert('rp_items', array(
            'item_id' => $itemid,
            'user_id' => $userid,
            'charge' => $charge
		));
	return 'The item was stashed.';	
}

/* FUNCTION rp_assign_item
 * Allows a user to assign a stashed item to a character in an RP.
 * $user INT the user ID doing the assigning (assigning can only be done by the user owning the item).
 * $item STRING the name of the item being assigned.
 * $character INT the character ID being assigned the item
 * $rp INT the rp ID that the character is getting the item for.
 * RETURN STRING the result to be passed by the bot.
 */
function rp_assign_item($user, $item, $character, $rp)
{
		$itemid = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_short', 'ce_id', array('cf_id' => '94', 'cv_value' => $item));
		if ($itemid == null)
			return 'Error! The item specified could not be found';
		
		if(!rp_in_rp($character, $rp))
			return 'Error! Your character is not currently participating in the provided role play.';
		
		$exists = $GLOBALS['SITE_DB']->query_select_value_if_there('rp_items', 'ID', array('item_id' => $itemid, 'user_id' => $user, 'character_id' => null, 'rp_id' => null));
		if ($exists == null)
			return 'Error! The user does not have any of that item in their stash.';
		
		// The 1 at the end means if the member has more than one of the same item in stash, we only want to assign one of them.
		$result = $GLOBALS['SITE_DB']->query_update('rp_items', array('rp_id' => $rp, 'character_id' => $character), array('item_id' => $itemid, 'user_id' => $user, 'character_id' => null, 'rp_id' => null), '', 1);
	
		return 'The item was assigned to ' . rp_get_character_name($character) . '. The ID of this item is ' . $exists . ' (you will need this ID when using the doitem rp command. If you forget it, you can find it when you access your stats for this RP)';
}

/* FUNCTION rp_get_item_name
 * Get the name of an item by its ID
 * $item INT the item ID
 * RETURN STRING the name of the item, or False if the item could not be found.
 */
function rp_get_item_name($item)
{
	$name = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_short', 'cv_value', array('cf_id' => '94', 'ce_id' => $item));
	if ($name == null)
		return false;
	return $name;
}

/* FUNCTION rp_in_rp
 * Check to see if a specified character is involved in a specified RP.
 * $character INT the character we're checking for.
 * $rp INT the rp ID we're checking to see if the character is in.
 * $ignoreverified BOOL (optional) set to true to not check to see if a character was validated yet in the rp.
 * RETURN BOOL true if the character is involved, false if the character is not, or is but has not been verified.
 */
function rp_in_rp($character, $rp, $ignoreverified = false)
{
	if ($ignoreverified)
	{
		$id = $GLOBALS['SITE_DB']->query_select_value_if_there('rp_participants', 'ID', array('rp_id' => $rp, 'character_id' => $character));
		return $id != null;
	} else {
		$id = $GLOBALS['SITE_DB']->query_select_value_if_there('rp_participants', 'ID', array('rp_id' => $rp, 'character_id' => $character, 'verified' => '1'));
		return $id != null;
	}
	return false;
}

/* FUNCTION rp_exists
 * Check to see if a specified RP ID exists.
 * $rp INT the RP id we're checking for.
 * RETURN BOOL true if the RP ID exists.
 */
function rp_exists($rp)
{
	$id = $GLOBALS['SITE_DB']->query_select_value_if_there('rp', 'ID', array('ID' => $rp));
	if ($id != null)
		return true;
	return false;
}

/* FUNCTION rp_gm
 * Gets the ID of the game master of the specified RP.
 * $rp INT the RP id we're checking for.
 * RETURN INT the RP's game master.
 */
function rp_gm($rp)
{
	return $GLOBALS['SITE_DB']->query_select_value_if_there('rp', 'gm', array('ID' => $rp));
}

/* FUNCTION rp_get_user_by_discord
 * Get the user ID on TLC+ from the discord ID provided.
 * $discord STRING the discord user ID we're checking for.
 * RETURN INT returns the user ID of that user from TLC+, or 0 if the provided discord ID has not linked a TLC+ user to their account.
 */
function rp_get_user_by_discord($discord)
{
	$userid = $GLOBALS['SITE_DB']->query_select_value_if_there('f_member_custom_fields', 'mf_member_id', array('field_69' => $discord));
	if ($userid != null)
		return $userid;
	
	return 0;
}

/* FUNCTION rp_new_rp
 * Start a new role play
 * $gamemaster INT the TLC+ user ID of the user starting the RP.
 * RETURN INT the RP ID of the new RP, or false if a new RP could not be created.
 */
function rp_new_rp($gamemaster)
{
		$GLOBALS['SITE_DB']->query_insert('rp', array(
            'gm' => $gamemaster,
            'time_advanced' => 0
		));	
		return $GLOBALS['SITE_DB']->query_select_value_if_there('rp', 'ID', null, ' ORDER BY ID DESC');
}

/* FUNCTION rp_add_character
 * Adds a character to the given RP as a participant.
 * $character INT the character ID being added.
 * $rp INT the ID of the role play we're adding the character to.
 * $hp INT (optional) the starting amount of HP for the character. null = start with the maximum HP.
 * $autoverify BOOL (optional) set to tru to auto verify the added character
 * RETURN STRING output for the bot.
 */
function rp_add_character($character, $rp, $hp = null, $autoverify = false)
{
		$exists = rp_exists($rp);
		if (!$exists)
			return 'Error! That RP ID does not exist.';
		$alreadyadded = rp_in_rp($character, $rp, true);
		if ($alreadyadded)
			return 'Error! That character was already added to the role play.';
		$maxhp = rp_get_max_HP($character, $rp);
		$hptouse = ($hp == null) ? $maxhp : $hp;
		if ($hptouse > $maxhp)
			return 'Error! You attempted to start the character off on an HP greater than their max permitted HP of ' . $maxhp;
		$owner = rp_get_character_owner($character);
		$GLOBALS['SITE_DB']->query_insert('rp_participants', array(
            'user_id' => $owner,
            'rp_id' => $rp,
            'character_id' => $character,
            'verified' => ($autoverify) ? 1 : 0,
            'HP' => $hptouse,
            'energy' => 100
		));	
		$output = 'Character added!';
		if (!$autoverify)
			$output .= ' Now, the owner of the character must verify this entry by executing the command @tlc-bot#3659 !rp|verify|' . rp_get_character_name($character) . '|' . $rp;
		return $output;
}

/* FUNCTION rp_verify_character
 * Marks an added character as verified
 * $character INT the character ID being verified.
 * $rp INT the ID of the role play we're verifying the character in.
 * $user INT the TLC+ user verifying the character
 * RETURN STRING output for the bot.
 */
function rp_verify_character($character, $rp, $user)
{
	$owner = rp_get_character_owner($character);
	$exists = rp_exists($rp);
	if (!$exists)
		return 'Error! That RP ID does not exist.';
	$alreadyadded = rp_in_rp($character, $rp, false);
	if ($alreadyadded)
		return 'Error! That character has already been added and verified.';
	$alreadyadded = rp_in_rp($character, $rp, true);
	if (!$alreadyadded)
		return 'Error! That character has not been added as a participant to that role play yet. Use the command @tlc-bot#3659 !rp|addcharacter|(character name)|(RP ID)|(OPTIONAL: Starting HP) first.';
	if ($owner != $user)
		return 'Error! The user trying to verify that character is not the owner of that character. Only the owner can verify the character.';
		
	$GLOBALS['SITE_DB']->query_update('rp_participants', array('verified' => 1), array('character_id' => $character, 'rp_id' => $rp), '', 1);	
	return 'Character has been verified and can now be used in the RP!';
}

/* FUNCTION rp_get_character_id
 * Get the ID of the provided character name.
 * $character STRING the name of the character.
 * RETURN INT returns the character ID, 0 if not found, -1 if the character is not canon, or -2 if the character is canon but not yet approved.
 */
function rp_get_character_id($character)
{
	$characterid = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_short', 'ce_id', array('cf_id' => '69', 'cv_value' => $character));
	if ($characterid == null)
		return 0;
	$noncanon = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_entries', 'cc_id', array('id' => $characterid));
	if ($noncanon != 52)
		return -1;
	$approved = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_entries', 'ce_validated', array('id' => $characterid));
	if ($approved != 1)
		return -2;
		
	return $characterid;
}

/* FUNCTION rp_get_stats_array
 * Return the current stats of a character or characters as an array. If an RP ID is provided, the stats are based off of the RP provided.
 * $characters (optional) INT or ARRAY of INTs the character ID or IDs we're getting stats for. Leave null for all characters participating in the RP (rp must not be null)
 * $rp INT (optional) the RP ID of the stats we're getting of
 * RETURN ARRAY array of data for the characters.
 */
function rp_get_stats_array($characters = null, $rp = null)
{
	if (is_null($characters) && is_null($rp))
		return false;
		
	if (is_null($characters))
		$characters = rp_get_characters($rp);
		
	if (!is_array($characters))
		$characters = array($characters);
		
	$stats = array();
	foreach ($characters as $key => $character)
	{
		$_ailments = array();
		$_items = array();
		if ($rp != null)
		{
			$ailments = $GLOBALS['SITE_DB']->query_select('rp_ailments', array('*'), array('rp_id' => $rp, 'character_id' => $character), ' AND ((time_left IS NOT NULL AND time_left > 0) OR time_left IS NULL)');	
			foreach ($ailments as $ailment)
			{
				$_ailments[] = array(
				'text' => $ailment['stats_text'],
				'time_left' => (($ailment['time_left'] == null) ? 'indefinite' : $ailment['time_left']),
				);
			}
			
				$items = $GLOBALS['SITE_DB']->query_select('rp_items', array('*'), array('rp_id' => $rp, 'character_id' => $character), ' AND (charge IS NOT NULL AND charge > 0)');
				foreach ($items as $item)
				{
					$_items[] = array(
					'name' => rp_get_item_name($item['item_id']),
					'ID' => $item['ID'],
					'charge' => $item['charge'],
					);
			
				}
		}
		
		$stats[] = array(
		'character' => $character,
		'character_name' => rp_get_character_name($character),
		'strength' => rp_get_stat('strength', $character, $rp),
		'defense' => rp_get_stat('defense', $character, $rp),
		'speed' => rp_get_stat('speed', $character, $rp),
		'accuracy' => rp_get_stat('accuracy', $character, $rp),
		'endurance' => rp_get_stat('endurance', $character, $rp),
		'immunity' => rp_get_stat('immunity', $character, $rp),
		'intuition' => rp_get_stat('intuition', $character, $rp),
		'XP' => rp_get_stat('XP', $character, $rp),
		'HP' => $rp != null ? rp_get_stat('HP', $character, $rp) : null,
		'maxHP' => $rp != null ? rp_get_max_HP($character, $rp) : null,
		'energy' => $rp != null ? rp_get_stat('energy', $character, $rp) : null,
		'maxenergy' => $rp != null ? rp_get_max_energy($character, $rp) : null,
		'ailments' => $_ailments,
		'items' => $_items,
		);
	}
	return $stats;
}

/* FUNCTION rp_get_stats EXTENDS rp_get_stats_array
 * Output the current stats of a character as an array for bot. If an RP ID is provided, the stats are based off of the RP provided.
 * $characters (optional) INT or ARRAY of INTs the character ID or IDs we're getting stats for. Leave null for all characters participating in the RP (rp must not be null)
 * $rp INT (optional) the RP ID of the stats we're getting of
 * RETURN ARRAY array of fields data to be output by the bot
 */
function rp_get_stats($characters = null, $rp = null)
{
	$stats = rp_get_stats_array($characters, $rp);
	$output = array();
	foreach ($stats as $key => $character)
	{
	$output['Strength (' . $character['character_name'] . ')'] = $character['strength'];
	$output['Defense (' . $character['character_name'] . ')'] = $character['defense'];
	$output['Speed (' . $character['character_name'] . ')'] = $character['speed'];
	$output['Accuracy (' . $character['character_name'] . ')'] = $character['accuracy'];
	$output['Endurance (' . $character['character_name'] . ')'] = $character['endurance'];
	$output['Immunity (' . $character['character_name'] . ')'] = $character['immunity'];
	$output['Intuition (' . $character['character_name'] . ')'] = $character['intuition'];
	$output['XP (' . $character['character_name'] . ')'] = $character['XP'];
	if ($rp != null)
	{
		$output['HP (' . $character['character_name'] . ')'] = $character['HP'] . ' / ' . $character['maxHP'];
		$output['Energy EP (' . $character['character_name'] . ')'] = $character['energy'] . ' / ' . $character['maxenergy'];
		$ailments = $character['ailments'];	
		foreach ($ailments as $key => $ailment)
		{
			$output['AILMENT/POWERUP ' . $key . ' (' . $character['character_name'] . ')'] = $ailment['text'] . '. Time left (RP minutes): ' . $ailment['time_left'];
		}
		$items = $character['items'];
		foreach ($items as $key => $item)
		{
			$output['ITEM #' . $item['ID'] . ' (' . $character['character_name'] . ')'] = $item['name'] . '. Charge left: ' . $ailment['charge'];
			
		}
	}
	}
	
	return $output;
	
}

/* FUNCTION rp_do_character_hook
 * Run the character hook code for a character. Useful for special talents.
 * $character INT the character ID we're running hooks for.
 * $hook STRING the name of the hook to run. Is passed to the code as $p['hook'].
 * $rp INT the RP ID.
 * $p ARRAY an array of numbers to be modified by the hook.
 * RETURN ARRAY the modified $p array after the hook was run.
 */
function rp_do_character_hook($character, $hook, $rp, $p)
{
	if (!array_key_exists('hook',$p))
		$p['hook'] = $hook;
	if (!array_key_exists('sourcecharacter',$p))
		$p['sourcecharacter'] = $character;
	if (!array_key_exists('rp',$p))
		$p['rp'] = $rp;
	if (!array_key_exists('extraoutput',$p))
		$p['extraoutput'] = '';
	$code = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_long', 'cv_value', array('cf_id' => '129', 'ce_id' => $character));
	if (!is_null($code))
	{
		$p2 = eval($code);
	} else {
		$p2 = $p;
	}
	if (is_null($p2))
		$p2 = $p;
		
	return $p2;
}

/* FUNCTION rp_check_character
 * Get the ID of the specified character
 * $character STRING name of the character
 * RETURN MIXED either a string error for the bot, or an INT ID of the character.
 */
function rp_check_character($character)
	{
		$sourcecharacter = rp_get_character_id($character);
		if ($sourcecharacter == 0)
		{
			return 'ERROR! We could not find character ' . $character . ' in the character database. Please check the name and try again.';
		} else if ($sourcecharacter == -1) {
			return 'ERROR! The character ' . $character . ' is a non-canon character and therefore cannot be used by the bot.';
		} else if ($sourcecharacter == -2) {
			return 'ERROR! The character ' . $character . ' was not yet approved by staff for use.';
		} 
		return $sourcecharacter;
	}

/* FUNCTION rp_get_characters
 * Get the ID of all the validated characters participating in a given role play.
 * $rp INT the rp ID
 * RETURN ARRAY the array of character IDs participating in the RP.
 */	
function rp_get_characters($rp)
{
	$characters = $GLOBALS['SITE_DB']->query_select('rp_participants', array('character_id'), array('rp_id' => $rp, 'verified' => '1'));
	$returnarray = array();
	foreach ($characters as $character)
	{
		$returnarray[] = $character['character_id'];
	}
	return $returnarray;
}
