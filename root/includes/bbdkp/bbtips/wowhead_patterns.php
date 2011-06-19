<?php
/**
* bbdkp-wowhead Link Parser v3 - Pattern Class
*
* @package bbDkp.includes
* @version $Id $
* @Copyright (c) 2008 Adam Koch
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
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

class wowhead_patterns
{
	// variable for each pattern
	public $patterns = array();

	public function wowhead_patterns()
	{
        global $phpbb_root_path, $user, $phpEx;
		$path = $phpbb_root_path . 'styles/' . $user->theme['template_path'] . '/template/bbtips/';
		if (!$opendir = @opendir($path) )
		{
			echo $path;
			trigger_error('Failed to open templates directory.  Please make sure the permissions were set properly.');
		}
		else
		{
			while (false !== ($file = readdir($opendir)))
			{
				if (substr($file, strpos($file, '.') + 1) == 'html')
				{
					$filename = (strpos($file, 'php') !== false) ? str_replace('.php', '', $file) : str_replace('.html', '', $file);
					$this->patterns[$filename] = @file_get_contents($path . $file);
				}
			}
		}

	}

	function pattern($name)
	{
		return $this->patterns[$name];
	}

	function close()
	{
		unset($this->patterns);
	}
}
?>