<?php
/**
* bbdkp-wowhead Link Parser v3 - Quest Extension
*
* @package bbDkp.includes
* @Copyright bbDKP
* @version $Id $
* @author sajaki9@gmail.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
	public $lang;
	public $patterns;
	private $args = array();
		
	function wowhead_quest($arguments = array())
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
	* Queries Wowhead for Quest by Name
	* @access private
	**/
	private function _getQuestByName($name)
	{
		if (trim($name) == '')
		{
		    return false;
		}
		
		$html = $this->_read_url($name, 'quest', false);
		
		if (!$html)
		{
			return false;
		}
		
		// make sure it didn't redirect
		if (preg_match('#Location: \/quest=([0-9]{1,10})#s', $html, $match))
		{
			$quest =  array(
				'name'			=>	ucwords(strtolower($name)),
				'search_name'	=>	$name,
				'type'			=>	'quest',
				'itemid'		=>	$match[1],
				'lang'			=>	$this->lang
			);	
			
			return $quest; 
		}
		
		// get the JSON line from the data
		$line = $this->_questLine($html);
		if (!$line)
		{
			return false;
		}
		else
		{
			// decode the json
			if (!$json = json_decode($line, true))
			{
				return false;
			}
			
			foreach ($json as $quests)
			{
				if (stripslashes(strtolower($quests['name'])) == stripslashes(strtolower($name)))
				{
					$quest = array(
						'name'			=>	$quests['name'],
						'search_name'	=>	$name,
						'type'			=>	'quest',
						'itemid'		=>	$quests['id'],
						'lang'			=>	$this->lang
					);
					return $quest; 
				}
			}
			
			return false;
		}
			
	}
	
	/*
	 * loop lines and extract from json 
	 */
	private function _questLine($data)
	{
		$parts = explode(chr(10), $data);
		foreach ($parts as $line)
		{
			if (strpos($line, "new Listview({template: 'quest', id: 'quests',") !== false)
			{
				$line = substr($line, strpos($line, 'data: [{') + 6);
				$line = str_replace('});', '', $line);
				return $line;
				break;
			}
		}
		
		return false;
	}
	
	
	
	
}
?>