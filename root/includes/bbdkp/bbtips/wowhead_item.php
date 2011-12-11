<?php
/**
* bbdkp-wowhead Link Parser v3 - Link Parser v3 - Item Extension
* @package bbDkp.includes
* @version $Id $
* @Copyright bbDKP
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* 
* Syntax
* [item {parameters}]{name or ID}[/item]
* [itemico {parameters}]{name or ID}[/itemico]
* [itemdkp {parameters}]{name or ID}[/itemdkp]
* [ptritem {parameters}]{name or ID}[/ptritem]
* [ptritemico {parameters}]{name or ID}[/ptritemico]
* [ptritemdkp {parameters}]{name or ID}[/ptritemdkp]
* 
* parameters can be gems or enchant
* itemico has extra size parameter
* 
* example usage
* [item gems="40133" enchant="3825"]50468[/item]
* [itemico gems="40133" enchant="3825"]50468[/item]
* [itemico gems="40133" enchant="3825" size=small]Ardent Guard[/itemico]
* [itemico gems="40133" enchant="3825" size=medium]Ardent Guard[/itemico]
* [itemico gems="40133" enchant="3825" size=large]Ardent Guard[/itemico]
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
 * handles creation of item tooltips
 *
 */
class wowhead_item extends wowhead
{
	public $lang;
	public $patterns;
	public $type; 
	public $size;
	public $itemid;
	public $name;
	public $search_name;
	public $icon;
	public $quality;
	public $args = array();

	/*
	 * $bbcode : either 'item' or 'itemico' or 'itemdkp'
	 */
	public function wowhead_item($bbcode, $argin = array())
	{
		global $phpEx, $phpbb_root_path, $config; 
		
		if (!class_exists('wowhead_patterns')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_patterns.' . $phpEx); 
        }
        $this->patterns = new wowhead_patterns();
		$this->type = $bbcode;
		$this->args = $argin;
		$this->lang = $config['bbtips_lang'];
		$this->size = (!array_key_exists('size', $this->args)) ? 'medium' : $this->args['size'];
	}

	/**
	* Parses Items
	*
	* @access public
	**/
	public function parse($name)
	{
		global $config, $phpEx, $phpbb_root_path; 

		if (trim($name) == '')
		{
			return false;
		}
		
	    if (!class_exists('wowhead_cache')) 
        {
          	   require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_cache.' . $phpEx); 
        }
		$cache = new wowhead_cache();

		// check if its already in the cache
		if (!$result = $cache->getObject($name, $this->type, $this->lang, '', $this->size))
		{
			//not in db so call wowhead
			if(is_numeric($name))
			{
				//xmlsearch
				$result = $this->_getItembyID($name);
			}
			else 
			{
				//json search
				$result = $this->_getItemByName($name);
				if (!$result)
				{
					//try without enchant
					$pattern  = "( of the)[ A-Za-z0123456789]";
					$unenchanted = split($pattern, $name);
					if ($unenchanted)
					{
						$result = $this->_getItemByName($unenchanted[0]);
					}
				}
			}
			
			
			if (!$result)
			{
				// item not found 
				return $this->_notfound($this->type, $name);
			}
			
			
			else
			{   //insert 
				$cache->saveObject($result); 
				if (array_key_exists('gems', $this->args) || array_key_exists('enchant', $this->args))
				{
					$enhance = $this->_buildEnhancement($this->args);
					return $this->_generateHTML($result, $enhance);
				}
				else
				{
					return $this->_generateHTML($result);
				}
			}
		}
		else
		{
			// already in db
			if (array_key_exists('gems', $this->args) || array_key_exists('enchant', $this->args))
			{
				$enhance = $this->_buildEnhancement($this->args);
				return $this->_generateHTML($result, $enhance);
			}
			else
			{
				return $this->_generateHTML($result);
			}
		}
	}
	
	/**
	* Builds Item Enhancement String
	* @access private
	**/
	private function _buildEnhancement($args)
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
	* Generates HTML for link
	* @access private
	**/
	private function _generateHTML($info, $gems = '')
	{
		
		$info['link'] = $this->_generateLink($info['itemid'], $this->type);
		
		if (trim($gems) != '')
		{
			$info['gems'] = $gems;
			if ($this->type =='item' or $this->type =='itemdkp' or $this->type =='ptritem')
			{
			    return $this->_replaceWildcards($this->patterns->pattern('item_gems'), $info);
			}
            elseif  ($this->type =='itemico' or $this->type =='ptritemico')
            {
			    return $this->_replaceWildcards($this->patterns->pattern('icon_'.$this->size.'_gems'), $info);
            }
		}
		else
		{
			// no gems
			if ($this->type =='item' or $this->type =='itemdkp' or $this->type =='ptritem')
			{
				return $this->_replaceWildcards($this->patterns->pattern('item'), $info);
			}
			elseif  ($this->type =='itemico' or $this->type =='ptritemico')
			{
				return $this->_replaceWildcards($this->patterns->pattern('icon_'.$this->size), $info);
			}
		}
	}

	/**
	* Queries Wowhead for Item id
	* @access private
	**/
	private function _getItembyID($id, $search='')
	{
		
		$id = (int) $id;
		if ($id == 0)
		{
			return false;
		}
		
		$this->make_url($id, 'craftable');
		$data = $this->gethtml($id, 'craftable');

		if (trim($data) == '' || empty($data)) 
		{ 
			return false; 
		}
		
		//if wowhead is down
		if(preg_match('#HTTP/1.1 503 Service Unavailable#s',$data,$match))
		{
			return $this->_notFound('Item', $id);
		}
		
		if ($this->_useSimpleXML())
		{
			// switch libxml error handler on
			libxml_use_internal_errors(true);
			// accounts for SimpleXML not being able to handle 3 parameters if you're using PHP 5.1 or below.
			if (!$this->_allowSimpleXMLOptions())
			{
				// remove CDATA tags
				$data = $this->_removeCData($data);
				$xml = simplexml_load_string($data, 'SimpleXMLElement');
			}
			else
			{
				$xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
			}
			
			$errors = libxml_get_errors();
			if (empty($errors))
			 {
			 	libxml_clear_errors();
			 	
			 	if(isset($xml->error))
			 	{
			 		return false;
			 	}
			 	
			 	$this->name = (string) $xml->item->name;
			 	$this->search_name = (trim($search) == '') ? $id : $search;
			 	$this->itemid = (string)$xml->item['id'];
			 	$this->quality = (string)$xml->item->quality['id'];
			 	$this->icon = 'http://static.wowhead.com/images/wow/icons/' . $this->size . '/' . strtolower($xml->item->icon) . '.jpg'; 
			 	
			 	// will hold return
				$item = array(
					'name'			=>	$this->name, 
					'search_name'	=>	$this->search_name, 
					'itemid'		=>	$this->itemid,
					'icon'			=>	$this->icon, 
					'icon_size'		=>	$this->size,
					'quality'		=>	$this->quality, 
					'type'			=>	$this->type,
					'lang'			=>	$this->lang
				);
				unset($xml);
				return $item; 
				
			 }
			else
			{
				// set error handler off - to free memory
				unset($xml);
				unset($errors); 
				libxml_clear_errors();
				return false;
			}
		}
		else 
		{
			return $this->_notFound('Item', $item);
		}
	}
	
	private function _getItemByName($name)
	{
		if (trim($name) == '')
		{
			return false;
		}

		$this->make_url($name, 'item');
		$data = $this->gethtml($name, 'item');
				
		if (!$data)
		{
			return false;
		}
		
		// for searches with only one result (aka redirect header)
		// example http://www.wowhead.com/search?q=Blighted Leggings
		if (preg_match('#Location: \/item=([0-9]{1,10})#s', $data, $match))
		{
			return $this->_getItembyID($match[1], $name);
		}
		
		// lots of results, so read the 
		$line = $this->_itemLine($data);
		
		if (!$line)
		{
			return false;
		}
		else
		{
			if (!$json = json_decode($line, true))
			{
				return false;
			}
				
			foreach ($json as $item)
			{
				// strip the first character, if necessary
				if (is_numeric(substr($item['name'], 0, 1)))
				{
					$item['name'] = substr($item['name'], 1);
				}
				
				if (strtolower(stripslashes($item['name'])) == strtolower(stripslashes($name)))
				{
					return $this->_getItembyID($item['id'], $name);
				}
			}
			return false;
		}
	}
	
	private function _itemLine($data)
	{
		$parts = explode(chr(10), $data);
		foreach ($parts as $line)
		{
			if (strpos($line, "new Listview({template: 'item', id: 'items',") !== false)
			{
				// clean the line up to make it valid JSON
				$line = substr($line, strpos($line, 'data: [{') + 6);
				$line = str_replace('});', '', $line);
				return $line;	
			}
		}
		return false;
	}
	
	
}
?>