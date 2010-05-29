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
	var $patterns = array();

	function wowhead_patterns()
	{
        global $phpbb_root_path, $phpEx;

		if (!$opendir = @opendir($phpbb_root_path . 'includes/bbdkp/bbtips/patterns/'))
		{
			trigger_error('Failed to open templates directory.  Please make sure the permissions were set properly.');
		}
		else
		{
			while (false !== ($file = readdir($opendir)))
			{
				if (substr($file, strpos($file, '.') + 1) == 'html')
				{
					$filename = (strpos($file, 'php') !== false) ? str_replace('.php', '', $file) : str_replace('.html', '', $file);
					$this->patterns[$filename] = @file_get_contents($phpbb_root_path . 'includes/bbdkp/bbtips/patterns/' . $file);
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