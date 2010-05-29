<?php
/**
* bbdkp-wowhead Link Parser v3 - Itemset Icon Extension
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
	function wowhead_itemset()
	{
		global $phpEx, $phpbb_root_path; 
		if (!class_exists('wowhead_patterns')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_patterns.' . $phpEx); 
        }
        $this->patterns = new wowhead_patterns();

	}

	/**
	* Parses itemset bbcode
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
		
		$this->lang = $config['bbtips_lang'];
		
		if (!class_exists('wowhead_cache')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_cache.' . $phpEx); 
        }
		$cache = new wowhead_cache();
		
		if (!$result = $cache->getItemset($name, $this->lang))
		{
			// not in the cache
			
				//search on name
				$data = $this->_read_url($name, 'itemset', false);
				
				if (!preg_match('#Location: /\?itemset=([\-0-9]{1,10})#s', $data, $match))
				{
					// didn't find the redirect header, -> find correct json from search 
					$summary = $this->_summaryLine($data);
					// look in summary json for this pattern, return $match 
					if (!preg_match('#"id":([0-9]{1,9}),"maxlevel":[0-9]{1,9},"minlevel":[0-9]{1,9},"name":"(.+?)"#s', $summary, $match))
					{
						//no pattern found
						return $this->_notFound('Itemset', $name);
					}
				}
				
				// is the itemset the one were looking for? 
				if (!strpos($match[2],$name))
				{
					return $this->_notFound('Itemset', $name);
				}

				// we now have the set id, and can query wowhead for the info we need
				$this->setid = $match[1];
				$data = $this->_read_url($this->setid, 'itemset', false);

				$this->itemset['setid'] = $this->setid;
				$this->itemset['name'] = ucwords($name);
				$this->itemset['search_name'] = $name;
				$this->itemset['lang'] = $this->lang;

				// find items in set
				$summary = $this->_summaryLine($data);
				
				while (preg_match('#([0-9]{3,})#s', $summary, $match))
				{
					$data = $this->_read_url($match[1]);
	
					if (trim($data) == '' || empty($data)) 
					{ 
						return false; 
					}
	
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
							// add the results to our item array
							array_push($this->itemset_items, array(
								'setid'		=>	$this->setid,
								'itemid'	=>	(int)$xml->item['id'],
								'name'		=>	(string)$xml->item->name,
								'quality'	=>	(int)$xml->item->quality['id'],
								'icon'		=>	'http://static.wowhead.com/images/wow/icons/small/' . strtolower($xml->item->icon) . '.jpg'
							));
						}
						else
						{
							return false;
						}
	
						unset($xml);
					}
				
					// strip what it found so we don't get an endless loop
					$summary = str_replace($match[0], '', $summary);
				}

			$cache->saveItemset($this->itemset, $this->itemset_items);
			return $this->_generateHTML();
		}
		else
		{
			$this->itemset = $result;
			$this->itemset_items = $cache->getItemsetReagents($this->itemset['setid']);
			
			return $this->_generateHTML();
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
			//search by id
			if (strpos($line, "new Summary({id: 'itemset', template: 'itemset',") !== false)
			{
				return $line;
				break;
			}
			
			// search by name
			if (strpos($line, "new Listview({template: 'itemset', id: 'item-sets',") !== false)
			{
				return $line;
				break;
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
				$patt = str_replace($key, $value, $patt);
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