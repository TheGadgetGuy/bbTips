<?php
/**
 * bbdkp WOW edition
 * @package bbDkp-installer
 * @author sajaki9@gmail.com
 * @copyright (c) 2009 bbDkp <http://code.google.com/p/bbdkp/>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * 
 */

define('UMIL_AUTO', true);
define('IN_PHPBB', true);
define('ADMIN_START', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// We only allow a founder install this MOD
if ($user->data['user_type'] != USER_FOUNDER)
{
    if ($user->data['user_id'] == ANONYMOUS)
    {
        login_box('', 'LOGIN');
    }

    trigger_error('NOT_AUTHORISED', E_USER_WARNING);
}

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
    trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

if (!file_exists($phpbb_root_path . 'install/index.' . $phpEx))
{
    trigger_error('Warning! Install directory has wrong name. it must be \'install\'. Please rename it and launch again.', E_USER_WARNING);
}


// The name of the mod to be displayed during installation.
$mod_name = 'bbTips 0.4.3';

/*
* The name of the config variable which will hold the currently installed version
* You do not need to set this yourself, UMIL will handle setting and updating the version itself.
*/
$version_config_name = 'bbdkp_plugin_bbtips_version';

/*
* The language file which will be included when installing
*/
$language_file = 'mods/dkp_tooltips';

/*
* Optionally we may specify our own logo image to show in the upper corner instead of the default logo.
* $phpbb_root_path will get prepended to the path specified
* Image height should be 50px to prevent cut-off or stretching.
*/
//$logo_img = 'images/bbdkp.png';

/*
* Run Options 
*/
$options = array(

'bbtips_lang'   => array('lang' => 'lang', 'type' => 'select', 'function' => 'langoptions', 'explain' => true),
'item'   => array('lang' => 'ITEM', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'itemico'   => array('lang' => 'ITEMICO', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'itemdkp'   => array('lang' => 'ITEMDKP', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'itemset'   => array('lang' => 'ITEMSET', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'craft'   => array('lang' => 'CRAFT', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'quest'   => array('lang' => 'QUEST', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'spell'   => array('lang' => 'SPELL', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'npc'     => array('lang' => 'NPC', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'achievement'   => array('lang' => 'ACHIEVEMENT', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),

);


/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/

/***************************************************************
 * 
 * Welcome to the bbtips installer
 * 
****************************************************************/

$versions = array(
    
    '0.4.0'    => array(

     // Lets add global config settings
	'config_add' => array(

		// source site
		array('bbtips_site', 'wowhead', true),
		array('bbtips_maxparse', 200, true),		
		
		// script source
		array('bbtips_localjs', 1, true),
		// automatic search
		array('bbtips_autsrch', 1, true),

		// language choice
		array('bbtips_lang', 'en', true),

		// custom tooltip settings
		array('bbtips_ttshow', 1, true),
		array('bbtips_type', 'ttbbdkp', true),
		array('bbtips_label', 'Wowhead', true),

	),
     'table_add' => array ( 
        // adding new tables for wowhead-addin to replace itemstats                 
              array($table_prefix . 'bbtips_wowhead_cache', array(
                    'COLUMNS'		=> array(
                       'id'			=> array('INT:8', NULL, 'auto_increment' ),
                       'itemid'		=> array('INT:8', 0 ),
		  			   'name'  		=> array('VCHAR_UNI:255', ''),
		  			   'search_name' => array('VCHAR_UNI:255', ''),
                       'quality'  	=> array('USINT', 0),
					   'rank' 	    => array('USINT', 0),
					   'type'  		=> array('VCHAR:255', ''),
					   'lang'  		=> array('VCHAR:255', ''),               	  
					   'icon'		=> array('VCHAR:255', ''),               	  
					   'icon_size'  => array('VCHAR:255', ''),
                    ),
                    'PRIMARY_KEY'	=> array('id'),
              ),
            ),
            
            array($table_prefix . 'bbtips_wowhead_craftable', array(
                    'COLUMNS'        => array(
                       'itemid'	  => array('INT:10', 0),
                       'name'	      => array('VCHAR_UNI:255', ''),
		  				'search_name' => array('VCHAR_UNI:255', ''),
                       'quality'  	  => array('USINT', 0),
						'lang'  	  => array('VCHAR:255', ''),               	  
						'icon'  	  => array('VCHAR:255', ''),               	  
                    ),
              ),
            ),
            
            array($table_prefix . 'bbtips_wowhead_craftable_reagent', array(
                    'COLUMNS'      => array(
                       'itemid'	=> array('INT:8', 0,), 
                       'reagentof'	=> array('INT:11', 0),        	
		  				'name'      => array('VCHAR_UNI:255', ''),
                       'quantity'  => array('USINT', 0),
						'quality'  	=> array('USINT', 0),        	  
						'icon'  	=> array('VCHAR:255', ''),               	  
                    ),
              ),
            ),
            
            array($table_prefix . 'bbtips_wowhead_craftable_spell', array(
                    'COLUMNS'      => array(
                       'reagentof'	=> array('UINT', 0),        	
                       'spellid'  => array('UINT', 0),
						'name'  	=> array('VCHAR_UNI:255', ''),               	  
                    ),
              ),
            ),
            
            array($table_prefix . 'bbtips_wowhead_itemset', array(
                    'COLUMNS'          => array(
                       'setid'	        => array('INT:8', 0),        	
                       'name'  	    => array('VCHAR_UNI:255', ''),  
            			'search_name'  	=> array('VCHAR_UNI:255', ''),  
                       'lang'          => array('VCHAR:2', ''),
						             	  
                    ),
              ),
            ),            

            array($table_prefix . 'bbtips_wowhead_itemset_reagent', array(
                    'COLUMNS'      => array(
                       'setid'	    => array('INT:8', 0), 
                       'itemid'	=> array('UINT', 0), 
                       'name'  	=> array('VCHAR_UNI:255', ''),  
            			'quality'  	=> array('USINT', 0),
                       'icon'      => array('VCHAR:255', ''),
                    ),
              ),
            ),            
            
            array($table_prefix . 'bbtips_wowhead_npc', array(
                    'COLUMNS'         => array(
                       'npcid'	       => array('INT:8', 0),  
                       'name'  	   => array('VCHAR_UNI:255', ''), 
                       'search_name'  => array('VCHAR_UNI:255', ''), 
            			'lang'          => array('VCHAR:2', ''),
                    ),
              ),
            ),          

         ),

         'custom' => array( 
             'bbdkp_caches' ,
             'insert_bbcodes_wrapper' , 
			 'moduleinstall',              
             
         ) 

    ),
    
    '0.4.1' => array(
    
         'custom' => array( 
             'bbdkp_caches' 
         ) 
    
         
     ), 
    
    '0.4.2' => array(
		//    
     ), 

    '0.4.3' => array(
		//    
     ), 
     
);

// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

/**************************************
 *  
 * function for rendering region list
 * 
 */
function langoptions($selected_value, $key)
{
	global $user;

    $languages = array(
    	'en'     			=> "English", 
    	'de'     			=> "German",     	 
    	'fr'     			=> "French",     	 
    	'es'     			=> "Spanish",     	 
    	'ru'     			=> "Russian",     	 
    );
    
    $default = 'en'; 
	$pass_lang_options = '';
	foreach ($languages as $key => $lang)
	{
		$selected = ($selected_value == $default) ? ' selected="selected"' : '';
		$pass_lang_options .= '<option value="' . $key . '"' . $selected . '>' . $lang . '</option>';
	}

	return $pass_lang_options;
}


/**************************************
 *  
 * global function for clearing cache
 * 
 */
function bbdkp_caches($action, $version)
{
    global $db, $table_prefix, $umil;
    
    $umil->cache_purge();
    $umil->cache_purge('imageset');
    $umil->cache_purge('template');
    $umil->cache_purge('theme');
    $umil->cache_purge('auth');
    
    return 'UMIL_CACHECLEARED';
}

/**
 * inserts bbcodes into database
 *
 * @param unknown_type $action
 * @param unknown_type $version
 */
function insert_bbcodes_wrapper($action, $version)
{
    global $db, $umil; 
   	switch ($action)
	{
		case 'install' :
		case 'update' :
			
			// uninstall then install or reinstall
			delete_bbcodes($action, $version, 'item'); 
			delete_bbcodes($action, $version, 'itemico');
			delete_bbcodes($action, $version, 'itemdkp');
			delete_bbcodes($action, $version, 'itemset');
			delete_bbcodes($action, $version, 'craft');
			delete_bbcodes($action, $version, 'quest');
			delete_bbcodes($action, $version, 'spell');
			delete_bbcodes($action, $version, 'npc');
			delete_bbcodes($action, $version, 'achievement');
			delete_bbcodes($action, $version, 'wowchar');	
			
			if(request_var('item', 0) == 1)
			{
				 
				 insert_bbcodes($action, $version, 'item', 'Item tooltip: example [item gems="40133" enchant="3825"]50468[/item]');
			}
		
			if(request_var('itemico', 0) == 1)
			{
				  
				 insert_bbcodes($action, $version, 'itemico', 'Item icon : example [itemico size="large" gems="40133" enchant="3825"]50468[/itemico]'); 			
			}
		
			if(request_var('itemdkp', 0) == 1)
			{
				  
				 insert_bbcodes($action, $version, 'itemdkp', 'Item DKP'); 			
			}
			
			if(request_var('itemset', 0) == 1)
			{
				 
				 insert_bbcodes($action, $version, 'itemset', 'Item Set'); 			
			}
		
			if(request_var('craft', 0) == 1)
			{
				 
				 insert_bbcodes($action, $version, 'craft', 'Craftable Items'); 			
			}
			
			if(request_var('quest', 0) == 1)
			{
				 
				 insert_bbcodes($action, $version, 'quest' , 'Quest Tag'); 			
			}
			
			if(request_var('spell', 0) == 1)
			{
				 
				 insert_bbcodes($action, $version, 'spell' , 'Spell tooltip'); 			
			}
			
			if(request_var('npc', 0) == 1)
			{
				 
				 insert_bbcodes($action, $version, 'npc' , 'NPC tooltip'); 			
			}
			
			if(request_var('achievement', 0) == 1)
			{
				 
				 insert_bbcodes($action, $version, 'achievement', 'Achievement tooltip'); 			
			}
		
			return array('command' => array('UMIL_BBCODE_ADDED') , 'result' => 'SUCCESS'); 
				
	      break;

		case 'uninstall' :
			delete_bbcodes($action, $version, 'item'); 
			delete_bbcodes($action, $version, 'itemico');
			delete_bbcodes($action, $version, 'itemdkp');
			delete_bbcodes($action, $version, 'itemset');
			delete_bbcodes($action, $version, 'craft');
			delete_bbcodes($action, $version, 'quest');
			delete_bbcodes($action, $version, 'spell');
			delete_bbcodes($action, $version, 'npc');
			delete_bbcodes($action, $version, 'achievement');
			return array('command' => 'UMIL_BBCODE_REMOVED', 'result' => 'SUCCESS'); 												
		    
		  break; 
        
	}
	

}

/**
 * inserts bbcodes into database
 *
 * @param string $action
 * @param string $version
 */
function insert_bbcodes($action, $version, $tag, $helpline)
{	
	global $db, $user, $template, $cache;
	global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		// Set up mode-specific vars
		// build each field for the sql query
		$bbcode_match 	=	"[$tag]{SIMPLETEXT}[/$tag]";
		$bbcode_tpl 	=	$bbcode_match;// same as match
			
		$sql = 'SELECT count(*) as checkcount FROM ' . BBCODES_TABLE . 
			" WHERE LOWER(bbcode_tag) = '" . $db->sql_escape(strtolower($tag)) . "'";
		$result = $db->sql_query($sql);
	    $checkcount = (int) $db->sql_fetchfield('checkcount');
	    
	    if ($checkcount >= 1)
	    {
	    	return; 
	    }
	    
		$db->sql_freeresult($result);
		
		// Include the bbcode class
		if (!class_exists('acp_bbcodes'))
		{
			require("{$phpbb_root_path}includes/acp/acp_bbcodes.$phpEx");
		}
		$acp_bbcodes = new acp_bbcodes;
		
		$data = $acp_bbcodes->build_regexp($bbcode_match, $bbcode_tpl);	
		
		// assign the other variables
		$sql_ary = array(
			'bbcode_tag'				=> $tag,
			'bbcode_match'				=> $bbcode_match,
			'bbcode_tpl'				=> $bbcode_tpl,
			'display_on_posting'		=> 1,
			'bbcode_helpline'			=> $helpline,
			'first_pass_match'			=> $data['first_pass_match'],
			'first_pass_replace'		=> $data['first_pass_replace'],
			'second_pass_match'			=> $data['second_pass_match'],
			'second_pass_replace'		=> $data['second_pass_replace']
		);
		
		// get max bbcodeid
		$sql = 'SELECT MAX(bbcode_id) as max_bbcode_id
			FROM ' . BBCODES_TABLE;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
	
		if ($row)
		{
			$bbcode_id = $row['max_bbcode_id'] + 1;
	
			// Make sure it is greater than the core bbcode ids...
			if ($bbcode_id <= NUM_CORE_BBCODES)
			{
				$bbcode_id = NUM_CORE_BBCODES + 1;
			}
		}
		else
		{
			$bbcode_id = NUM_CORE_BBCODES + 1;
		}
	
		if ($bbcode_id > 1511)
		{
			trigger_error($user->lang['TOO_MANY_BBCODES'] . adm_back_link($this->u_action), E_USER_WARNING);
		}
		
		$sql_ary['bbcode_id'] = (int) $bbcode_id;
	
		$db->sql_query('INSERT INTO ' . BBCODES_TABLE . $db->sql_build_array('INSERT', $sql_ary));
		$cache->destroy('sql', BBCODES_TABLE);
		
		$lang = 'BBCODE_ADDED';
		$log_action = 'LOG_BBCODE_ADD';
		
		add_log('admin', $log_action, $data['bbcode_tag']);
					
}

/**
 * deletes bbcodes from database
 *
 * @param string $action
 * @param string $version
 */
function delete_bbcodes($action, $version, $tag)
{	
	global $db, $user, $template, $cache;
	global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	
	switch ($action)
	{
		case 'uninstall' :
			$sql = 'SELECT bbcode_id FROM ' . BBCODES_TABLE . " WHERE lower(bbcode_tag) = '" . $db->sql_escape(strtolower($tag)) . "'"; 
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			if ($row)
			{
				$db->sql_query('DELETE FROM ' . BBCODES_TABLE . " WHERE bbcode_id = ".  $row['bbcode_id']);
				$cache->destroy('sql', BBCODES_TABLE);
				add_log('admin', 'LOG_BBCODE_DELETE', $tag);
			}
			break; 
	}


}


/**
 *  module installer
 */
function moduleinstall($action, $version)
{
    global $user, $config, $db, $table_prefix, $umil; 
	switch ($action)
	{
		case 'install' :
		case 'update' :
			switch ($version)
			{
				case '0.4.3':
				// check if bbdkp is installed
				if (isset($config['bbdkp_version']))
				{
					// bbdkp found. install module under the raids module

					$umil->module_add('acp', 'ACP_DKP_RAIDS', array(
					    'module_basename'   => 'dkp_bbtooltips',
					    'modes'             => array('bbtooltips'),
						'module_auth'       => 'acl_a_dkp',
						'module_langname'	=> 'ACP_DKP_DKPTOOLTIPS', 
						'module_mode'       => 'bbtooltips', 
						));
						
						
				}
				else
				{	
					$umil->module_add(array(
				    // Add a new category named ACP_CAT_BBTIPS to ACP_CAT_DOT_MODS
				    array('acp', 'ACP_CAT_DOT_MODS', 'ACP_CAT_BBTIPS'),
    				array('acp', 'ACP_CAT_BBTIPS', array(
        			     'module_basename'       => 'dkp_bbtooltips',
     	  			     'modes'                 => array('bbtooltips'),
    					 'module_auth'       	=> 'acl_a_board',
    					 'module_langname'	=> 'ACP_DKP_DKPTOOLTIPS', 
						 'module_mode'       => 'bbtooltips', 
     			   		 ))
     			   	));
				}
        	}
        	break;
        	
        case 'uninstall' :
        		/* check if bbdkp is installed*/
				if (isset($config['bbdkp_version']))
				{
					$umil->module_remove('acp', 'ACP_DKP_RAIDS', array(
					    'module_basename'   => 'dkp_bbtooltips',
					    'modes'             => array('bbtooltips'),
						'module_auth'       	=> 'acl_a_dkp',
						'module_langname'	=> 'ACP_DKP_DKPTOOLTIPS', 
						'module_mode'       => 'bbtooltips', 
						));					
					
				}
				else
				{	
					
					$umil->module_remove('acp', 'ACP_CAT_BBTIPS', array(
					    'module_basename'   => 'dkp_bbtooltips',
					    'modes'             => array('bbtooltips'),
						'module_auth'       	=> 'acl_a_board',
						'module_langname'	=> 'ACP_DKP_DKPTOOLTIPS', 
						'module_mode'       => 'bbtooltips', 
						));
						
					$umil->module_remove('acp', 'ACP_CAT_DOT_MODS', 'ACP_CAT_BBTIPS');
				}
        		
        
        
    }

}
?>