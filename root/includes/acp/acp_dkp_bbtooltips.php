<?php
/**
* This class manages bbtips 
* 
* @package bbDkp.acp
* @author sajaki9@gmail.com
* @version $Id$
* @copyright (c) 2009 bbdkp http://code.google.com/p/bbdkp/
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* 
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}
if (! defined('EMED_BBDKP')) 
{
	$user->add_lang ( array ('mods/dkp_admin' ));
	trigger_error ( $user->lang['BBDKPDISABLED'] , E_USER_WARNING );
}

require ($phpbb_root_path . 'includes/acp/acp_dkp.' . $phpEx); 

class acp_dkp_bbtooltips extends acp_dkp
{
   var $u_action;
   var $new_config;
   
   function main($id, $mode)
   {
      global $db, $user, $auth, $template,  $sid, $cache;
      global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx; 
     
      $link = '<br /><a href="'.append_sid("index.$phpEx", "i=dkp&amp;mode=mainpage") . '"><h3>Return to Index</h3></a>'; 
      
	  $user->add_lang(array('mods/dkp_tooltips', 'mods/dkp_admin'));
	
      switch($mode)
      {
         case 'bbtooltips':

         	/*** note there are more $config entries for bbtips but they aren't user-configurable yet */
             
            $submit1 = (isset($_POST['site_submit'])) ? true : false;
            $submit2 = (isset($_POST['arm_submit'])) ? true : false;
                        
            $gamesites = array(
				'wowhead'   		=> "Wowhead"
				);
            
			$langlist = array(
				'en'   => "English", 
				'fr'   => "French",
            	'de'   => "German",
			    'es'   => "Spanish",
			    'ru'   => "Russian",
				);
			
		    $regions = array(
		    	'EU'     			=> "WoW European region", 
		    	'US'     			=> "WoW US region",     	 
		    );
		    
			if ($submit1)
			{
			    set_config('bbtips_maxparse', request_var('maxparse', 0), true );				    
				set_config('bbtips_lang', request_var('site_lang', ''), true ); 
			    set_config('bbtips_localjs', request_var('localjs', 0), true );
			    set_config('bbdkp_default_region', request_var('region', ''), true );				    
				set_config('bbdkp_default_realm', request_var('realm', ''), true ); 

				$cache->destroy('config');
			    trigger_error($user->lang['BBTOOLTIPS_SETTINGSAVED']. $link, E_USER_NOTICE);
			}
			
			else 
			{
                foreach($langlist as $tt_lang => $tt_langname)
    		    {
        			$template->assign_block_vars ( 'site_lang_row', array (
    	    			'VALUE' => $tt_lang, 
    		    		'SELECTED' => ($tt_lang == $config['bbtips_lang'] ) ? ' selected="selected"' : ''  ,
    			    	'OPTION' =>   $tt_langname  ));  
    		    }
			    
    		    foreach($regions as $regionid => $regionnname)
    		    {
        			$template->assign_block_vars ( 'region_row', array (
    	    			'VALUE' => $regionid, 
    		    		'SELECTED' => ($regionid == $config['bbdkp_default_region'] ) ? ' selected="selected"' : ''  ,
    			    	'OPTION' =>   $regionnname  ));  
    		    }
    		    
                $template->assign_vars(array(
                	'F_BBTOOLTIPS'   	=> append_sid("index.$phpEx", "i=dkp_bbtooltips&amp;mode=bbtooltips&amp;"),
                 	'MAXPARSE'			=> $config['bbtips_maxparse'],
                 	'LOCALJS_YES_CHECKED' => ( $config['bbtips_localjs'] == '1' ) ? ' checked="checked"' : '',
    				'LOCALJS_NO_CHECKED' => ( $config['bbtips_localjs'] == '0' ) ? ' checked="checked"' : '',
                	'REALM_NAME'		 => $config['bbdkp_default_realm'],  
                 )
    			);
			}
				        
			$this->page_title = $user->lang['BBTOOLTIPS'];
			$this->tpl_name = 'dkp/acp_'. $mode;
            break;
	        
      }

   }
   

   
}

?>
