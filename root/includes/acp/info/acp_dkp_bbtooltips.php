<?php
/**
* This class manages Itemstats 
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

/**
* @package module_install
*/
class acp_dkp_bbtooltips_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_dkp_bbtooltips',
			'title'		=> 'ACP_DKP_DKPTOOLTIPS',
			'version'	=> '1.0.0',
			'modes'		=> array(
    			'bbtooltips'	=> array('title' => 'ACP_DKP_DKPTOOLTIPS', 'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_ITEM')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}


?>
