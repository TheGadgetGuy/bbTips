<?php
/**
* bbdkp-wowhead Achievement Extension
*
* @package bbDkp.includes
* @version $Id $
* @copyright 2010 bbdkp <http://code.google.com/p/bbdkp/>
* @author: Adam "craCkpot" Koch (admin@crackpot.us) -- 
* @author: Sajaki (sajaki9@gmail.com)
*
**/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class wowhead_achievement extends wowhead
{
	var $lang;
	var $patterns;
	var $args; 

	function wowhead_achievement($arguments = array())
	{
		global $config, $phpEx, $phpbb_root_path; 
	    
        if (!class_exists('wowhead_patterns')) 
        {
			require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_patterns.' . $phpEx); 
        }
        $this->patterns = new wowhead_patterns();
        $this->args = $arguments;
		$this->lang = $config['bbtips_lang'];
	}

	function parse($name)
	{
	    global $phpbb_root_path, $phpEx;
	    
		if (trim($name) == '')
		{
			return false;
		}
		
		if (!class_exists('wowhead_cache')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_cache.' . $phpEx); 
        }
		$cache = new wowhead_cache();


		if (!$result = $cache->getObject($name, 'achievement', $this->lang))
		{
			// not in cache
			if (is_numeric($name))
			{
				$result = $this->_getAchievementByID($name);
			}
			else
			{
				$result = $this->_getAchievementByName($name);
			}

			if (!$result)
			{
				// not found
				
				return $this->_notfound('achievement', $name);
			}
			else
			{
				$cache->saveObject($result);
				
				return $this->_generateHTML($result, 'achievement');
			}
		}
		else
		{
			
			return $this->_generateHTML($result, 'achievement');
		}
	}

	/**
	* Queries Wowhead for Achievement info by ID
	* @acess private
	**/
	private function _getAchievementByID($id)
	{
		if (!is_numeric($id))
		{
		    return false;
		}

		$data = $this->_read_url($id, 'achievement', false);

		if ($data == '$WowheadPower.registerAchievement(1337, 25, {});')
		{
			return false;
		}
		else
		{
			if (preg_match('#<b class="q">(.+?)</b>#s', $data, $match))
			{
				return array(
						'name'			=>	stripslashes($match[1]),
						'itemid'		=>	$id,
						'search_name'	=>	$id,
						'type'			=>	'achievement',
						'lang'			=>	$this->lang

				);
			}
			else
			{
				return false;
			}
		}
	}

	/**
	* Queries Wowhead for Achievement by Name
	* @access private
	**/
	private function _getAchievementByName($name)
	{
		if (trim($name) == '')
		{
			return false;
		}

		$data = $this->_read_url($name, 'achievement', false);

		if (preg_match('#Location: /\?achievement=(.+?)\n#s', $data, $match))
		{
			// result returns a redirection header (aka only one result)
			// so we can get the information we need from there
			return array(
					'name'			=>	stripslashes(ucwords(strtolower($name))),
					'search_name'	=>	$name,
					'itemid'		=>	$match[1],
					'type'			=>	'achievement',
					'lang'			=>	$this->lang
			);
		}
		else
		{
			// result returns wowhead search page, now get results -> get to CDATA
			$parts = explode(chr(10), $data);
			foreach ($parts as $line)
			{
				if (strpos($line, "new Listview({template: 'achievement', id: 'achievements'") !== false)
				{
					$the_line = $line; 
					break 1;
				}
			}

			if (!$the_line)
			{
				return false;
			}
			
			while (preg_match('#"id":([0-9]{1,10}),"name":"(.+?)"#s', $the_line, $match))
			{
				
				if (strtolower($match[2]) == addslashes(strtolower($name)))
				{
					return array(
						'name'			=>	$match[2],
						'search_name'	=>	$name,
						'itemid'		=>	$match[1],
						'type'			=>	'achievement',
						'lang'			=>	$this->lang
					);
				}
				else
				{
					$the_line = str_replace($match[0], '', $the_line);
				}
			}
		}
	}
	
	/**
	* Generates HTML for link
	* @access private
	**/
	function _generateHTML($info, $type, $size = '', $rank = '', $gems = '')
	{
	    $info['link'] = $this->_generateLink($info['itemid'], $type);
		return $this->_replaceWildcards($this->patterns->pattern($type), $info);
	}
	
	
}
?>