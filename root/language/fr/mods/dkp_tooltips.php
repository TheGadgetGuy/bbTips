<?php
/**
 * bbdkp admin language file [German]
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
    'UMIL_BBCODE_ADDED' => 'BBCodes ajoutés',
    'UMIL_BBCODE_REMOVED' => 'BBCodes supprimés',

    'ACP_BBTOOLTIPS' => 'Réglages Popup ',
    'BBTOOLTIPS' => 'bbTips 0.3.7',
    'BBTOOLTIPS_EXPLAIN' => 'BBCode pour Wowhead Tooltips',

    'BBTIPS_SETTING' => 'Règlages bbTips',
    'BBTIPS_MAXPARSE' => 'Analyse maximum',
    'BBTIPS_MAXPARSE_EXPLAIN' => 'Nombre maximal de BBCodes analyséeds par page (0=illimité). Si immilité ou trop haut (>200), impacte négatif sur vitesse. ',

    'ITEM' => 'Installer BBCode [item]',
    'ITEMICO' =>  'Installer BBCode [itemico]',
    'ITEMDKP' =>  'Installer BBCode [itemdkp]',
    'ITEMSET' =>  'Installer BBCode [itemset]',
    'CRAFT' => 'Installer BBCode [craft]',
    'QUEST' => 'Installer BBCode [quest]',
    'SPELL' => 'Installer BBCode [quest]',
    'NPC' => 'Installer BBCode [npc]',
    'ACHIEVEMENT' => 'Installer BBCode [achievement]',
    'CHARACTER' =>  'Installer BBCode [wowchar]',

    'BBTOOLTIPS_LOCALJS' => 'Supprime Script Wowhead',
    'BBTOOLTIPS_LOCALJS_EXPLAIN' => 'mettre ceci à  ’non’ hätera la vitesse de chargement.',
    'BBTOOLTIPS_LANG' => 'Langue',
    'BBTOOLTIPS_LANG_EXPLAIN' => 'bbTips s’addressera au site Wowhead localisé',

    'BBTOOLTIPS_ARM' => 'Règleages BBCode [wowchar]',
    'BBTOOLTIPS_REA' => 'Royaume',
    'BBTOOLTIPS_REG' => 'Région',



));

?>
