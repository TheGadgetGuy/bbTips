<?php
/**
* bbdkp-wowhead NPC
* @package bbDkp.includes
* @author sajaki9@gmail.com
* @version $Id $
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @Copyright bbDKP
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
	var $args; 
	
	function wowhead_npc($arguments = array())
	{
		global $phpEx, $config, $phpbb_root_path; 
		
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
		if (trim($name) == '')
		{
			return false;
		}
		
		global $config, $phpEx, $phpbb_root_path; 
	
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
			if (preg_match('#Location: /npc=([0-9]{1,10})#s', $data, $match))
			{
				return array(
					'npcid'			=>	$match[1],
					'name'			=>	ucwords(strtolower($name)),
					'search_name'	=>	$name,
					'lang'			=>	$this->lang
				);	
			}
			else
			{
				$npc = $this->_getIDFromSearch($name, $data);
				if (!$npc) 
				{
					return false; 
				}
				else 
				{
					return $npc; 
				}
				
			}
		}
		else
		{
			$data = $this->_read_url($name, 'npc', false);
			$npc_name = $this->_getNPCNameFromID($data);
			return array(
				'npcid'			=>	$name,
				'name'			=>	$npc_name,
				'search_name'	=>	$name,
				'lang'			=>	$this->lang
			);
			
			
			
		}

	}

	function _getIDFromSearch($name, $data)
	{
		if (trim($data) == '')
		{
			return false;
		}

		// the line we need to pull the info from
		$line = '';
		$parts = explode(chr(10), $data);
		foreach ($parts as $line)
		{
			if (strpos($line, "new Listview({template: 'npc', id: 'npcs',") !== false)
			{
				$line = substr($line, strpos($line, 'data: [{') + 6);
				$line = str_replace('});', '', $line);
				break 1;	
			}
		}
		
		if ($line == '')
		{
			return false;	
		}
		
		// json decode
		if (!$json = json_decode($line, true))
		{
			return false;
		}
			
		foreach ($json as $npc)
		{
			if (stripslashes(strtolower($npc['name'])) == stripslashes(strtolower($name)))
			{
				return array(
					'npcid'			=>	$npc['id'],
					'name'			=>	stripslashes($npc['name']),
					'search_name'	=>	$name,
					'lang'			=>	$this->lang
				);
			}
		}
		// otherwise, return false
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