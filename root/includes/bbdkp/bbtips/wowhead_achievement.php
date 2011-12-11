<?php
/**
* bbdkp-wowhead Achievement Extension
*
* @package bbDkp.includes
* @version $Id $
* @copyright 2010 bbdkp <http://code.google.com/p/bbdkp/>
* @author: Adam "craCkpot" Koch (admin@crackpot.us) -- 
* @author: Sajaki (sajaki9@gmail.com)
*
**/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class wowhead_achievement extends wowhead
{
	public $lang;
	public $patterns;
	private $args; 

	public function wowhead_achievement($arguments = array())
	{
		global $config, $phpEx, $phpbb_root_path; 
	    
        if (!class_exists('wowhead_patterns')) 
        {
			require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_patterns.' . $phpEx); 
        }
        $this->patterns = new wowhead_patterns();
        $this->args = $arguments;
		$this->lang = $config['bbtips_lang'];
	}

	public function parse($name)
	{
	    global $phpbb_root_path, $phpEx;
	    
		if (trim($name) == '')
		{
			return false;
		}
		
		if (!class_exists('wowhead_cache')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_cache.' . $phpEx); 
        }
		$cache = new wowhead_cache();


		if (!$result = $cache->getObject($name, 'achievement', $this->lang))
		{
			// not in cache
			if (is_numeric($name))
			{
				$result = $this->_getAchievementByID($name);
			}
			else
			{
				$result = $this->_getAchievementByName($name);
			}

			if (!$result)
			{
				// not found
				
				return $this->_notfound('achievement', $name);
			}
			else
			{
				$cache->saveObject($result);
				
				return $this->_generateHTML($result, 'achievement');
			}
		}
		else
		{
			
			return $this->_generateHTML($result, 'achievement');
		}
	}

	/**
	* Queries Wowhead for Achievement info by ID
	* @acess private
	**/
	private function _getAchievementByID($id)
	{
		if (!is_numeric($id))
		{
		    return false;
		}


		$this->make_url($id, 'achievement');
		$data = $this->gethtml($id, 'achievement');

		if ($data == '$WowheadPower.registerAchievement(1337, 25, {});')
		{
			return false;
		}
		else
		{
			if (preg_match('#<b class="q">(.+?)</b>#s', $data, $match))
			{
				return array(
						'name'			=>	stripslashes($match[1]),
						'itemid'		=>	$id,
						'search_name'	=>	$id,
						'type'			=>	'achievement',
						'lang'			=>	$this->lang

				);
			}
			else
			{
				return false;
			}
		}
	}

	/**
	* Queries Wowhead for Achievement by Name
	* @access private
	**/
	private function _getAchievementByName($name)
	{
		global $phpbb_root_path, $phpEx;
		
        if (trim($name) == '')
		{
			return false;
		}

		$this->make_url($name, 'achievement');
		$data = $this->gethtml($name, 'achievement');
		
		if ( !class_exists('simple_html_dom_node')) 
        {
            include ($phpbb_root_path . 'includes/bbdkp/bbtips/simple_html_dom.' . $phpEx); 
        }

		$html = str_get_html ($data, $lowercase = true);
		
		// get name from meta tag
		$element = $html->find('meta[property=og:title]'); 
		$achievementname = "";
		foreach($element as $attr)
		{
			$achievementname = (string) $attr->getattribute('content');
		}
		
		// get link from meta tag
		$element = $html->find('link[rel=canonical]'); 
		foreach($element as $attr)
		{
			$achievementlink = (string) $attr->getattribute('href');
			// content="http://www.wowhead.com/achievement=4874/breaking-out-of-tol-barad"
			$linkarray = explode("/" , $achievementlink, 5);
			$achid = str_replace("achievement=", "", $linkarray[3])  ;
		}
		
		$html->clear(); 
        unset($html);
		
		if($name === $achievementname)
		{
			//success
			return array(
				'name'			=>	$achievementname,
				'search_name'	=>	$achievementname,
				'itemid'		=>	$achid,
				'type'			=>	'achievement',
				'lang'			=>	$this->lang
			);
					
		}
		else 
		{
			// not found
			return false;
		}
		
	}
	
	/**
	* Generates HTML for link
	* @access private
	**/
	private function _generateHTML($info, $type, $size = '', $rank = '', $gems = '')
	{
	    $info['link'] = $this->_generateLink($info['itemid'], $type);
		return $this->_replaceWildcards($this->patterns->pattern($type), $info);
	}
	
	
}
?>