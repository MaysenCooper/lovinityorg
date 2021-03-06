<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core
 */

/**
 * Hook class.
 */
class Hook_discord_hm
{
	public function help()
	{
		return array(
		'!hm' => 'Start a new hangman game.',
		'!hm|(letter)' => 'Guess a letter in the word.',
		'!hm|(word)' => 'Guess what the word is.',
		'hm command note' => 'If you have your Discord linked to your TLC+ account, you will earn Yang for correct word guesses.',
		);
	}
	
	public function run($params, $userid, $username = 'guest')
	{
		$fields = array();
		if (!array_key_exists(1,$params) || $params[1] == '')
		{
			$currentword = $GLOBALS['SITE_DB']->query_select_value_if_there('hangman_words', 'word', array('used' => '1'));
			if ($currentword != null)
			{
				$fields['Information'] = 'A game is already in play';
				$fields['Word'] = $this->render_word($currentword);
				$fields['Guesses Left'] = $this->letters_left();
				return array('title' => '!hm', 'color' => '16776960', 'fields' => $fields);
			}
				
			$id = $GLOBALS['SITE_DB']->query_select_value_if_there('hangman_words', 'ID', null, ' ORDER BY RAND()');
			$word = $GLOBALS['SITE_DB']->query_select_value_if_there('hangman_words', 'word', array('ID' => $id), '');
			$GLOBALS['SITE_DB']->query_update('hangman_words', array('used' => 1), array('ID' => $id), '', 1);
			$fields['Instructions'] = 'Welcome to Hangman! Try to guess what the secret word is. First, guess up to 10 letters by using the command !hm (letter). If you think you know the word, you can guess the whole word instead of the letter, but an incorrect word guess will cost you one letter guess.';
			$fields['Word'] = $this->render_word($word);
			$fields['Guesses Left'] = $this->letters_left();
			return array('title' => '!hm', 'color' => '16776960', 'fields' => $fields);
		} else {
			$output = '';
			$word = $GLOBALS['SITE_DB']->query_select_value_if_there('hangman_words', 'word', array('used' => 1), '');
			if ($word === null)
			{
				$fields['ERROR'] = 'A game has not been started yet! Use !hm to begin a hangman game.';
				return array('title' => '!hm', 'color' => '16711680', 'fields' => $fields);
			}
			if (strtolower($params[1]) === strtolower($word) && $params[1] !== '')
			{
				require_code('rp');
				$user = rp_get_user_by_discord($userid);
				$fields['Result'] = $username . ' has won the game!';
				if ($user != 0)
				{
					require_code('points2');
					$wordsplit = str_split(strtoupper($word));
					$yang = integer_format(count($wordsplit) + (2 * $this->letters_left()));
					system_gift_transfer('Discord hangman win. Word: ' . $word, $yang, $user);
					$fields['Yang earned on TLC+ website account'] = $yang;
				}
			$this->reset_game();
			$fields['Play Again?'] = 'Use the command !hm';
			return array('title' => '!hm', 'color' => '65280', 'fields' => $fields);
			} else {
				if ($this->letters_left() > 0)
				{
					if (strlen($params[1]) == 1)
					{
						if (!preg_match('/^[a-zA-Z]$/', $params[1]))
						{
							$fields['ERROR'] = 'That is not a valid guess.';
							return array('title' => '!hm', 'color' => '16711680', 'fields' => $fields);
						}
						if ($GLOBALS['SITE_DB']->query_select_value_if_there('hangman_letters', 'guessed', array('letter' => strtoupper($params[1])), '') == 1)
						{
							$fields['ERROR'] = 'The letter you provided was already guessed.';
							return array('title' => '!hm', 'color' => '16711680', 'fields' => $fields);
						}
						
						$GLOBALS['SITE_DB']->query_update('hangman_letters', array('guessed' => 1), array('letter' => strtoupper($params[1])), '', 1);
						$fields['Result'] = 'Your letter guess was accepted.';
						if ($this->letters_left() < 1)
							$fields['OUT OF GUESSES'] = 'You are out of guesses! You have one shot at guessing what the word is.';
						$fields['Word'] = $this->render_word($word);
						$fields['Guesses Left'] = $this->letters_left();
						return array('title' => '!hm', 'color' => '16776960', 'fields' => $fields);
					} else {
						$result = $GLOBALS['SITE_DB']->query_insert('hangman_letters', array(
						'letter' => '0',
						'guessed' => 1
						));
						$fields['Result'] = 'Your word guess was not correct. You have been penalized one guess!';
						if ($this->letters_left() < 1)
							$fields['OUT OF GUESSES'] = 'You are out of guesses! You have one shot at guessing what the word is.';
						$fields['Word'] = $this->render_word($word);
						$fields['Guesses Left'] = $this->letters_left();
						return array('title' => '!hm', 'color' => '16776960', 'fields' => $fields);
					}
				} else {
					$this->reset_game();
					$fields['Result'] = 'Shoot! You did not correctly guess the word.';
					$fields['Word'] = $word;
					$fields['Play Again?'] = 'Use the command !hm';
					return array('title' => '!hm', 'color' => '16744448', 'fields' => $fields);
				}
			}
		}

	}
	
	public function render_word($theword)
	{
		$render = '';
		$wordsplit = str_split(strtoupper($theword));
		$guesses = $GLOBALS['SITE_DB']->query_select('hangman_letters', array('letter'), array('guessed' => 0));
		foreach ($wordsplit as $key => $value)
		{
			foreach ($guesses as $guess)
			{
				if ($guess['letter'] == $value)
					$wordsplit[$key] = '#';
			}
		$render .= $wordsplit[$key];
		}
		return $render;
	}
	
	public function letters_left()
	{
		$count = $GLOBALS['SITE_DB']->query_select_value_if_there('hangman_letters', 'COUNT(*)', array('guessed' => 1));
		$count = 10 - integer_format($count);
		return $count;
	}
	
	public function reset_game()
	{
		$GLOBALS['SITE_DB']->query_update('hangman_words', array('used' => 0), array('used' => 1));
		$GLOBALS['SITE_DB']->query_update('hangman_letters', array('guessed' => 0), array('guessed' => 1));
		$GLOBALS['SITE_DB']->query_delete('hangman_letters', array('letter' => '0'));
	}
	
}
