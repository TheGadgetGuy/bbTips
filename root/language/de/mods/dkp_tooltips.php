<?php
/**
 * bbdkp admin language file [German]
 * @author sajaki@bbdkp.com
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
    'UMIL_BBCODE_ADDED' => 'BBCodes hinzugefügt',
    'UMIL_BBCODE_REMOVED' => 'BBCodes entfernt',

    'ACP_BBTOOLTIPS' => 'bbTips Einstellungen',
    'BBTOOLTIPS' => 'bbTips 0.3.7 Plugin',
    'BBTOOLTIPS_EXPLAIN' => 'Tooltips von Wowhead',

    'BBTIPS_SETTING' => 'Tooltip Einstellungen',
    'BBTIPS_MAXPARSE' => 'Maximum Parsing',
    'BBTIPS_MAXPARSE_EXPLAIN' => 'Maximum number of bbcode parsing on a given page. Setting this to 0 (unlimited) or too high (>200) may cause excessive load times which will cause PHP execution to timeout.',

    'ITEM' => 'Installiere BBCode [item]',
    'ITEMICO' =>  'Installiere BBCode [itemico]',
    'ITEMDKP' =>  'Installiere BBCode [itemdkp]',
    'ITEMSET' =>  'Installiere BBCode [itemset]',
    'CRAFT' => 'Installiere BBCode [craft]',
    'QUEST' => 'Installiere Wowhead [quest]',
    'SPELL' => 'Installiere BBCode [spell]',
    'NPC' => 'Installiere NPC Tooltip BBCode',
    'ACHIEVEMENT' => 'Installiere BBCode [achievement]',
    'CHARACTER' =>  'Installiere BBCode [wowchar]',

    'BBTOOLTIPS_LOCALJS' => 'Entferne wowheadscript',
    'BBTOOLTIPS_LOCALJS_EXPLAIN' => 'Setzen dieser Einstellung auf ’nein’ beschleunigt den Aufbau der Seite. ',
    'BBTOOLTIPS_LANG' => 'Tooltip Sprache',
    'BBTOOLTIPS_LANG_EXPLAIN' => 'bbtips wird die Tooltips in der ausgewählten Sprache anzeigen.',

    'BBTOOLTIPS_ARM' => 'Armory tooltip Standarts',
    'BBTOOLTIPS_REG' => 'Armory Region',
    'BBTOOLTIPS_REA' => 'Realm',

	'ITEMNOTFOUND' => '%s "%s" wurde nicht gefunden',
	'INSERTFAILED' => 'Einführung von %s in die Datenbank gescheitert.',
	'BBTOOLTIPS_SETTINGSAVED' => 'BBTips einstellungen gespeichert', 

));

?>
