<?php
/**
* bbdkp-wowhead Link Parser v3 - Quest Extension
*
* @package bbDkp.includes
* @version $Id $
* @Copyright (c) 2008 Adam Koch
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* By: Adam "craCkpot" Koch (admin@crackpot.us) -- Adapted by bbdkp Team (sajaki9@gmail.com)
*
**/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class wowhead_quest extends wowhead
{
	var $lang;
	var $patterns;
	function wowhead_quest()
	{
		global $phpEx, $phpbb_root_path; 
		
		if (!class_exists('wowhead_patterns')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_patterns.' . $phpEx); 
        }
        $this->patterns = new wowhead_patterns();
	}

	function parse($name, $args = array())
	{
		
		if (trim($name) == '')
		{
		    return false;
		}

		global $config; 
		global $phpEx, $phpbb_root_path; 

		$this->lang = $config['bbtips_lang'];
		
		if (!class_exists('wowhead_cache')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_cache.' . $phpEx); 
        }
		$cache = new wowhead_cache();

		if (!$result = $cache->getObject($name, 'quest', $this->lang))
		{
				// not in cache
			if (is_numeric($name))
			{
				// by id
				$result = $this->_getQuestByID($name);
			}
			else
			{
				// by name
				$result = $this->_getQuestByName($name);
			}

			if (!$result)
			{
				// not found
				
				return $this->_notfound('quest', $name);
			}
			else
			{
				$cache->saveObject($result);
				
				return $this->_generateHTML($result, 'quest');
			}
		}
		else
		{
			// found in cache
			
			return $this->_generateHTML($result, 'quest');
		}
	}

	/**
	* Generates HTML for link
	* @access private
	**/
	function _generateHTML($info, $type, $size = '', $rank = '', $gems = '')
	{
	    $info['link'] = $this->_generateLink($info['itemid'], $type);
	    
	    $html = $this->_replaceWildcards($this->patterns->pattern($type), $info);
	    
	    return $html; 
	}
	
	
	
	/**
	* Queries Wowhead for Quest info by ID
	* @access private
	**/
	private function _getQuestByID($id)
	{
		if (!is_numeric($id))
		{
				return false;
		}

		$data = $this->_read_url($id, 'quest', false);

		// wowhead doesn't have the info
		if ($data == '$WowheadPower.registerQuest(' . $id . ', {});')
		{
			return false;
		}
		else
		{
			// gets the quest's name
			if (preg_match('#<b class="q">(.+?)</b>#s', $data, $match))
			{
				return array(
					'name'			=>	stripslashes($match[1]),
					'itemid'		=>	$id,
					'search_name'	=>	$id,
					'type'			=>	'quest',
					'lang'			=> $this->lang
				);
			}
			else
			{
				return false;
			}
		}
	}

	
	/**
	* Uses Wowhead's search to find our info
	* @access private
	**/
	public function _findQuestBySearch($name)
	{
		$data = $this->_read_url($name, $type, false);

		if (preg_match('#Location: /\?' . $type . '=(.+?)\n#s', $data, $match))
		{
			// for searches with only one result
			return array(1 => $match[1], 2 => ucwords(strtolower($name)));
		}
		else
		{
			$the_line = $data;
			$pattern = '#<a href="/quest=([0-9]{1,10})">(.+?)</a>#s';

			// then we'll use preg_match to find any matches
			while (preg_match($pattern, $the_line, $match))
			{
				// do we have a match?
				if (stripslashes(strtolower($match[2])) == strtolower($name))
				{
					return $match;
					break;
				}
				else
				{
					// remove the found entry to prevent a never ending loop
					$the_line = str_replace($match[0], '', $the_line);
				}
			}

		}
		return false;
	}
	
	
	/**
	* Queries Wowhead for Quest by Name
	* @access private
	**/
	private function _getQuestByName($name)
	{
		if (trim($name) == '')
		{
		    return false;
		}
		
		$query = $this->_findQuestBySearch($name);

		if ($query != false)
		{
			return array(
				'name'			=>	stripslashes($query[2]),
				'search_name'	=>	$name,
				'itemid'		=>	$query[1],
				'type'			=>	'quest',
				'lang'			=>  $this->lang
			);
		}
		else
		{
			return false;
		}
	}
	
	
}
?>