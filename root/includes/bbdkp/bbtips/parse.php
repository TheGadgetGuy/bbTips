<?php
/**
* bbTips Parser 
*
* @package bbDkp.includes
* @version 0.3.5 $Id $
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @copyright (c) 2008 Adam Koch
* @copyright (c) 2010 bbdkp <http://code.google.com/p/bbdkp/>
* @author : Adam "craCkpot" Koch (admin@crackpot.us) 
* @author : Sajaki (sajaki@bbdkp.com)
*
* 
**/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class bbtips
{
	
	function parse($whp_message)
	{
	    global $phpbb_root_path, $phpEx, $config;
		
	    unset($match); 
	
	    $parses = 0;
	    
	    //max 600 items will be parsed no matter what the setting of maxparse is set too
		//600 will parse approximetly 8 different wowchar character profiles...
	    $maxparse = min(600,(int) $config['bbtips_maxparse']); 
	    
	    while (
	    	($parses < $maxparse) &&
		  	preg_match('#\[(item|quest|achievement|craft|itemset|spell|itemico|itemdkp|npc|wowchar)\](.+?)\[/(item|quest|achievement|craft|itemset|spell|itemico|itemdkp|npc|wowchar)\]#s', $whp_message, $match) or
		  	preg_match('#\[(item|quest|achievement|craft|itemset|spell|itemico|itemdkp|npc|wowchar) (.+?)\](.+?)\[/(item|quest|achievement|craft|itemset|spell|itemico|itemdkp|npc|wowchar)\]#s', $whp_message, $match) 
		  	)
		  {
				$args = array();
			
				if (  (count($match)>= 5) && ( 
						strpos($match[2], 'lang=') !== false || strpos($match[2],'nomats') !== false || strpos($match[2], 'enchant=') !== false ||
						strpos($match[2], 'size=') !== false || strpos($match[2],'rank=')  !== false || strpos($match[2], 'gems=') !== false ||
						strpos($match[2], 'loc=') !== false || strpos($match[2],'realm=')  !== false || strpos($match[2],'region=')  !== false  )
					)
				{
					// we have arguments
					$args = $this->whp_arguments($match[2]);
				}
				
			    if ( !class_exists('wowhead')) 
                {
                	require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead.' . $phpEx); 
                }
                
                
				switch ($match[1])
				{
					case 'item':
		        		if ( !class_exists('wowhead_item')) 
		                {	                	
		                    require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_item.' . $phpEx);    
		                }
		                $object = new wowhead_item();
						break;
						
					case 'itemdkp':
		        		if ( !class_exists('wowhead_item_dkp')) 
		                {    
		                    require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_itemdkp.' . $phpEx);    
		                    
		                 }
		                 $object = new wowhead_item_dkp();
						break;
						
					case 'itemico':
		        		if ( !class_exists('wowhead_itemico')) 
		                {
		                    require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_itemico.' . $phpEx); 
		                }
		                $object = new wowhead_itemico(); 
						break;
						
					case 'spell':
		        		if ( !class_exists('wowhead_spell')) 
		                {
		                    require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_spell.' . $phpEx);    
		                }
		                $object = new wowhead_spell();
						break;
						
					case 'quest':
					    if ( !class_exists('wowhead_quest')) 
		                {
		                    require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_quest.' . $phpEx);    
		                }
		                $object = new wowhead_quest();
						break;
						
					case 'achievement':
		                if ( !class_exists('wowhead_achievement')) 
		                {
		                    require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_achievement.' . $phpEx);    
		                }
		                $object = new wowhead_achievement();
						break;
						
					case 'itemset':
		        		if ( !class_exists('wowhead_itemset')) 
		                {
		                    require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_itemset.' . $phpEx);    
		                }
		                $object = new wowhead_itemset();
						break;
						
					case 'craft':
					    if ( !class_exists('wowhead_craft')) 
		                {
		                    require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_craft.' . $phpEx);    
		                }
		                $object = new wowhead_craft();
						break;
						
					case 'npc':
		                if ( !class_exists('wowhead_npc')) 
		                { 
		                    require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_npc.' . $phpEx);    
		                }
		                $object = new wowhead_npc();
						break;
					case 'wowchar':	
						// uses the arguments realm and region
			            if ( !class_exists('wowcharacter')) 
			                { 
			                    require($phpbb_root_path . 'includes/bbdkp/bbtips/wowcharacter.' . $phpEx);    
			                }
			                $object = new wowcharacter();
						break;
												
						
					default:
						break;
				}
		
				$namein = (sizeof($args) > 0) ? html_entity_decode($match[3], ENT_QUOTES) : html_entity_decode($match[2], ENT_QUOTES);
		   		
			   	// prevent any unwanted script execution or html formatting
				$nameout = $this->html2txt($namein);
				if ($nameout != $namein)
				{
				    $whp_message = str_replace($match[0], "<span class=\"notfound\">Illegal HTML/JavaScript found.</span>", $whp_message);
				}
				else
				{
					// ok tag content allowed, go to parser
				    $whp_message = str_replace($match[0], $object->parse(trim($nameout), $args), $whp_message);
				}
		   		
		   		$parses++;
		}
		
		unset($object);
		return $whp_message;
	}
	
	/**
	 * strips illegal html/javascript
	 */
	function html2txt($document)
	{
	  $search = array('@]*?>.*?@si',          // Strip out javascript
	                 '@]*?>.*?@siU',          // Strip style tags properly
	                 '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
	                 '@@',                    // Strip multi-line comments including CDATA
	  				 '@http@si' , 			  // strip out http 
	  				 '@HTTP@si' , 			  // strip out HTTP
	  				 '@https@si' , 			  // strip out https 
	  );
	  $text = preg_replace($search, '', $document);
	  return trim($text);
	}

	
	// turn the arguments into an array
	function whp_arguments($in)
	{
		if (strlen($in) == 0) 
		{ 
			return false; 
		}
		
		if (strpos($in, '"') !== false) 
		{ 
			$in = str_replace('"', '', $in); 
		}
		
		if (strpos($in, '&quot;') !== false) 
		{ 
			$in = str_replace('&quot;', '', $in); 
		}
		
		if (strpos($in, ' ') === false)
		{ 
			$args = array();
			// only one argument
			if (trim($in) == 'nomats')
			{
				return array(
					'nomats'	=>	true
				);
			}
			elseif (trim($in) == 'realm')
			{
				// used with wowchar				
				return array('realm' => true);
			}
			elseif (trim($in) == 'region')
			{
				// used with wowchar				
				return array('region' => true);
			}
			else
			{
				$pre = substr($in, 0, strpos($in, '='));
				$post = substr($in, strpos($in, '=') + 1);
				$args[$pre]=$post;
				
				return $args;
			}
		}
		else
		{
			$args = array();
			// multiple arguments
			$in_array = explode(' ', $in);
	
			foreach ($in_array as $value)
			{
				if ($value == 'nomats')
				{
					$args['nomats'] = true;
				}
				elseif ($value == 'realm')
				{
					// used with wowchar
					$args['realm'] = true;
				}
				elseif ($value == 'region')
				{
					// used with wowchar					
					$args['region'] = true;
				}				
				else
				{
					$pre = substr($value, 0, strpos($value, '='));
					$post = substr($value, strpos($value, '=') + 1);
					$args[$pre] = $post;
				}
	
			}
			return $args;
		}
	}
}
?>