<?php
/**
* bbdkp Character core class
*
* @package bbDkp.includes
* @version $Id$
* @copyright (c) 2010 bbDkp <http://code.google.com/p/bbdkp/>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @author Kapli, Malfate, Sajaki, Blazeflack, Twizted
*
*
**/

/**
 * This class describes a wow character 
 */
 function replace_specchar($str) {
  $str = htmlentities($str, ENT_COMPAT, "UTF-8");
  $str = preg_replace(
'/&([a-zA-Z])(uml|acute|grave|circ|tilde);/',
'',$str);
  return html_entity_decode($str);
}
class wowcharacter
{
	// character definition
	
	public $name ='';
	public $realm = '';
	public $region = '';
	public $ModelViewURL;
	public $url;
	public $feedurl;
	public $level ='';
	public $class = 0;
	public $talents ='';
	
	public $talent1name ='';
	public $talent1 ='';
	public $talent2name ='';
	public $talent2 ='';
	public $professions ='';
	public $classid = 0;
	public $genderid = 0;
	public $raceid = 0;
	public $faction = 0;
	public $guild = ''; 

	public $spellpower = 0; 
	public $spellhit = 0; 
	public $firecrit = 0;
	public $frostcrit = 0; 
	public $arcanecrit = 0; 
	public $holycrit = 0; 
	public $shadowcrit = 0; 
	public $naturecrit = 0; 
	public $mrcast = 0; 
	public $spellhaste = 0; 
	
	public $hp = 0; 
	public $mana = 0; 
	public $rap = 0; 
	public $rcr = 0; 
	public $rhr = 0;  
	public $rdps = 0; 
	public $rspeed = 0; 
	public $map = 0; 
	public $mcr = 0;
	public $mhr = 0; 
	public $mhdps = 0; 
	public $ohdps = 0; 
	public $mspeed = 0; 
	
	public $expertise = 0; 
	public $armor = 0; 
	public $defense = 0; 
	public $dodge = 0; 
	public $parry = 0; 
	public $block = 0;  

	public $glyphminor;
	public $glyphmajor;
	public $item = array();
	public $achievements;
	public $gear = array();
	public $ilvl = array();
	public $gems1 = array();
	public $gems2 = array();
	public $gems3 = array();
	public $ench = array();
	public $gearNameLink = array();
	
	public $modeltemplate; 

	

	function wowcharacter()
	{

	}
	
	
	/**
 	 * main parser function to get character from armory
	 * 
	 * @tutorial usage [wowchar realm=Lightbringer region=EU]Sajaki[/wowchar]
	 * 
	 * @access public
	 * @return boolean or object
	 * 
	 * @param $name = content between brackets
	 * @param $arguments = optional array holding two arguments realm and region 
	 * 
	 */
	public function parse($name, $arguments)
	{
		global $db, $user, $config;
		
		if (strlen($name) == 0 )
		{
			return false;
		}
		
		if(isset($arguments['realm']))
		{
			$spaceChars = array("+", "_");
			$this->realm = str_replace($spaceChars, " ",  $arguments['realm'] );  
		}
		else 
		{
			if(isset( $config['bbdkp_default_realm']))
			{
				//take default realm
				$this->realm = $config['bbdkp_default_realm']; 
			}
			else 
			{
				return false;
			}
		}
		
		//region either EU or US
		if(isset($arguments['region']))
		{
			//take default region
			$this->region = $arguments['region']; 
		}
		else
		{
		
			if(isset( $config['bbdkp_default_region']))
			{
				//take default region
				$this->region = $config['bbdkp_default_region']; 
			}
			else 
			{
				return false;
			}
			
		}
		
		$this->name = $name; 
		
		$base_url = ($this->region == "US") ? "http://www.wowarmory.com" : "http://eu.wowarmory.com"; 
		$charurl = $base_url . "/character-sheet.xml?r=" . $this->realm  . "&n=" . $name;

		$this->feedurl = $base_url . "/character-feed.atom?r=" . $this->realm  . "&cn=" . $name . '&filters=ACHIEVEMENT,RESPEC&achCategories=168&locale=en_US'; 
		$this->ModelViewURL = $base_url . "/character-model-embed.xml?r=" . urlencode($this->realm ) . "&cn=" . urlencode($name) . "&rhtml=true";
		$this->url = $base_url . "/character-sheet.xml?r=" . urlencode($this->realm ) . "&n=" . urlencode($name);

		
		//calling static bbdkp urlreader function. 
		$xml_data = bbDkp_Admin::read_php ( $this->url,false,false );
		
		if (empty($xml_data))
		{
    	    return false;
		}
		else
		{
		    $xml = simplexml_load_string($xml_data);
		    if (!isset($xml->characterInfo->character['name']))
		    {
        	    return false;
		    }
		}
		
		if($this->_Getchar2($xml) == true)
		{
			// get post content
			$this->modeltemplate = $this->_getHTML();
			return $this->modeltemplate; 
		}
		else 
		{
			return false;
		}
		
	}
	
	/**
	 * internal function to fill character object
	 * 
	 */
	private function _Getchar2($xml) 
	{
		
		global $phpbb_root_path, $phpEx;  
		
		$skills = $xml->xpath('characterInfo/characterTab/professions/skill');
		if (!empty($skills)) 
		{
			$this->professions = '';
			foreach ($skills as $k => $v) 
			{
			    $skills[$k] = $v->attributes();
				$this->professions .='<img src="'.$phpbb_root_path.'images/bbtips/Trade_'.$skills[$k]['name'] . '.jpg" width="20" height="20" alt="'.$skills[$k]['name'] . '">[color=#105289] '.$skills[$k]['name'] . '[/color] '. $skills[$k]['value'] . '/'. $skills[$k]['max'] .'<br/>';
			}
		}	
		else 
		{
			//return false; 
			//If no professions are detected
		}
				
		$talent = $xml->xpath('characterInfo/characterTab/talentSpecs/talentSpec');
		foreach ($talent as $k => $v)
		{
		    $talent[$k] = $v->attributes();
            if ($talent[$k]['active']=="1") 
            {
            	$this->talent1name = (string) $talent[$k]['prim'];
			    $this->talent1 =  ' (' . $talent[$k]['treeOne'] . "/" . $talent[$k]['treeTwo'] . "/" . $talent[$k]['treeThree'].")";
			    
            }
            else
            {
			    $this->talent2name = (string) $talent[$k]['prim'];
			    $this->talent2 = ' (' . $talent[$k]['treeOne'] . "/" . $talent[$k]['treeTwo'] . "/" . $talent[$k]['treeThree'].")";
            }
		}
		$this->talents = (string) $this->talent1 . '  ' . $this->talent2;
		$this->name = (string)  ucfirst(strtolower($this->name));
		$this->level = (int)  $xml->characterInfo->character['level'];
		
		$this->class = (string) $xml->characterInfo->character['class'];
		$this->classid = (int) $xml->characterInfo->character['classId'];
		$this->genderid = (int) $xml->characterInfo->character['genderId'];
		$this->raceid = (int) $xml->characterInfo->character['raceId'];
		$this->faction = (string) $xml->characterInfo->character['faction'];
		
		$this->spellpower = (float) $xml->characterInfo->characterTab->spell->bonusHealing['value'];
		$this->spellhit = (float)$xml->characterInfo->characterTab->spell->hitRating['increasedHitPercent'];
		$this->firecrit = (float)  $xml->characterInfo->characterTab->spell->critChance->fire['percent'];
		$this->frostcrit = (float) $xml->characterInfo->characterTab->spell->critChance->frost['percent'];
		$this->arcanecrit = (float) $xml->characterInfo->characterTab->spell->critChance->arcane['percent'];
		$this->holycrit = (float) $xml->characterInfo->characterTab->spell->critChance->holy['percent'];
		$this->shadowcrit = (float) $xml->characterInfo->characterTab->spell->critChance->shadow['percent'];
		$this->naturecrit = (float) $xml->characterInfo->characterTab->spell->critChance->nature['percent'];
		$this->mrcast = (float) $xml->characterInfo->characterTab->spell->manaRegen['casting'];
		$this->spellhaste = (float) $xml->characterInfo->characterTab->spell->hasteRating['hastePercent'];		
		$this->hp = (int) $xml->characterInfo->characterTab->characterBars->health['effective'];
		$this->mana = (int) $xml->characterInfo->characterTab->characterBars->secondBar['effective'];
		$this->rap = (float) $xml->characterInfo->characterTab->ranged->power['effective'];
		$this->rcr = (float) $xml->characterInfo->characterTab->ranged->critChance['percent'];
		$this->rhr = (float) $xml->characterInfo->characterTab->ranged->hitRating['increasedHitPercent'];
		$this->rdps = (float) $xml->characterInfo->characterTab->ranged->damage['dps'];
		$this->rspeed = (float) $xml->characterInfo->characterTab->ranged->speed['hastePercent'];
		$this->map = (int) $xml->characterInfo->characterTab->melee->power['effective'];
		$this->mcr = (float) $xml->characterInfo->characterTab->melee->critChance['percent'];
		$this->mhr = (float) $xml->characterInfo->characterTab->melee->hitRating['increasedHitPercent'];
		$this->mhdps = (float) $xml->characterInfo->characterTab->melee->mainHandDamage['dps'];
		$this->ohdps = (float) $xml->characterInfo->characterTab->melee->offHandDamage['dps'];
		$this->mspeed = (float) $xml->characterInfo->characterTab->melee->mainHandSpeed['hastePercent'];
		$this->expertise = (float) $xml->characterInfo->characterTab->melee->expertise['percent'];
		$this->armor = (int) $xml->characterInfo->characterTab->defenses->armor['effective'];
		$this->defense = (float) $xml->characterInfo->characterTab->defenses->defense['value'] + $xml->characterInfo->characterTab->defenses->defense['plusDefense'];
		$this->dodge = (float) $xml->characterInfo->characterTab->defenses->dodge['percent'];
		$this->parry = (float) $xml->characterInfo->characterTab->defenses->parry['percent'];
		$this->block = (float) $xml->characterInfo->characterTab->defenses->block['percent'];

		//glyphs 
		$glyphs = $xml->characterInfo->characterTab->glyphs;
		$this->glyphmajor = '';
		$this->glyphminor = '';
		foreach ($glyphs->glyph as $key)
		{
			if(@$key->attributes()->type[0] == "major")
			{
				$this->glyphmajor .= '[itemico]'. (string) @$key->attributes()->name[0] . '[/itemico]&nbsp';
			}
			elseif (@$key->attributes()->type[0] == "minor")
			{
				$this->glyphminor .= '[itemico]'. (string) @$key->attributes()->name[0] . '[/itemico]&nbsp';
			}
		}
		
		// Gem/item information, and item name display. (Icon or name only)
		// Thanks Ethereal for finding Info for Gear List Tab
		$item = $xml->xpath('characterInfo/characterTab/items/item');
		foreach ($item as $k => $v)
		{
			$item[$k]= $v->attributes();
			
			$gearslot = $item[$k]['slot'];
			$gearslot = intval($gearslot);
	
			unset ($gearench);		
			// is item enchanted ?
			if (!empty($item[$k]['permanentEnchantItemId'])) 
			{
				$this->ench[$gearslot] = '[itemico size=small]' . (string) $item[$k]['permanentEnchantItemId'] . '[/itemico]';
			}
			else
			{
				$this->ench[$gearslot] = '';
			}

			// is item gemmed ?
			unset ($gemall);
			$geargem1 = (string) $item[$k]['gem0Id'];
			$geargem2 = (string) $item[$k]['gem1Id'];
			$geargem3 = (string) $item[$k]['gem2Id'];
			$this->gems[$gearslot] = ''; 
			if ($geargem1!="0") 
			{
				$this->gems[$gearslot] .= '[itemico size=small]' . $geargem1 . '[/itemico]';
			}
			if ($geargem2!="0") 
			{
				$this->gems[$gearslot] .= '[itemico size=small]' . $geargem2 . '[/itemico]';
			}
			if ($geargem3!="0") 
			{
				$this->gems[$gearslot] .= '[itemico size=small]' . $geargem3 . '[/itemico]';
			}

			//Icon link using bbcodes --> icon type set in html
			$this->gear[$gearslot] = (string) $item[$k]['id'];
			
			//Item name only(no icon) using bbcodes
			$this->gearNameLink[$gearslot] = '[item]' . (string)  $item[$k]['id'] . '[/item]';
			
			$this->ilvl[$gearslot] = (string) $item[$k]['level'];
		}
		
		
	    if (!class_exists('SimplePie')) 
        {
			require($phpbb_root_path . 'includes/bbdkp/bbtips/simplepie.class.' . $phpEx); 
        }
        
		// We'll process this feed with all of the default options.
		$feed = new SimplePie();
		
		$feed->set_feed_url($this->feedurl);
		// Run SimplePie.
		$feed->init();
		// This makes sure that the content is sent to the browser as text/html and the UTF-8 character set (since we didn't change it).
		$feed->handle_content_type();
		
		// get 15 latest achievements
		$count = 0;
		$this->achievements = '';  
		foreach ($feed->get_items() as $item)
		{
			$achdate = $item->get_date('j F Y : '); 
			$description = $item->get_description(); 
			$this->achievements .= '<p>' . $achdate . $description . '</p>';
			$count++;
			if ($count > 15 )
			{
				break;
			}
		}
		unset($feed); 
		
		return true; 
		
	}
	
	/**
	 * this function prepares the post contents, using object data
	 * table html gets passed back to main 
	 *  
	 */
	private function _getHTML()
	{
		global $common, $user, $phpbb_root_path;
		
		// get template
		$opendir = @opendir($phpbb_root_path . 'includes/bbdkp/bbtips/patterns/');
		 
		while (($file = readdir($opendir)) !== false )
		{
			if (substr($file, strpos($file, '.') + 1) == 'html')
			{
				$filename = (strpos($file, 'php') !== false) ? str_replace('.php', '', $file) : str_replace('.html', '', $file);
				$filecontents[$filename] = @file_get_contents($phpbb_root_path . 'includes/bbdkp/bbtips/patterns/' . $file);
			}
		}
		
		// get main div				
		$innerdiv = $filecontents['wowchar_innerdiv'];
		
		//avatar creation url - get pic from roster
		if($this->level == 80)
		{
			$bracketlevel= "-80";
		}
		elseif($this->level>=70)
		{
			$bracketlevel= "-70";
		}
		elseif($this->level>=60)
		{
			$bracketlevel= "";
		}
		else
		{
			$bracketlevel="-default";
		}
		
		// we get the icon from bbDkp roster --> bbdkp must be installed !
		$memberportraiturl = $phpbb_root_path. './images/roster_portraits/wow'. $bracketlevel .'/' . 
			$this->genderid . '-' . $this->raceid . '-' . $this->classid . '.gif';
		$innerdiv = str_replace('{PLAYERID}', replace_specchar($this->name)  , $innerdiv);
		
		// replace placeholders with content	
		$innerdiv = str_replace('{PLAYERNAME}',  $this->name . ', ' . $this->realm . '/' . $this->region , $innerdiv);
		$innerdiv = str_replace('{PLAYERURL}',  $this->url  , $innerdiv);
		$innerdiv = str_replace('{PORTRAIT}', $memberportraiturl , $innerdiv);
		
		// make right td
		// insert gearlist and total3d and achiev in right td
		$gearlist = $filecontents['wowchar_gearlist'];
		$innerdiv = str_replace('{GEARLIST}', $gearlist , $innerdiv);
		$total3d = $filecontents['wowchar_total3d'];
		$innerdiv = str_replace('{TOTAL3D}', $total3d ,   $innerdiv);
		$achievements = $filecontents['wowchar_achievements'];
		$innerdiv = str_replace('{ACHIEV}', $achievements , $innerdiv);

		//put achievement list in div
		$search['{ACHIEVLIST}'] = $this->achievements; 
		$search['{FACTION}'] = $this->faction; 
		
		//prepare replacement strings for 18 slots
		$search['{T_IMAGES_PATH}']  = $phpbb_root_path . 'images/'; 
		//total3d
		$search['{TALENT1NAME}'] = $this->talent1name;
		$search['{TALENT2NAME}'] = $this->talent2name;

		//class icon : if the class name has a space in it then take just the first part, or else take the whole classname
		$build1img = (strstr($this->talent1name,' ')) ? strtolower(substr($this->talent1name, 0, strpos($this->talent1name, ' '))) : strtolower($this->talent1name);
		$search['{TALENT1ICON}'] = $phpbb_root_path . '/styles/' . $user->theme['theme_path'] . '/theme/images/bbtips/spec_icons/' . strtolower($this->class) . '_' . $build1img . '.png';  
		$build2img = (strstr($this->talent2name,' ')) ? strtolower(substr($this->talent2name, 0, strpos($this->talent2name, ' '))) : strtolower($this->talent2name);
		$search['{TALENT2ICON}'] = $phpbb_root_path . '/styles/' . $user->theme['theme_path'] . '/theme/images/bbtips/spec_icons/' . strtolower($this->class) . '_' . $build2img . '.png';  

		$search['{TALENTTREE1}'] = $this->talent1;  
		$search['{TALENTTREE2}'] = $this->talent2;
		$search['{GLYPHMINOR}'] = $this->glyphminor;
		$search['{GLYPHMAJOR}'] = $this->glyphmajor;
		$search['{MODEL3D}'] = $this->ModelViewURL; 
		
		//gearlist
		$search['{TALENTS}'] = $this->talent1 . '  ' . $this->talent2;  
		$search['{MEMBERURL}'] = $memberportraiturl;
		$search['{FRAME}'] = $phpbb_root_path . '/styles/' . $user->theme['theme_path'] . '/theme/images/bbtips/gear_list_portrait_frame.png';  
		
		for($slot = 0 ; $slot <= 18; $slot++)
		{
			//TOTAL3D (ico) and GEARLIST (smallico)
			$search['{GEAR'.$slot .'}'] = (isset($this->gear[$slot]) ? '[itemico]' . $this->gear[$slot] . '[/itemico]' : '' );
			$search['{GEARICO'.$slot .'}'] = (isset($this->gear[$slot]) ? '[itemico size=small]' . $this->gear[$slot] . '[/itemico]': '' );
			//GEARLIST 
			$search['{GEARNAMELINK'.$slot.'}'] = (isset($this->gearNameLink[$slot]) ? $this->gearNameLink[$slot]  : '' );
			$search['{ILVL'.$slot.'}'] = (isset($this->ilvl[$slot]) ? $this->ilvl[$slot] : '' );
			$search['{GEMS'.$slot.'}'] = (isset($this->gems[$slot]) ? $this->gems[$slot] : '' );
			$search['{ENCH'.$slot.'}'] = (isset($this->ench[$slot]) ? $this->ench[$slot] : '' );
		}
		
		// replace slots with content
		foreach ($search as $key => $value)
		{
			$innerdiv = str_replace($key, $value, $innerdiv);
		}
		
		
		//return innerdiv for insertion in post
		return $innerdiv; 		
		
	}

}

?>