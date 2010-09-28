<?php
/**
* bbdkp-wowhead Link Parser v3 
*
* @package bbDkp.includes
* @version $Id$
* @Copyright (c) 2008 Adam Koch
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* Wowhead (wowhead.com) Link Parser v3 - Spell Extension
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
	 
/**
* Wowhead Base Class
* @package wowhead
**/
class wowhead
{
	var $patterns; 
	
	function wowhead()
	{
		global $phpEx, $phpbb_root_path; 
		
		if (!class_exists('wowhead_patterns')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_patterns.' . $phpEx); 
        }
        $this->patterns = new wowhead_patterns();
		
	}
	
	/**
	* Attempts to read URL and return content
	* @access private
	* 
	* @param $url
	* @param $type default 'item'
	* @param $headers
	* 
	**/
	function _read_url($url, $type = 'item', $headers = true)
	{
		// build the url depending on bbcode
		switch ($type)
		{
			case 'npc':
			case 'itemset':
				if(is_numeric($url))
				{
					//parse page directly
					$built_url = $this->_getDomain() . '/' . $type . '=' . $url; 
				}
				else 
				{
					//use search and parse page
					$built_url = $this->_getDomain() . '/search?q=' . $this->_convert_string($url);
				}
				$html_data = bbDkp_Admin::read_php($built_url, 1, 0 );
				break;
			case 'spell':
			case 'quest':  
			case 'achievement':	
				if(is_numeric($url))
				{
					//parse page directly
					$built_url = $this->_getDomain() . '/' . $type . '=' . $url . '&power'; 
				}
				else 
				{
					//use search and parse page
					$built_url = $this->_getDomain() . '/search?q=' . $this->_convert_string($url);
				}
				$html_data = bbDkp_Admin::read_php($built_url, 1, 0 );
				break;
			case 'item':      
		    case 'itemdkp':   
			case 'itemico':   
			case 'craftable':
			default:
				//xml
				$built_url = $this->_getDomain() . '/item=' . $this->_convert_string($url) . '&xml';
				$html_data = bbDkp_Admin::read_php($built_url, 0, 0 );
				break;
		}
		return $html_data;
	
	}

	/**
	* Gets Gem Info
	* @access private
	**/
	function _getGemInfo($name, $itemid, $slot)
	{
		if (trim($name) == '')
			return false;

		$data = $this->_read_url($name);

		if (empty($data)) { return false; }

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
					'gemid'			=>	(string)$xml->item['id'],
					'itemid'		=>	$itemid,
					'slot'			=>	$slot
				);
			}
			else
			{
				return false;
			}

			unset($xml);
		}
	}
	
	/**
	* Strips Headers
	* @access private
	**/
	function _strip_headers($data)
	{
		// split the string
		$chunks = explode(chr(10), $data);

		// return the last index in the array, aka our xml
		return $chunks[sizeof($chunks) - 1];
	}

	/**
	* Cleans HTML for passing to Wowhead
	* @access private
	**/
	function _cleanHTML($string)
	{
	    if (function_exists("mb_convert_encoding"))
	        $string = mb_convert_encoding($string, "ISO-8859-1", "HTML-ENTITIES");
	    else
	    {
	       $conv_table = get_html_translation_table(HTML_ENTITIES);
	       $conv_table = array_flip($conv_table);
	       $string = strtr ($string, $conv_table);
	       $string = preg_replace('/&#(\d+);/me', "chr('\\1')", $string);
	    }
	    return ($string);
	}

	/**
	* Encodes the string in UTF-8 if it already isn't
	* @access private
	**/
	function _convert_string($str)
	{
		// convert to utf8, if necessary
		if (!$this->_is_utf8($str))
		{
			$str = utf8_encode($str);
		}

		// clean up the html
		$str = $this->_cleanHTML($str);

		// return the url encoded string
		return urlencode($str);
	}

	/**
	* Returns true if the $string is UTF-8, false otherwise.
	* @access private
	**/
	function _is_utf8($string) {
		// From http://w3.org/International/questions/qa-forms-utf-8.html
		return preg_match('%^(?:
			[\x09\x0A\x0D\x20-\x7E]            # ASCII
			| [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
			|  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
			| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
			|  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
			|  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
			| [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
			|  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
		)*$%xs', $string);
	}

	/**
	* Gets the proper domain for the language selected
	* @access private
	**/
	function _getDomain()
	{
		if ($this->lang == 'en')
			return 'http://www.wowhead.com';
		else
			return 'http://' . strtolower($this->lang) . '.wowhead.com';

		return 'http://www.wowhead.com';
	}

	/**
	* Returns the link to the spell/quest
	* @access private
	**/
	function _generateLink($id, $type)
	{
		if ($type == 'itemico' || $type == 'item' || $type == 'itemdkp')
		{
			return $this->_getDomain() . '/item=' . $id;
		}
		else
		{
			return $this->_getDomain() . '/' . $type . '=' . $id;
		}
	}

	/**
	* Checks if SimpleXML can accept 3 parameters
	* @access private
	**/
	function _allowSimpleXMLOptions()
	{
		$parts = explode('.', phpversion());
		return ($parts[0] == 5 && $parts[1] >= 1) ? true : false;
	}

	/**
	* Determines if we can use SimpleXML
	* @access private
	**/
	function _useSimpleXML()
	{
		$parts = explode('.', phpversion());
		return ($parts[0] == 5) ? true : false;
	}

	/**
	* Called when object isn't found
	* @access private
	**/
	function _notFound($type, $name)
	{
		global $user; 
		$user->add_lang ( array ('mods/dkp_tooltips' ));
		return '<span class="notfound">[' . ucwords($type) . ' "' . $name  . '" ' . $user->lang['ITEMNOTFOUND']  . ']</span>';
	}

	/**
	* Returns the specific line we need
	* @access private
	**/
	function _abilityLine($data, $name)
	{
		$parts = explode(chr(10), $data);

		foreach ($parts as $line)
		{
			if (strpos($line, "new Listview({template: 'spell', id: 'abilities'") !== false && strpos(strtolower($line), strtolower(addslashes($name))) !== false)
				return array(
					'type'	=>	'ability',
					'line'	=>	$line
				);
			elseif (strpos($line, "new Listview({template: 'spell', id: 'talents'") !== false && strpos(strtolower($line), strtolower(addslashes($name))) !== false)
				return array(
					'type'	=>	'talent',
					'line'	=> 	$line
				);
			elseif (strpos($line, "new Listview({template: 'spell', id: 'recipes'") !== false && strpos(strtolower($line), strtolower(addslashes($name))) !== false)
				return array(
					'type'	=>	'recipe',
					'line'	=>	$line
				);
			elseif (strpos($line, "new Listview({template: 'spell', id: 'uncategorized-spells'") !== false && strpos(strtolower($line), strtolower(addslashes($name))) !== false)
				return array(
					'type'	=>	'ability',
					'line'	=>	$line
				);
		}

		return false;
	}

	/**
	* Returns the specific line we need for achievements
	* @access private
	**/
	function _achievementLine($data)
	{
		$parts = explode(chr(10), $data);	// split by line breaks

		foreach ($parts as $line)
		{
			if (strpos($line, "new Listview({template: 'achievement', id: 'achievements'") !== false)
				return $line;
		}
		return false;
	}

	/**
	* Replaces wildcards from patterns
	* @access private
	**/
	function _replaceWildcards($in, $info)
	{
		$wildcards = array();

		// build our wildcard array
		if (array_key_exists('link', $info))
			$wildcards['{link}'] = $info['link'];

		if (array_key_exists('realm', $info))
			$wildcards['{realm}'] = $info['realm'];

		if (array_key_exists('region', $info))
			$wildcards['{region}'] = $info['region'];

		if (array_key_exists('icons', $info))
			$wildcards['{icons}'] = $info['icons'];

		if (array_key_exists('name', $info))
			$wildcards['{name}'] = stripslashes($info['name']);

		if (array_key_exists('quality', $info))
			$wildcards['{qid}'] = $info['quality'];

		if (array_key_exists('rank', $info))
			$wildcards['{rank}'] = $info['rank'];

		if (array_key_exists('icon', $info))
			$wildcards['{icon}'] = $info['icon'];

		if (array_key_exists('class', $info))
			$wildcards['{class}'] = $info['class'];

		if (array_key_exists('gems', $info))
			$wildcards['{gems}'] = $info['gems'];

		if (array_key_exists('tooltip', $info))
			$wildcards['{tooltip}'] = $info['tooltip'];

		if (array_key_exists('npcid', $info))
			$wildcards['{npcid}'] = $info['npcid'];

		foreach ($wildcards as $key => $value)
		{
			$in = str_replace($key, stripslashes($value), $in);
		}

		return $in;
	}

	/**
	* Builds Item Enhancement String
	* @access private
	**/
	function _buildEnhancement($args)
	{
		if (!is_array($args) || sizeof($args) == 0)
			return false;

		if (array_key_exists('gems', $args))
		{
			$gem_args = 'gems=' . str_replace(',', ':', $args['gems']);
		}

		if (array_key_exists('enchant', $args))
		{
			$enchant_args = 'ench=' . $args['enchant'];
		}

		if (!empty($gem_args) && !empty($enchant_args))
		{
			return $enchant_args . '&amp;' . $gem_args;
		}
		elseif (!empty($enchant_args))
		{
			return $enchant_args;
		}
		elseif (!empty($gem_args))
		{
			return $gem_args;
		}

		return false;
	}


	/**
	* Strips out any apostrophes to prevent any display problems
	* @access private
	**/
	function _strip_apos($in)
	{
		return str_replace("'", "", $in);
	}
	
	/****
	 * 
	 * if the user is using php 5.1 then strip CDATA from xml
	 */
	function _removeCData($xml) 
	{
	    $new_xml = NULL;
	    preg_match_all("/\<\!\[CDATA \[(.*)\]\]\>/U", $xml, $args);
	
	    if (is_array($args)) {
	        if (isset($args[0]) && isset($args[1])) 
	        {
	            $new_xml = $xml;
	            for ($i=0; $i<count($args[0]); $i++) 
	            {
	                $old_text = $args[0][$i];
	                $new_text = htmlspecialchars($args[1][$i]);
	                $new_xml = str_replace($old_text, $new_text, $new_xml);
	            }
	        }
	    }
	
	    return $new_xml;
	}
	
}
?>