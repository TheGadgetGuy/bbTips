<?php
/**
* ParseWowhead :create itemstats tooltips using wowhead
* started: 31/05/2007
* @author : Frank Matheron fenuzz@gmail.com 
* @author : sajaki sajaki9@gmail.com 
*
* @package bbDkp.includes
* @version $Id$
* @copyright (c) 2007, 2008 bbDKP 
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
 * The main interface to the Wowhead parser
 *
 */
class ParseWowhead
{
 	 function wow_url($lang)
	 {
		 switch ($lang) 
		 {
				case "en":
					return 'http://www.wowhead.com';
				case "de":
					return 'http://de.wowhead.com';
				case "fr":
					return 'http://fr.wowhead.com';
				case "es":
					return 'http://es.wowhead.com';
				case "es":
					return 'http://ru.wowhead.com';
				default:
					return 'http://www.wowhead.com';
			}
	 }

	 /**
	 * Attempts to retrieve data for the specified item from Wowhead 
	 * **
	 * // not used
	 * @param char $name
	 * @return html
	 */
	function getItem($name)
    {
        global $config; 
  		// Ignore blank names.
		$name = trim(html_entity_decode($name, ENT_QUOTES));
		if (empty($name)) 
		{ 
		    return null; 
		}
		
		$item = array('name' => $name);
		$fixed_name = implode(' ', preg_split ("/[\s\+]+/", urldecode(urldecode($name))));
		$encoded_name = urlencode($fixed_name);
		$encoded_name = str_replace('+' , '%20' , $encoded_name);
		$encoded_name =  "/?item=". $encoded_name; 
		$item_url = $this->wow_url($config['bbdkp_it_lang']) . $encoded_name . '&xml';
		$itemxml = $this->getItemData($item_url);
		$itemhtml = $this->buildTooltip($itemxml);        
		return $itemhtml;

    }

	/**
	 * Attempts to retrieve data for the specified item from Wowhead by its wowhead itemid
	 *
	 * @param int $item_id
	 * @return getItemData
	 */
	function getItemId($item_id)
	{
	    global $config; 
		$item_url = $this->wow_url( $config['bbdkp_it_lang']) . '/?item=' .$item_id . '&xml';
		return $this->buildTooltip($this->getItemData($item_url));
	}

	/**
	 * Parses the XML representation of the item data
	 * requires PHP > 5.1 and simplexml extension. (LIBXML_NOCDATA requires LibXML  > 2.6) 
	 * $item_url = the URL to the item data (XML).
	 */
	function getItemData($item_url)
	{
		$xml_item_data = bbDkp_Admin::read_php($item_url, 0, 0);
		// to get access to cdata
		//$item_data = simplexml_load_string($xml_item_data, 'SimpleXMLElement', LIBXML_NOCDATA);
		$item_data = simplexml_load_string($xml_item_data);
		if (isset($item_data->error[0])) 
		{
			return false;
		}
		return $item_data;
	}
	
	/**
	 * Builds the tooltip using the parsed XML data.
	 * $item_data = parsed XML item data.
	 */
	function buildTooltip($item_data) 
	{
	    
	    global $config;
	    
		$item = array();

		if (!$item_data) 
		{
			unset($item['link']);
			return $item;
		}
		
		// set item data
		$item['id'] =   (string) $item_data->item['id']; 
		$item['name'] = (string) $item_data->item->name;
		$item['lang'] = $config['bbdkp_it_lang'];
		$item['link'] = (string) $item_data->item->link;
		$item['icon'] = (string) strtolower($item_data->item->icon);

		// set the item color based on the item quality
		switch ($item_data->item->quality['id']) 
		{
			case 0:
				$item['color'] = 'greyname';
				break;
			case 1:
				$item['color'] = 'whitename';
				break;
			case 2:
				$item['color'] = 'greenname';
				break;
			case 3:
				$item['color'] = 'bluename';
				break;
			case 4:
				$item['color'] = 'purplename';
				break;
			case 5:
				$item['color'] = 'orangename';
				break;
			case 6:
				$item['color'] = 'redname';
				break;
			default:
				$item['color'] = 'greyname';
				break;
		}    
		
		// create the tooltip html
		if (substr((string) $item_data->item->htmlTooltip, 0, 7) != '<table>') 
		{
			$item['html'] = '<table><tr><td>' . (string)$item_data->item->htmlTooltip . '</td></tr></table>';
		} 	
		else 
		{
			$item['html'] = (string)$item_data->item->htmlTooltip;
		}
		
		/* remove tooltips from the tooltip */
		$item['html'] = preg_replace('/<span class="q2 tip" onmouseover=".+?".+?>(.+?)<\/span>/', '<span class="q2">\\1</span>', $item['html']);
		
		/* remove the width attributes from the tooltips, they mess the tooltip up in IE */
	    //	$item['html'] = str_replace(' width="100%"', '', $item['html']);
		
		/* tooltip title/item name links to its wowhead page */
		$item['html'] = str_replace($item['name'], '<a href=\'' . $item['link'] . '\' target=\'_new\'>' . (string) $item_data->item->name . '</a>', $item['html']);
		
		/* add escape slashes this is necessary for overlib */
		$item['html'] = str_replace('"', '\'', $item['html']);
		
		/* place the tooltip content html into the tooltip template */
		$template_html = trim(file_get_contents(WOWHEAD_TEMPLATE));
		
		$item['html'] = str_replace('{ITEM_HTML}', $item['html'], $template_html);		

		return $item;	
	}

	/**
	 * Cleans up resources used by this object.
	 *
	 */
	function close() 
	{
		
	}
}
?>
