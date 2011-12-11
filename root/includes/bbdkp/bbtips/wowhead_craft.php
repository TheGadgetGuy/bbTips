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
	private $craft = array();
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
		global $db, $config, $phpEx, $phpbb_root_path; 
		
		if (trim($name) == '')
		{
			return false;
		}
		
		if (!class_exists('wowhead_cache')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_cache.' . $phpEx); 
        }
		
        
        $sql = "SELECT spellid as recipeid, name FROM " . BBTIPS_CRAFT_SPELL_TBL . " WHERE name='" . $db->sql_escape($name) . "'";		
	    $result = $db->sql_query($sql);
	    $recipe_id = $db->sql_fetchfield('recipeid', false, $result);
	    $db->sql_freeresult($result);
	    
		if (!$recipe_id)
		{
			// not in db, get html
			
			$this->make_url($name, 'craftable');
			$data = $this->gethtml($name, 'craftable');

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
					$prhref = $prhtml->find('table tr td span.q1 a', 0)->href;
					preg_match_all('/([\d]+)/', $prhref, $match);
 					$prid= (int) @$match[1][0];
					$prname = $prhtml->find('table tr td span.q1 a', 0)->plaintext;
					
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
						'icon'			=>	'http://static.wowhead.com/images/wow/icons/medium/' . strtolower( (string) $xml->item->icon) . '.jpg'
					);
					
					// finally make reagents array
					
					// is there a mats array ?
					if(isset($this->args['mats']) == true)
					{
						$this->mats = true;
						$reagents_htmls = $prhtml->find('table tr td span.q1 a');
						foreach($reagents_htmls as $id => $reagents_html)
						{
							if($id > 0)
							{
								$href = $reagents_html->href;
								preg_match_all('/([\d]+)/', $href, $match);
		 						$this->craft_reagents[$id]['itemid'] = (int) @$match[1][0];
		 						$this->craft_reagents[$id]['name'] = (string) @$reagents_html->plaintext;
		 						$this->craft_reagents[$id]['reagentof'] = (int) $prid;
		 						$this->craft_reagents[$id]['quantity'] = 1;
		 						
		 						
		 						if ( !class_exists('wowhead_item')) 
				                {	                	
				                    require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_item.' . $phpEx);    
				                }
				                $args = array();
				                $object = new wowhead_item($this->craft_reagents[$id]['itemid'], $args);
				                $object->parse(trim($this->craft_reagents[$id]['itemid']));
		 						$this->craft_reagents[$id]['quality'] =  $object->quality;
		 						$this->craft_reagents[$id]['icon'] = $object->icon;
				                
		 						 $debug=1;
							}
						}
					}

					
				}
				else
				{
					
					return $this->_notfound('craftable', $name);
				}
			}

			if ($this->mats == true)
			{
				$this->saveCraftable($this->craft, $this->craft_recipe, $this->craft_reagents);
			}
			else
			{
				$this->saveCraftable($this->craft, $this->craft_recipe);
			}
			
			unset($xml);
			return $this->_toHTML();
		}
		else
		{  
			// get recipe
			$sql = "SELECT a.spellid, a.name, b.quality, a.reagentof 
				FROM " . BBTIPS_CRAFT_SPELL_TBL . " a 
				INNER JOIN " . BBTIPS_CRAFT_TBL . " b 
				ON  a.reagentof = b.itemid 
				WHERE a.spellid='" . $db->sql_escape($recipe_id) . "'";
					
		    $result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$this->craft_recipe = array(
					'recipeid'		=>	(int) $row['spellid'],
					'name'			=>	(string) $row['name'],
					'quality'		=>	(int) $row['quality'],
					'reagentof'		=>	(int) $row['reagentof'],
				);
				
			}
			$db->sql_freeresult($result);

			// get craft product
			$sql = 'SELECT * FROM ' . BBTIPS_CRAFT_TBL . " WHERE itemid = ". (int) $this->craft_recipe['reagentof'] ;
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$this->craft = array(
					'itemid'		=>	(int) $row['itemid'],
					'name'			=>	(string) $row['name'],
					'search_name'	=>	(string) $row['search_name'],
					'quality'		=>	(int) $row['quality'],
					'lang'			=>	(string) $row['lang'],
					'icon'			=>	(string) $row['icon']
				);
			}
			$db->sql_freeresult($result);
			
			if(isset($this->args['mats']) == true)
			{
				$this->mats = true;
				$this->craft_reagents = $this->getCraftableReagents($this->craft['itemid']);
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
			$spell_html = str_replace('{splink}', $this->_generateLink($this->craft_recipe['recipeid'], 'item'), $spell_html);
			$spell_html = str_replace('{spname}', $this->craft_recipe['name'], $spell_html);
			$spell_html = str_replace('{recipequality}', $this->craft_recipe['quality'], $spell_html);
			
			//product
			$craft_html = $this->patterns->pattern('craftable');
			$craft_html = str_replace('{recipe}' , $spell_html, $craft_html);
			$craft_html = str_replace('{link}', $this->_generateLink($this->craft['itemid'], 'item'), $craft_html);
			$craft_html = str_replace('{qid}', $this->craft['quality'], $craft_html);
			$craft_html = str_replace('{name}', stripslashes($this->craft['name']), $craft_html);
			
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
			
			$craft_html = str_replace('{reagents}', $reagent_html, $craft_html);
			$craft_html = str_replace('{CREATED_BY}', $user->lang['CREATED_BY'], $craft_html);
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
	
	/**
	 * inserts craft recipe
	 *
	 * @param array $craft
	 * @param array $craft_spell
	 * @param array $craft_reagents
	 * @return void
	 */
	private function saveCraftable($craft, $craft_spell, $craft_reagents = array())
	{
	    global $db;
	    
		if ( !is_array($craft) || !is_array($craft_spell)  || !isset($craft['itemid']) || !isset($craft['name'])  )
		{
		    return false;
		}
	     
		// save the recipe
		$reagentof = $craft_spell['reagentof']; 
		$spellid = $craft_spell['recipeid']; 
		$name = $craft_spell['name'] ; 

		unset ($sql_ary); 
        $sql_ary = array(
		    'reagentof'  => $reagentof, 
		    'spellid'    => $spellid, 
		    'name'       => $name,
		    
		);
			
		$sql = 'INSERT INTO ' . BBTIPS_CRAFT_SPELL_TBL . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$result = $db->sql_query($sql);
		if (!$result)
		{
			global $user;
			$user->add_lang(array('mods/dkp_tooltips'));
			trigger_error('Failed to insert ' . $craft_spell['reagentof'] . ' in the ' . BBTIPS_CRAFT_SPELL_TBL . 'table <br/><br/>') ;
			return false;
		}
				
	    // save the product
        $sql_ary = array(
		    'itemid'      => (int) $craft['itemid'],
		    'name'    	  => (string) $craft['name'],
		    'search_name' =>  (string) $craft['search_name'],
		    'quality'     => (int) $craft['quality'],
	        'lang'        => (string) $craft['lang'],
	        'icon'        => (string) $craft['icon'] 
		);
		
		$sql = 'INSERT INTO ' . BBTIPS_CRAFT_TBL . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$result = $db->sql_query($sql);
		if (!$result)
		{
			global $user;
			$user->add_lang(array('mods/dkp_tooltips'));
			trigger_error(  sprintf($user->lang['BBTOOLTIPS_ERRORCACHING'], $craft['name'] , BBTIPS_CRAFT_TBL), E_USER_WARNING ) ;
			return false;
		}
		
		// now save the reagents
		if (sizeof($craft_reagents) > 0)
		{
			foreach ($craft_reagents as $reagent)
			{
				unset ($sql_ary); 
                $sql_ary = array(
        		    'itemid'     => $reagent['itemid'], 
        		    'reagentof'  => $reagent['reagentof'], 
        		    'name'       => $reagent['name'], 
        		    'quantity'   => $reagent['quantity'], 
        		    'quality'    => $reagent['quality'], 
        		    'icon'       => $reagent['icon'], 
        		);
        		$sql = 'INSERT INTO ' . BBTIPS_CRAFT_REAGENT_TBL . ' ' . $db->sql_build_array('INSERT', $sql_ary);
               $db->sql_query($sql);
			}
		}
	}

	/**
	* Gets craftable reagents
	 *
	 * @param unknown_type $id
	 * @return unknown
	 */
	private function getCraftableReagents($id)
	{
	    if (trim($id) == '')
		{
		    return false;
		}
			
        global $db; 
        
		$reagents = array();

		$query_text = 'SELECT itemid, name, quantity, quality, icon FROM ' . BBTIPS_CRAFT_REAGENT_TBL . ' 
		WHERE reagentof=' . $db->sql_escape($id) . ' ORDER BY name ASC';
		
		$result = $db->sql_query($query_text);
	    if ( $db->sql_affectedrows() == 0)
		{
			$db->sql_freeresult($result);
			return false;
		}
		else
		{
    		while ($row = $db->sql_fetchrow($result))
            {
                array_push($reagents, $row);
            }
            $db->sql_freeresult($result);
    		return $reagents;
		}
	}

}
?>