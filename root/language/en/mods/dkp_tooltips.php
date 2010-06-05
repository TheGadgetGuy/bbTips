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
'UMIL_BBCODE_ITEM_ADDED' => 'Added item bbcode', 
'UMIL_BBCODE_ITEM_REMOVED' => 'Not Removed item bbcode',
'UMIL_BBCODE_ITEMICO_ADDED' => 'Added itemico bbcode', 
'UMIL_BBCODE_ITEMICO_REMOVED' => 'Not Removed itemico bbcode',
'UMIL_BBCODE_ITEMDKP_ADDED' => 'Added itemdkp bbcode', 
'UMIL_BBCODE_ITEMDKP_REMOVED' => 'Not Removed itemdkp bbcode',
'UMIL_BBCODE_CRAFT_ADDED' => 'Added craft bbcode', 
'UMIL_BBCODE_CRAFT_REMOVED' => 'Not Removed craft bbcode',
'UMIL_BBCODE_QUEST_ADDED' => 'Added quest bbcode', 
'UMIL_BBCODE_QUEST_REMOVED' => 'Not Removed quest bbcode',
'UMIL_BBCODE_SPELL_ADDED' => 'Added spell bbcode', 
'UMIL_BBCODE_SPELL_REMOVED' => 'Not Removed spell bbcode',
'UMIL_BBCODE_ACHIEVEMENT_ADDED' => 'Added Achievement bbcode', 
'UMIL_BBCODE_ACHIEVEMENT_REMOVED' => 'Not Removed Achievement bbcode',
'UMIL_BBCODE_ARMORY_ADDED' => 'Added Armory bbcode', 
'UMIL_BBCODE_ARMORY_REMOVED' => 'Not Removed Armory bbcode',
'UMIL_BBCODE_PROFILE_ADDED' => 'Added Profile bbcode', 
'UMIL_BBCODE_PROFILE_REMOVED' => 'Not Removed Profile bbcode',
'UMIL_BBCODE_GUILD_ADDED' => 'Added Guild bbcode', 
'UMIL_BBCODE_GUILD_REMOVED' => 'Not Removed Guild bbcode',

'ACP_BBTOOLTIPS' => 'Popup Configuration',
'BBTOOLTIPS' => 'bbTips 0.3.3 Plugin', 
'BBTOOLTIPS_EXPLAIN' => 'bbDKP Tooltips from Wowhead', 

'BBTIPS_SETTING' => 'Tooltip Settings', 
'BBTIPS_MAXPARSE' => 'Maximum Parsing',
'BBTIPS_MAXPARSE_EXPLAIN' => 'Maximum number of bbcode parsing on a given page. Setting this to 0 (unlimited) or too high (>200) may cause excessive load times which will cause PHP execution to timeout.', 

'ITEM' => 'Install Wowhead Item  Tooltip bbCode',
'ITEMICO' =>  'Install Wowhead Item icon Tooltip bbCode',
'ITEMDKP' =>  'Install ItemDkp Tooltip bbCode', 
'CRAFT' => 'Install Wowhead Craftables Tooltip bbCode',
'QUEST' => 'Install Wowhead Quest Tooltip bbCode', 
'SPELL' => 'Install Wowhead Spell Tooltip bbCode', 
'ACHIEVEMENT' => 'Install Wowhead Achievement Tooltip bbCode', 
'CHARACTER' =>  'Install Warcraft character Overlay bbCode', 


'BBTOOLTIPS_LOCALJS' => 'Remote wowheadscript', 
'BBTOOLTIPS_LOCALJS_EXPLAIN' => 'Setting this to \'no\' will speed up pageloads. ', 
'BBTOOLTIPS_LANG' => 'Tooltips Language',
'BBTOOLTIPS_LANG_EXPLAIN' => 'bbtips will show tooltips in the language you choose.', 

'BBTOOLTIPS_ARM' => 'Armory tooltip defaults', 
'BBTOOLTIPS_REG' => 'Armory Region', 
'BBTOOLTIPS_REA' => 'Realm', 



));

?>
