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
	public $lang;
	private $createdby = array();
	
	/**
	 * the formula
	 *
	 * @var array
	 */
	private $craft_recipe = array();
	private $craft_product = array();
	private $craft_reagents = array();
	public 	$patterns;
	private $mats = false;
	private $args = array();

	public function wowhead_craft($craftargs)
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

		if (!$result = $cache->getCraftable($name, $this->lang))
		{
			// not in cache, get html
			$this->make_url($name, 'craftable');
			$data = $this->gethtml('craftable');

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
					
					// the craft recipe
					$craftrecipe = (array) json_decode((string) '{' .$xml->item->json[0] . '}');
					
					// get product by parsing through tooltip html
					if (!class_exists('simple_html_dom_node')) 
			        {
			            include ($phpbb_root_path . 'includes/bbdkp/bbtips/simple_html_dom.' . $phpEx); 
			        }
					$prhtml = str_get_html ($xml->item->htmlTooltip[0], $lowercase = true);
					$prhref = $prhtml->find('table tr td span.q1 a', 1)->href;
					preg_match_all('/([\d]+)/', $prhref, $match);
 					$prid= (int) @$match[1][0];
					$prname = $prhtml->find('table tr td span.q1 a', 1)->plaintext;
					
					// make recipe array
					$this->craft_recipe = array(
						'recipeid'		=>	(int) $craftrecipe['id'],
						'name'			=>	(string) substr($craftrecipe['name'],1),
						'quality'		=>	(int) $xml->item->quality['id'],
						'reagentof'		=>	(int) $prid,
					);
					
					// make product array					
					$this->craft = array(
						'itemid'		=>	(int) $prid,
						'name'			=>	(string) $prname,
						'search_name'	=>	(string) $prname,
						'quality'		=>	(int) $xml->item->quality['id'],
						'lang'			=>	(string) $this->lang,
						'icon'			=>	'http://static.wowhead.com/images/wow/icons/small/' . strtolower($xml->item->icon) . '.jpg'
					);
					
					// finally make reagents array
					//$this->mats = (!array_key_exists('nomats', $this->args)) ? true : false;
					/*if ($this->mats == true)
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
*/
					
				}
				else
				{
					
					return $this->_notfound('craftable', $name);
				}
			}

			if ($this->mats == true)
			{
				$cache->saveCraftable($this->craft, $this->craft_recipe, $this->craft_reagents);
			}
			else
			{
				$cache->saveCraftable($this->craft, $this->craft_recipe);
			}
			
			unset($xml);
			return $this->_toHTML();
		}
		else
		{  
			$this->craft = $result;
			$this->craft_recipe = $cache->getCraftableSpell($this->craft['itemid']);
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
	private function _toHTML()
	{
		global $user;
		$user->add_lang(array('mods/dkp_tooltips'));
		
		if ($this->mats == true)
		{
			// generate spell html first
			$spell_html = $this->patterns->pattern('craftable_spell');
			$spell_html = str_replace('{link}', $this->_generateLink($this->craft_recipe['recipeid'], 'item'), $spell_html);
			$spell_html = str_replace('{name}', $this->craft_recipe['name'], $spell_html);

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
			//recipe
			$craft_html = str_replace('{splink}', $this->_generateLink($this->craft_recipe['recipeid'], 'item'), $craft_html);
			$craft_html = str_replace('{recipequality}', $this->craft_recipe['quality'], $craft_html);
			$craft_html = str_replace('{spname}', stripslashes($this->craft_recipe['name']), $craft_html);
			//product
			$craft_html = str_replace('{link}', $this->_generateLink($this->craft['itemid'], 'item'), $craft_html);
			$craft_html = str_replace('{qid}', $this->craft['quality'], $craft_html);
			$craft_html = str_replace('{name}', stripslashes($this->craft['name']), $craft_html);
			
			$craft_html = str_replace('{CREATED_BY}', $user->lang['CREATED_BY'], $craft_html);
		}
		return $craft_html;
	}

}
?>