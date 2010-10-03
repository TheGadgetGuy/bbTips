<?php
/**
* bbdkp-wowhead Link Parser v3 - Craftable Extension
* @package bbDkp.includes
* @version $Id $
* @Copyright (c) 2008 Adam Koch
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* syntax
* [craft {parameters}]{id or name}[/craft]
* parameters : nomats will 
* example usage
* [craft nomats]Battlelord's Plate Boots[/craft]
* 
**/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class wowhead_craft extends wowhead
{
	var $lang;
	var $createdby = array();
	var $craft = array();
	var $craft_spell = array();
	var $craft_reagents = array();
	var $patterns;
	var $nomats = false;
	var $args = array();

	function wowhead_craft($craftargs)
	{
		global $phpEx, $phpbb_root_path, $config; 
		
		if (!class_exists('wowhead_patterns')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_patterns.' . $phpEx); 
        }
        $this->args = $craftargs;
        $this->patterns = new wowhead_patterns();
		$this->lang = $config['bbtips_lang'];

	}

	function parse($name)
	{
		global $config, $phpEx, $phpbb_root_path; 
		
		if (trim($name) == '')
		{
			return false;
		}
		
		$this->nomats = (!array_key_exists('nomats', $this->args)) ? false : $this->args['nomats'];
		
		if (!class_exists('wowhead_cache')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_cache.' . $phpEx); 
        }
		$cache = new wowhead_cache();

		if (!$result = $cache->getCraftable($name, $this->lang))
		{
			// not in cache
			$data = $this->_read_url($name, 'craftable');

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
					
					// build our craft array
					$this->craft = array(
						'itemid'		=>	(int) $xml->item['id'],
						'name'			=>	(string) $xml->item->name,
						'search_name'	=>	(string) $name,
						'quality'		=>	(int) $xml->item->quality['id'],
						'lang'			=>	(string) $this->lang,
						'icon'			=>	'http://static.wowhead.com/images/wow/icons/small/' . strtolower($xml->item->icon) . '.jpg'
					);

					$id = (int) $xml->item['id']; 
					$spellid = (int) $xml->item->createdBy->spell['id']; 
					$name = (string) $xml->item->createdBy->spell['name']; 
					
					// build spell craft array
					$this->craft_spell = array(
						'reagentof'		=>	$id,
						'spellid'		=>	$spellid,
						'name'			=>	$name
					);
					
					
					if ($this->nomats == false)
					{
						// build reagent array
						foreach ($xml->item->createdBy->spell->reagent as $reagent)
						{
							array_push($this->craft_reagents, array(
								'itemid'	=>	(int) $reagent['id'],
								'reagentof'	=>	(int) $xml->item['id'],
								'name'		=>	(string) $reagent['name'],
								'quantity'	=>	(int) $reagent['count'],
								'quality'	=>	$reagent['quality'],
								'icon'		=>	'http://static.wowhead.com/images/wow/icons/small/' . strtolower($reagent['icon']) . '.jpg'
							));
						}
					}
				}
				else
				{
					
					return $this->_notfound('craftable', $name);
				}
			}

			if ($this->nomats == false)
			{
				$cache->saveCraftable($this->craft, $this->craft_spell, $this->craft_reagents);
			}
			else
			{
				$cache->saveCraftable($this->craft, $this->craft_spell);
			}
			unset($xml);
			return $this->_toHTML();
		}
		else
		{  
			$this->craft = $result;
			$this->craft_spell = $cache->getCraftableSpell($this->craft['itemid']);
			if ($this->nomats == false)
			{
				$this->craft_reagents = $cache->getCraftableReagents($this->craft['itemid']);
			}
			
			return $this->_toHTML();
		}
	}

	/**
	* Generates HTML for display
	* @access private
	**/
	function _toHTML()
	{
		if ($this->nomats == false)
		{
			// generate spell html first
			$spell_html = $this->patterns->pattern('craftable_spell');
			$spell_html = str_replace('{link}', $this->_generateLink($this->craft_spell['spellid'], 'spell'), $spell_html);
			$spell_html = str_replace('{name}', $this->craft_spell['name'], $spell_html);

			// generate reagent html now
			$reagent_html = '';
			
			if ($this->craft_reagents !="")
			{
				foreach ($this->craft_reagents as $reagent)
				{
					$patt = $this->patterns->pattern('craftable_reagents');
					$search = array(
						'{link}'	=>	$this->_generateLink($reagent['itemid'], 'item'),
						'{name}'	=>	stripslashes($reagent['name']),
						'{count}'	=>	$reagent['quantity'],
						'{qid}'		=>	$reagent['quality'],
						'{icon}'	=>	$reagent['icon']
					);
	
					foreach ($search as $key => $value)
						$patt = str_replace($key, $value, $patt);
	
					$reagent_html .= $patt;
				}
				
			}
			
			// finally put it all together
			$craft_html = $this->patterns->pattern('craftable');
			$craft_html = str_replace('{spell}' , $spell_html, $craft_html);
			$craft_html = str_replace('{reagents}', $reagent_html, $craft_html);
			$craft_html = str_replace('{link}', $this->_generateLink($this->craft['itemid'], 'item'), $craft_html);
			$craft_html = str_replace('{qid}', $this->craft['quality'], $craft_html);
			$craft_html = str_replace('{name}', stripslashes($this->craft['name']), $craft_html);
		}
		else
		{
			$craft_html = $this->patterns->pattern('craftable_nomats');
			$craft_html = str_replace('{link}', $this->_generateLink($this->craft['itemid'], 'item'), $craft_html);
			$craft_html = str_replace('{qid}', $this->craft['quality'], $craft_html);
			$craft_html = str_replace('{name}', stripslashes($this->craft['name']), $craft_html);
			$craft_html = str_replace('{splink}', $this->_generateLink($this->craft_spell['spellid'], 'spell'), $craft_html);
			$craft_html = str_replace('{spname}', stripslashes($this->craft_spell['name']), $craft_html);
		}
		return $craft_html;
	}

}
?>