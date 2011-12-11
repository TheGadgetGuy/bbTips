<?php
/**
* bbdkp-wowhead cache class
*
* @package bbDkp.includes
* @version $Id$
* @author (c) 2009 bbDKP - Sajaki 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
 * wrapper sql interface
 *
 */
class wowhead_cache
{



	public function saveNPC($info)
	{
		if (sizeof($info) == 0  || !isset($info['npcid']) || !isset($info['name'])  )
		{
		    return false;
		}
		
        global $db;
        
        // save the npc
        $sql_ary = array(
		    'npcid'         => (int) $info['npcid'],
		    'name'    	     => $info['name'] ,
		    'search_name'   => $info['search_name'] ,
		    'lang'          => $info['lang'],
		);
		
		$sql = 'INSERT INTO ' . BBTIPS_NPC_TBL . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$result = $db->sql_query($sql);
		if (!$result)
		{
			global $user;
			$user->add_lang(array('mods/dkp_tooltips'));
			trigger_error(  sprintf($user->lang['BBTOOLTIPS_ERRORCACHING'], $info['name'] , BBTIPS_NPC_TBL), E_USER_WARNING ) ;
			return false;
		}

	}

	/**
	* Saves itemset
	* @access public
	**/
	public function saveItemset($itemset, $items )
	{
	    global $db;
	    
	    
		if (!is_array($itemset) || !is_array($items) || !isset($itemset['setid']) || !isset($itemset['name']) )
		{
		    return false;
		}

		// save the itemset first, then we'll handle each item
        $sql_ary = array(
		    'setid'         => (int) $itemset['setid'],
		    'name'    	    => $itemset['name'],
		    'search_name'   => $itemset['search_name'],
		    'lang'          => $itemset['lang'],
		 );
		 
		$sql = 'INSERT INTO ' . BBTIPS_ITEMSET_TBL . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);
		if ($db->sql_affectedrows() == 0)
		{
			global $user;
			$user->add_lang(array('mods/dkp_tooltips'));
			trigger_error(  sprintf($user->lang['BBTOOLTIPS_ERRORCACHING'], $itemset['name'] , BBTIPS_ITEMSET_TBL), E_USER_WARNING ) ;
			return false;
		}
		else
		{
       		$sql = "DELETE FROM " . BBTIPS_ITEMSET_REAGENT_TBL . ' WHERE SETID = ' . (int) $itemset['setid']; 
       		$db->sql_query($sql);
   			foreach ($items as $item)
			{
   	            $sql_ary = array(
                       'setid'     => (int) $itemset['setid'],
                       'itemid'    => (int) $item['itemid'],
                       'name'      => $item['name'],
                       'quality'   => $item['quality'],
                       'icon'      => $item['icon'],
           		 );
				$sql = "INSERT INTO " . BBTIPS_ITEMSET_REAGENT_TBL . ' ' .  $db->sql_build_array('INSERT', $sql_ary);
				$db->sql_query($sql);
				if ($db->sql_affectedrows() == 0)
				{
					global $user;
					$user->add_lang(array('mods/dkp_tooltips'));
           			trigger_error(  sprintf($user->lang['BBTOOLTIPS_ERRORCACHING'], $item['name'] , BBTIPS_ITEMSET_REAGENT_TBL), E_USER_WARNING ) ;
           			return false;
				}
			}
		}
	}

	public function getNPC($name, $lang)
	{
		global $config, $db; 
		if (trim($lang) == '')
		{
		    $lang = $config['bbtips_lang'];
		}
		
        $search = $db->sql_like_expression($db->any_char . $db->sql_escape($name) . $db->any_char) ; 
		
		$query_text = 'SELECT npcid, name FROM ' . BBTIPS_NPC_TBL . ' WHERE 
					 (search_name ' . $search . '
					      OR name ' . $search . '
						  OR npcid '. $search . "
					  )  AND lang='"  . $lang . "'";
		
		$result = $db->sql_query($query_text);
							
	    if ( $db->sql_affectedrows() == 0)
		{
			$db->sql_freeresult($result);
			return false;
		}
		else
		{
		    $row =  $db->sql_fetchrow($result);
			return $row; 
		}
		
	}

	/**
	* Gets itemset
	* @access public
	**/
	public function getItemset($name)
	{
		global $db, $config; 
		
		$search = $db->sql_like_expression($db->any_char . $db->sql_escape($name) . $db->any_char) ; 
		
		$query_text = 'SELECT setid, name FROM ' . BBTIPS_ITEMSET_TBL . ' WHERE 
					 (search_name ' . $search . '
					      OR setid ' . $search . '
						  OR name '. $search . "
					  )  AND lang='"  . $config['bbtips_lang'] . "'";
		
	    $result = $db->sql_query($query_text);
							
	    if ( $db->sql_affectedrows() == 0)
		{
			$db->sql_freeresult($result);
			return false;
		}
		else
		{
		    $row =  $db->sql_fetchrow($result);
			return $row; 
		}

	}

	

	/**
	* Gets itemset components
	* @access public
	**/
	public function _getItemsetReagents($id)
	{
	    if (trim($id) == '')
		{
		    return false;
		}
	    global $db;
	    
		$reagents = array();

		$query_text = 'SELECT itemid, name, quality, icon FROM ' . BBTIPS_ITEMSET_REAGENT_TBL . "
						WHERE setid='" . $id . "'
						ORDER BY name ASC";
		
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

	/**
	* Gets Gem from 
	* @access public
	**/
	public function getGems($itemid)
	{
	    
	    global $db;
		$gems = array();
		$query_text = 'SELECT gemid FROM ' . BBTIPS_GEM_TBL . ' WHERE itemid=\'$itemid\' ORDER BY slot ASC';
		$result = $db->sql_query($query_text);

		if ( $db->sql_affectedrows() == 0)
		{
			$db->sql_freeresult($result);
			return false;
		}
		else
		{
		    if ($db->sql_affectedrows() > 1  )
		    {
        		while (list($gemid) = $db->sql_fetchrow($result))
                {
                    array_push($gems, $row);
                }
                $db->sql_freeresult($result);
        		return $gems;
		    }
		    else // just 1
		    {
		        list($gemid) = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				return $gemid;
		        
		    }
		}
	}
	
	/**
	* Saves Gem to 
	* @access public
	**/
	public function saveGems($gems)
	{
		if (!is_array($gems) || sizeof($gems) == 0)
		{
		    return false;
		}
	}


	
	/**
	* Gets object from cache table
	* @access public
	**/
	public function getObject($name, $type = 'item', $lang = '', $rank = '', $size = '')
	{
	    
	    global $db, $config;

		if (trim($lang) == '')
		{
		    $lang = $config['bbtips_lang'];
		}
		
		$search = $db->sql_like_expression($db->any_char . $db->sql_escape($name) . $db->any_char) ; 
		
		$query_text = 'SELECT itemid, name, search_name, quality, rank, type, lang, icon, icon_size
							 FROM ' . BBTIPS_CACHE_TBL . ' WHERE 
					 (search_name ' . $search . '
					      OR itemid ' . $search . '
						  OR name '. $search;
		$query_text .= ")  AND lang='"  . $lang . "' AND type='"  . $type . "'";
		
		
		if (trim($rank) != '') 
		{ 
		    $query_text .= " AND rank='" . $rank . "'"; 
		}
		
		if (trim($size) != '') 
		{ 
		    $query_text .= " AND icon_size='" . $size . "'";  
		}
		
	    $result = $db->sql_query($query_text);
							
	    if ( $db->sql_affectedrows() == 0)
		{
			// not found in cache, return false
		    $db->sql_freeresult($result);
			return false;
		}
		else
		{
		    $row =  $db->sql_fetchrow($result);
			$db->sql_freeresult($result);		    
			return $row; 
		}
		
	}

	/**
	* Saves an object to 
	* @access public
	**/
	public function saveObject($info)
	{
	    global $db;
	      
		if (!is_array($info) || sizeof($info) == 0 || !isset($info['name']) || !isset($info['itemid']))
		{
			return false;    
		}

		$quality = (array_key_exists('quality', $info)) ? $info['quality'] : 0;
		$rank = (array_key_exists('rank', $info) && $info['rank'] != '') ? $info['rank'] :0 ;
		$icon = (array_key_exists('icon', $info)) ? $info['icon'] : 'NULL';
		$icon_size = (array_key_exists('icon_size', $info)) ? $info['icon_size'] : 'medium';
		
		$sql_ary = array(
    		'itemid'        => $info['itemid'],
    		'name'	        => $info['name'], 
    		'search_name'   => $info['search_name'], 
    		'quality'       => $quality,
    		'rank'          => $rank,
    		'type'          => $info['type'],
    		'lang'          => $info['lang'], 
    		'icon'          => $icon, 
    		'icon_size'     => $icon_size, 
		);

        $sql = 'INSERT INTO ' . BBTIPS_CACHE_TBL . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		
		$result = $db->sql_query($sql);
    	if (!$result)
		{
			global $user;
			$user->add_lang(array('mods/dkp_tooltips'));
			trigger_error(  sprintf($user->lang['BBTOOLTIPS_ERRORCACHING'], $info['name'] , BBTIPS_CACHE_TBL), E_USER_WARNING ) ;
			return false;
		}
		
	}
}
?>