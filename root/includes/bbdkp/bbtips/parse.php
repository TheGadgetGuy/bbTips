<?php
/**
* bbdkp-wowhead Link Parser v3 - Parse Script
*
* @package bbDkp.includes
* @version $Id $
* @Copyright (c) 2008 Adam Koch
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
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

class bbtips
{
	
	function parse($whp_message)
	{
	    global $phpbb_root_path, $phpEx, $config;
		
	    unset($match); 
	
	    $parses = 0;
	    
	    //max 30 items will be parsed whatever the setting of maxparse
	    $maxparse = min(100,(int) $config['bbtips_maxparse']); 
	    
	    while (
	    	($parses < $maxparse) &&
		  	preg_match('#\[(item|quest|achievement|craft|itemset|spell|itemico|itemdkp|npc)\](.+?)\[/(item|quest|achievement|craft|itemset|spell|itemico|itemdkp|npc)\]#s', $whp_message, $match) or
		  	preg_match('#\[(item|quest|achievement|craft|itemset|spell|itemico|itemdkp|npc) (.+?)\](.+?)\[/(item|quest|achievement|craft|itemset|spell|itemico|itemdkp|npc)\]#s', $whp_message, $match) 
		  	)
		  {
				$args = array();
			
				if (  (count($match)>= 5) && ( 
						strpos($match[2], 'lang=') !== false || strpos($match[2],'nomats') !== false || strpos($match[2], 'enchant=') !== false ||
						strpos($match[2], 'size=') !== false || strpos($match[2],'rank=')  !== false || strpos($match[2], 'gems=') !== false ||
						strpos($match[2], 'loc=') !== false  || strpos($match[2],'noicons') !== false || strpos($match[2], 'noclass') !== false ||
						strpos($match[2], 'norace') !== false)
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
						
					default:
						break;
				}
		
				$name = (sizeof($args) > 0) ? html_entity_decode($match[3], ENT_QUOTES) : html_entity_decode($match[2], ENT_QUOTES);
		   		
			   	// prevent any unwanted script execution or html formatting
				$name = strip_tags($name);
				if (trim($name) == '')
				{
				    $whp_message = str_replace($match[0], "<span class=\"notfound\">Illegal HTML/JavaScript found. Tags removed.</span>", $whp_message);
				}
				else
				{
				    $whp_message = str_replace($match[0], $object->parse($name, $args), $whp_message);
				}
		   		
		   		$parses++;
		}
		
		unset($object);
		return $whp_message;
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
			elseif (trim($in) == 'noicons')
			{
				// force a reload from the armory
				return array('noicons'	=>	true);
			}
			elseif (trim($in) == 'noclass')
			{
				return array('noclass' => true);
			}
			elseif (trim($in) == 'norace')
			{
				return array('norace' => true);
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
				elseif ($value == 'noicons')
				{
					$args['noicons'] = true;
				}
				elseif ($value == 'noclass')
				{
					$args['noclass'] = true;
				}
				elseif ($value == 'norace')
				{
					$args['norace'] = true;
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