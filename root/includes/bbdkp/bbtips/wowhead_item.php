<?php
/**
* bbdkp-wowhead Link Parser v3 - Link Parser v3 - Item Extension
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

class wowhead_item extends wowhead
{
	var $lang;
	var $patterns;

	function wowhead_item()
	{
		global $phpEx, $phpbb_root_path; 
		
		if (!class_exists('wowhead_patterns')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_patterns.' . $phpEx); 
        }
        $this->patterns = new wowhead_patterns();
	}

	/**
	* Parses Items
	* @access public
	**/
	function parse($name, $args = array())
	{
		global $config; 
		global $phpEx, $phpbb_root_path; 

		if (trim($name) == '')
		{
			return false;
		}
		
	    if (!class_exists('wowhead_cache')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_cache.' . $phpEx); 
        }
		$cache = new wowhead_cache();

		$this->lang = $config['bbtips_lang'];

		// check if its already in the cache
		if (!$result = $cache->getObject($name, 'item', $this->lang))
		{
			// not in the cache

			if (!$result = $this->_getItemInfo($name))
			{
				// item not found
				
				return $this->_notfound('item', $name);
			}
			else
			{
				$cache->saveObject($result);	// save it to cache
				
				if (array_key_exists('gems', $args) || array_key_exists('enchant', $args))
				{
					$enhance = $this->_buildEnhancement($args);
					return $this->_generateHTML($result, 'item', '', '', $enhance);
				}
				else
				{
					return $this->_generateHTML($result, 'item');
				}
			}
		}
		else
		{
			

			if (array_key_exists('gems', $args) || array_key_exists('enchant', $args))
			{
				$enhance = $this->_buildEnhancement($args);
				return $this->_generateHTML($result, 'item', '', '', $enhance);
			}
			else
			{
				return $this->_generateHTML($result, 'item');
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
			
     	if (trim($gems) != '')
		{
			$info['gems'] = $gems;
			return $this->_replaceWildcards($this->patterns->pattern('item_gems'), $info);
		}
		else
		{
			return $this->_replaceWildcards($this->patterns->pattern($type), $info);
		}
	
		
	}

	/**
	* Queries Wowhead for Item Info
	* @access private
	**/
	function _getItemInfo($name)
	{
		if (trim($name) == '')
		{
			return false;
		}

		// gets the XML data from wowhead and remove CDATA tags
		$data = $this->_read_url($name);

		if (trim($data) == '' || empty($data)) { return false; }

		if ($this->_useSimpleXML())
		{
			// accounts for SimpleXML not being able to handle 3 parameters if you're using PHP 5.1 or below.
			if (!$this->_allowSimpleXMLOptions())
			{
				$data = $this->_removeCData($data);
				$xml = simplexml_load_string($data, 'SimpleXMLElement');
			}
			else
			{
				$xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
			}

			if ($xml->error == '')
			{
				// this will hold our results
				return array(
					'name'			=>	(string)$xml->item->name,
					'search_name'	=>	$name,
					'itemid'		=>	(string)$xml->item['id'],
					'quality'		=>	(string)$xml->item->quality['id'],
					'type'			=>	'item',
					'lang'			=>	$this->lang
				);
			}
			else
			{
				return false;
			}

			unset($xml);
		}
		
		
	}
}
?>