<?php
/**
* bbdkp-wowhead Link Parser v3 - NPC Extension
*
* @package bbDkp.includes
* @version $Id $
* @Copyright (c) 2008 Adam Koch
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* By: Adam "craCkpot" Koch (admin@crackpot.us) Adapted by bbdkp Team (sajaki9@gmail.com)
*
**/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class wowhead_npc extends wowhead
{
	var $lang;
	var $patterns;
	function wowhead_npc()
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
		
		if (!$result = $cache->getNPC($name, $this->lang))
		{
			// not found in cache

			$result = $this->_getNPCInfo($name);
			if (!$result)
			{
				// not found
				
				return $this->_notFound('NPC', $name);
			}
			else
			{
				// found, save it and display
				$cache->saveNPC($result);
				
				return $this->_generateHTML($result, 'npc');
			}
		}
		else
		{
			
			return $this->_generateHTML($result, 'npc');
		}
	}

	function _getNPCInfo($name)
	{

		if (trim($name) == '')
		{
			return false;
		}

		if (!is_numeric($name))
		{
			$data = $this->_read_url($name, 'npc', false);
			// get the id of the npc
			if (preg_match('#Location: /\npc=([0-9]{1,10})#s', $data, $match))
			{
				$id = $match[1];
			}
			else
			{
				$id = $this->_getIDFromSearch($name, $data);

				if (!$id) { return false; }
			}
			$npc_name = ucwords($name);
		}
		else
		{
			$data = $this->_read_url($name, 'npc', false);
			$npc_name = $this->_getNPCNameFromID($data);
			$id = $name;
		}

		return array(
			'npcid'			=>	$id,
			'name'			=>	$npc_name,
			'search_name'	=>	$name,
			'lang'			=>	$this->lang
		);
	}

	function _getIDFromSearch($name, $data)
	{
		if (trim($data) == '')
			return false;

		// the line we need to pull the info from
		$line = $this->_npcSearchLine($data);
		//new regex
		while (preg_match('#"id":([0-9]{1,10}),"location":\[[0-9]{1,9}\],"maxlevel":[0-9]{1,9},"minlevel":[0-9]{1,9},"name":"(.+?)"#s', $line, $match))
		{
			if (urldecode(addslashes(strtolower($match[2]))) == urldecode(addslashes(strtolower($name))))
			{
				// we have a match
				return $match[1];
			}
			else
			{
				// no match so replace the line to prevent a never-ending loop
				$line = str_replace($match[0], '', $line);
			}
		}

		// otherwise, return false
		return false;
	}

	function _npcSearchLine($data)
	{
		$parts = explode(chr(10), $data);

		foreach ($parts as $line)
		{
			if (strpos($line, "new Listview({template: 'npc', id: 'npcs'") !== false)
			{
				return $line;
			}
		}
		return false;
	}

	function _getNPCNameFromID($data)
	{
		while (preg_match('#<h1>(.+?)</h1>#s', $data, $match))
		{
			if (strpos($match[1], "World of Warcraft") === false) {
				return $match[1];
			}
			else
			{
				$data = str_replace($match[0], '', $data);
			}
		}
	}
	
	/**
	* Generates HTML for link
	* @access private
	**/
	function _generateHTML($info, $type, $size = '', $rank = '', $gems = '')
	{
	    $info['link'] = $this->_generateLink($info['npcid'], 'npc'); 
		return $this->_replaceWildcards($this->patterns->pattern($type), $info);
	}
	
}

?>