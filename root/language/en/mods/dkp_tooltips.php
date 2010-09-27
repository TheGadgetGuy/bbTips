<?php
/**
 * bbdkp admin language file [English]
 * @author Sajaki@betenoire
 * @package bbDkp
 * @copyright 2009 bbdkp <http://code.google.com/p/bbdkp/>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * 
 */

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

// DKP
$lang = array_merge($lang, array(

// installer
'UMIL_BBCode_ADDED' => 'Added BBCodes', 
'UMIL_BBCode_REMOVED' => 'Removed BBCodes', 

'ACP_BBTOOLTIPS' => 'Popup Configuration',
'BBTOOLTIPS' => 'bbTips 0.3.7 Plugin', 
'BBTOOLTIPS_EXPLAIN' => 'Tooltips from Wowhead', 

'BBTIPS_SETTING' => 'Tooltip Settings', 
'BBTIPS_MAXPARSE' => 'Maximum Parsing',
'BBTIPS_MAXPARSE_EXPLAIN' => 'Maximum number of BBCode parsing on a given page. Setting this to 0 (unlimited) or too high (>200) may cause excessive load times which will cause PHP execution to timeout.', 

'ITEM' => 'Install Wowhead Item Tooltip BBCode',
'ITEMICO' =>  'Install Wowhead Item icon Tooltip BBCode',
'ITEMDKP' =>  'Install ItemDkp Tooltip BBCode', 
'ITEMSET' =>  'Install ItemSet BBCode',
'CRAFT' => 'Install Wowhead Craftables Tooltip BBCode',
'QUEST' => 'Install Wow	head Quest Tooltip BBCode', 
'SPELL' => 'Install Wowhead Spell Tooltip BBCode', 
'NPC' => 'Install NPC Tooltip BBCode',
'ACHIEVEMENT' => 'Install Wowhead Achievement Tooltip BBCode', 
'CHARACTER' =>  'Install Warcraft character Overlay BBCode', 

'BBTOOLTIPS_LOCALJS' => 'Remote wowheadscript', 
'BBTOOLTIPS_LOCALJS_EXPLAIN' => 'Setting this to ’no’ will speed up pageloads. ', 
'BBTOOLTIPS_LANG' => 'Tooltips Language',
'BBTOOLTIPS_LANG_EXPLAIN' => 'bbtips will show tooltips in the language you choose.', 

'BBTOOLTIPS_ARM' => 'Armory tooltip defaults', 
'BBTOOLTIPS_REG' => 'Armory Region', 
'BBTOOLTIPS_REA' => 'Realm', 



));

?>
