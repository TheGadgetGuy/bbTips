<?php
/**
* bbdkp-wowhead Link Parser v3 - Itemset Icon Extension
*
* @package bbDkp.includes
* @version $Id $
* @copyright (c) 2010 bbDkp <http://code.google.com/p/bbdkp/> 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* By: Adam "craCkpot" Koch (admin@crackpot.us) -- Adapted by bbdkp Team (sajaki9@gmail.com)
* 
* Syntax
* <code>
* [itemset {parameters}]{name or ID}[/item]
* </code>
* example usage
* <code>
* [itemset]-259[/itemset]
* [itemset]Sanctified Ymirjar Lord's Plate[/itemset]
* </code>
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class wowhead_itemset extends wowhead
{
	// variables
	var $lang;
	var $itemset = array();
	var $itemset_items = array();
	var $setid;
	var $patterns; 

	/**
	* Constructor
	* @access public
	**/
	function wowhead_itemset($argin)
	{
		global $phpEx, $config, $phpbb_root_path; 
		if (!class_exists('wowhead_patterns')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_patterns.' . $phpEx); 
        }
        $this->patterns = new wowhead_patterns();
        $this->lang = $config['bbtips_lang'];
        $this->args = $argin;
	}	

	/**
	* Parses itemset bbcode
	* @access public
	**/
	function parse($name)
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
		
		if (!$result = $cache->getItemset($name))
		{
			// not in the cache so call wowhead
			if(is_numeric($name))
			{
				$result = $this->_getItemsetByID($name); 
			}
			else 
			{
				// json lookup
				$result = $this->_getItemsetByName($name);
			}
				
			if (!$result)
			{
				// item not found 
				return $this->_notfound($this->type, $name);
			}
			else
			{   //insert
				$cache->saveItemset($this->itemset, $this->itemset_items);
				return $this->_generateHTML();
			}
			
		}
		else
		{
			// already in db
			$this->itemset = $result;
			$this->itemset_items = $cache->_getItemsetReagents($this->itemset['setid']);
			return $this->_generateHTML();
		} 
	}
	
	function _getItemsetByID($id)
	{
		if (trim($id) == '' || !is_numeric($id))
		{
			return false;
		}

		$data = $this->_read_url($id, 'itemset', false);
		
		if (trim($data) == '' || empty($data)) 
		{ 
			return false; 
		}
				
		// pull the properly formatted name
		$nameline = '';
		$parts = explode(chr(10), $data);
		foreach ($parts as $nameline)
		{	
			if (strpos($nameline, 'var g_pageInfo = {type:') !== false)
			{
				break 1;
			}
		}
		
		if (!preg_match("/name: '(.+?)'\};/", $nameline, $linematch))
		{
			return false;	
		}
		
		$this->itemset = array(
			'setid'			=>	$id,
			'name'			=>	stripslashes($linematch[1]),
			'search_name'	=>	stripslashes($linematch[1]),
			'lang'			=>	$this->lang
		);
		
		// now pull the items
		while (preg_match('#<span class="q([0-6]{1})"><a href="\/item=(.+?)">(.+?)</a></span>#s', $data, $items))
		{
			$this->itemset_items[] = array(
				'setid'		=>	$id,
				'itemid'	=>	$items[2],
				'name'		=>	stripslashes($items[3]),
				'quality'	=>	$items[1],
				'icon'		=>	'http://static.wowhead.com/images/wow/icons/small/' . $this->getItemIcon($items[2])
			);
			$data = str_replace($items[0], '', $data);
		}		
		
		if (sizeof($this->itemset) == 0 || sizeof($this->itemset_items) == 0)
			return false;
		else
			return true;	
	}
	
	function _getItemIcon($id)
	{
		$xml_data = $this->_read_url($id, 'item');
	
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
			 	
			 	$iconname = strtolower($xml->item->icon) . '.jpg';
				unset($xml);
				return $iconname; 
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
	
	
	
	/**
	* Returns the summary line we need for getting itemset items
	* @access private
	**/
	function _summaryLine($data)
	{
		$parts = explode(chr(10), $data);
		foreach ($parts as $line)
		{
			if (strpos($line, "new Summary({id: 'itemset', template: 'itemset',") !== false)
			{
				$line = substr($line, strpos($line, 'data: [{') + 6);
				$line = str_replace('});', '', $line);
				return $line;
				break;
			}
			elseif (strpos($line, "new Listview({template: 'itemset', id: 'itemsets',") !== false)
			{
				$line = substr($line, strpos($line, 'data: [{') + 6);
				$line = str_replace('});', '', $line);
				return $line;
				break;	
			}
			elseif (strpos($line, "new Listview({template: 'itemset', id: 'item-sets',") !== false)
			{
				$line = substr($line, strpos($line, 'data: [{') + 6);
				$line = str_replace('});', '', $line);
				return $line;
				break;
			}
		}
		return false;
	}
	
	function _getItemsetByName($name)
	{
		if (trim($name) == '')
		{
			return false;
		}
		
		$data = $this->_read_url($name, 'itemset', false);
		
		if (trim($data) == '' || empty($data)) 
		{ 
			return false; 
		}
				
		if (preg_match('#Location: \/itemset=([\-0-9]{1,10})#s', $data, $match))
		{
			// since it redirected to a new page, we must pull that data
			$data = $this->_read_url($match[1], 'itemset', false);

			$nameline = '';
			$parts = explode(chr(10), $data);
			foreach ($parts as $nameline)
			{	
				if (strpos($nameline, 'var g_pageInfo = {type:') !== false)
				{
					break 1;
				}
			}
			
			if (!preg_match("/name: '(.+?)'\};/", $nameline, $linematch))
			{
				return false;	
			}
			
			$this->itemset = array(
				'setid'			=>	$match[1],
				'name'			=>	stripslashes($linematch[1]),
				'search_name'	=>	$name,
				'lang'			=>	$this->lang
			);
			
			// now time to pull the items
			while (preg_match('#<span class="q([0-6]{1})"><a href="\/item=(.+?)">(.+?)</a></span>#s', $data, $items))
			{
				$this->itemset_items[] = array(
					'setid'		=>	$match[1],
					'itemid'	=>	$items[2],
					'name'		=>	stripslashes($items[3]),
					'quality'	=>	$items[1],
					'icon'		=>	'http://static.wowhead.com/images/wow/icons/small/' . $this->_getItemIcon($items[2])
				);
				$data = str_replace($items[0], '', $data);
			}
			
			if (sizeof($this->itemset) == 0 || sizeof($this->itemset_items) == 0)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		
		// get the data line
		$line = $this->_summaryLine($data);

		if (!$line)
		{
			return false;
		}
		else
		{
			// decode the json result
			if (!$json = json_decode($line, true))
			{
				return false;
			}

			foreach ($json as $itemset)
			{
				// strip the first character from the name
				$itemset['name'] = substr($itemset['name'], 1);
				
				if (stripslashes(strtolower($itemset['name'])) == stripslashes(strtolower($name)))
				{
					$this->itemset = array(
						'setid'			=>	$itemset['id'],
						'name'			=>	stripslashes($itemset['name']),
						'search_name'	=>	$name,
						'lang'			=>	$this->lang
					);
					
					foreach ($itemset['pieces'] as $piece)
					{
						$xml_data = $this->_read_url($piece, 'item');
						
						libxml_use_internal_errors(true);
						// accounts for SimpleXML not being able to handle 3 parameters if you're using PHP 5.1 or below.
						if (!$this->_allowSimpleXMLOptions())
						{
							// remove CDATA tags
							$xml_data = $this->_removeCData($xml_data);
							$xml = simplexml_load_string($xml_data, 'SimpleXMLElement');
						}
						else
						{
							$xml = simplexml_load_string($xml_data, 'SimpleXMLElement', LIBXML_NOCDATA);
						}
							
						$errors = libxml_get_errors();
						if (empty($errors))
						{
							 	libxml_clear_errors();
							 	
							 	if(isset($xml->error))
							 	{
							 		return false;
							 	}
							 	
								$this->itemset_items[] = array(
									'setid'		=>	$itemset['id'],
									'itemid'	=>	$piece,
									'name'		=>	(string)$xml->item->name,
									'quality'	=>	(int)$xml->item->quality['id'],
									'icon'		=>	'http://static.wowhead.com/images/wow/icons/small/' . strtolower((string)$xml->item->icon) . '.jpg'
								);
								unset($xml_data, $xml);
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
					
					if (sizeof($this->itemset) == 0 || sizeof($this->itemset_items) == 0)
					{
						return false;
					}
					else
					{
						return true;						
					}
				}
			}
		}
	}
	
	
	/**
	* Generates HTML
	* @access private
	**/
	function _generateHTML()
	{
		// generate item HTML first
		$item_html = ''; $set_html = $this->patterns->pattern('itemset');

		foreach ($this->itemset_items as $item)
		{
			$patt = $this->patterns->pattern('itemset_item');
			$search = array(
				'{link}'	=>	$this->_generateLink($item['itemid'], 'item'),
				'{name}'	=>	stripslashes($item['name']),
				'{qid}'		=>	$item['quality'],
				'{icon}'	=>	$item['icon']
			);
			foreach ($search as $key => $value)
			{
				$patt = str_replace($key, $value, $patt);
			}
			$item_html .= $patt;
		}

		// now generate everything
		$set_html = str_replace('{link}', $this->_generateLink($this->itemset['setid'], 'itemset'), $set_html);
		$set_html = str_replace('{name}', $this->itemset['name'], $set_html);
		$set_html = str_replace('{items}', $item_html, $set_html);

		return $set_html;
	}

	
}
?>