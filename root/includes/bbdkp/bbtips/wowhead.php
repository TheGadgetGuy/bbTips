<?php
/**
* bbdkp-wowhead Link Parser v3 
* @author sajaki@gmail.com
* @package bbDkp.includes
* @version $Id$
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}
	 
/**
* Wowhead Base Class
* @package wowhead
**/
class wowhead
{
	var $patterns; 
	
	function wowhead()
	{
		global $phpEx, $phpbb_root_path; 
		
		if (!class_exists('wowhead_patterns')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_patterns.' . $phpEx); 
        }
        $this->patterns = new wowhead_patterns();
		
	}
	
	/**
	* Attempts to read URL and return content
	* @access private
	* 
	* @param $url
	* @param $type default 'item'
	* @param $headers
	* 
	**/
	function _read_url($url, $type = 'item', $headers = true)
	{
		// build the url depending on bbcode
		switch ($type)
		{
			case 'npc':
			case 'itemset':
				if(is_numeric($url))
				{
					//parse page directly
					$built_url = $this->_getDomain() . '/' . $type . '=' . $url; 
				}
				else 
				{
					//use search and parse page
					$built_url = $this->_getDomain() . '/search?q=' . $this->_convert_string($url);
				}
				$html_data = $this->read_php($built_url, 1, 0 );
				break;
			case 'spell':
			case 'quest':  
			case 'achievement':	
				if(is_numeric($url))
				{
					//parse page directly
					$built_url = $this->_getDomain() . '/' . $type . '=' . $url . '&power'; 
				}
				else 
				{
					//use search and parse page
					$built_url = $this->_getDomain() . '/search?q=' . $this->_convert_string($url);
				}
				$html_data = $this->read_php($built_url, 1, 0 );
				break;
			case 'item':      
		    case 'itemico':   
		    case 'itemdkp':   
				if(is_numeric($url))
				{	
					$built_url = $this->_getDomain() . '/item=' . $this->_convert_string($url) . '&xml';
					$html_data = $this->read_php($built_url, 0, 0 );
				}
				else 
				{
					//use search and parse page
					$built_url = $this->_getDomain() . '/search?q=' . $this->_convert_string($url);
					$html_data = $this->read_php($built_url, 1, 0 );
				}
				break;
			case 'craftable':
			default:
				//xml
				$built_url = $this->_getDomain() . '/item=' . $this->_convert_string($url) . '&xml';
				$html_data = $this->read_php($built_url, 0, 0 );
				break;
		}
		return $html_data;
	
	}

	/**
	* Gets Gem Info
	* @access private
	**/
	private function _getGemInfo($name, $itemid, $slot)
	{
		if (trim($name) == '')
			return false;

		$data = $this->_read_url($name);

		if (empty($data)) { return false; }

		if ($this->_useSimpleXML())
		{
			// accounts for SimpleXML not being able to handle 3 parameters if you're using PHP 5.1 or below.
			if (!$this->_allowSimpleXMLOptions())
			{
				$data = $this->_removeCData($data);
				$xml = simplexml_load_string($data, 'SimpleXMLElement');
			}
			else
			{
				$xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
			}

			if ($xml->error == '')
			{
				// this will hold our results
				return array(
					'name'			=>	(string)$xml->item->name,
					'gemid'			=>	(string)$xml->item['id'],
					'itemid'		=>	$itemid,
					'slot'			=>	$slot
				);
			}
			else
			{
				return false;
			}

			unset($xml);
		}
	}
	
	/**
	* Strips Headers
	* @access private
	**/
	private function _strip_headers($data)
	{
		// split the string
		$chunks = explode(chr(10), $data);

		// return the last index in the array, aka our xml
		return $chunks[sizeof($chunks) - 1];
	}

	/**
	* Cleans HTML for passing to Wowhead
	* @access private
	**/
	private function _cleanHTML($string)
	{
	    if (function_exists("mb_convert_encoding"))
	        $string = mb_convert_encoding($string, "ISO-8859-1", "HTML-ENTITIES");
	    else
	    {
	       $conv_table = get_html_translation_table(HTML_ENTITIES);
	       $conv_table = array_flip($conv_table);
	       $string = strtr ($string, $conv_table);
	       $string = preg_replace('/&#(\d+);/me', "chr('\\1')", $string);
	    }
	    return ($string);
	}

	/**
	* Encodes the string in UTF-8 if it already isn't
	* @access private
	**/
	private function _convert_string($str)
	{
		// convert to utf8, if necessary
		if (!$this->_is_utf8($str))
		{
			$str = utf8_encode($str);
		}

		// clean up the html
		$str = $this->_cleanHTML($str);

		// return the url encoded string
		return urlencode($str);
	}

	/**
	* Returns true if the $string is UTF-8, false otherwise.
	* @access private
	**/
	private function _is_utf8($string) {
		// From http://w3.org/International/questions/qa-forms-utf-8.html
		return preg_match('%^(?:
			[\x09\x0A\x0D\x20-\x7E]            # ASCII
			| [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
			|  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
			| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
			|  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
			|  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
			| [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
			|  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
		)*$%xs', $string);
	}

	/**
	* Gets the proper domain for the language selected
	* @access private
	**/
	private function _getDomain()
	{
		if ($this->lang == 'en')
			return 'http://www.wowhead.com';
		else
			return 'http://' . strtolower($this->lang) . '.wowhead.com';

		return 'http://www.wowhead.com';
	}

	/**
	* Returns the link to the spell/quest
	* @access private
	**/
	public function _generateLink($id, $type)
	{
		if ($type == 'itemico' || $type == 'item' || $type == 'itemdkp')
		{
			return $this->_getDomain() . '/item=' . $id;
		}
		else
		{
			return $this->_getDomain() . '/' . $type . '=' . $id;
		}
	}

	/**
	* Checks if SimpleXML can accept 3 parameters
	* @access private
	**/
	public function _allowSimpleXMLOptions()
	{
		$parts = explode('.', phpversion());
		return ($parts[0] == 5 && $parts[1] >= 1) ? true : false;
	}

	/**
	* Determines if we can use SimpleXML
	* @access private
	**/
	public function _useSimpleXML()
	{
		$parts = explode('.', phpversion());
		return ($parts[0] == 5) ? true : false;
	}

	/**
	* Called when object isn't found
	* @access private
	**/
	private function _notFound($type, $name)
	{
		global $user; 
		$user->add_lang ( array ('mods/dkp_tooltips' ));
		return '<span class="notfound">[' . sprintf($user->lang['ITEMNOTFOUND'], ucwords($type) , $name) . ']</span>';
	}

	/**
	* Returns the specific line we need
	* @access private
	**/
	private function _abilityLine($data, $name)
	{
		$parts = explode(chr(10), $data);

		foreach ($parts as $line)
		{
			if (strpos($line, "new Listview({template: 'spell', id: 'abilities'") !== false && strpos(strtolower($line), strtolower(addslashes($name))) !== false)
				return array(
					'type'	=>	'ability',
					'line'	=>	$line
				);
			elseif (strpos($line, "new Listview({template: 'spell', id: 'talents'") !== false && strpos(strtolower($line), strtolower(addslashes($name))) !== false)
				return array(
					'type'	=>	'talent',
					'line'	=> 	$line
				);
			elseif (strpos($line, "new Listview({template: 'spell', id: 'recipes'") !== false && strpos(strtolower($line), strtolower(addslashes($name))) !== false)
				return array(
					'type'	=>	'recipe',
					'line'	=>	$line
				);
			elseif (strpos($line, "new Listview({template: 'spell', id: 'uncategorized-spells'") !== false && strpos(strtolower($line), strtolower(addslashes($name))) !== false)
				return array(
					'type'	=>	'ability',
					'line'	=>	$line
				);
		}

		return false;
	}

	/**
	* Returns the specific line we need for achievements
	* @access private
	**/
	private function _achievementLine($data)
	{
		$parts = explode(chr(10), $data);	// split by line breaks

		foreach ($parts as $line)
		{
			if (strpos($line, "new Listview({template: 'achievement', id: 'achievements'") !== false)
				return $line;
		}
		return false;
	}

	/**
	* Replaces wildcards from patterns
	* @access private
	**/
	public function _replaceWildcards($in, $info)
	{
		$wildcards = array();

		// build our wildcard array
		if (array_key_exists('link', $info))
			$wildcards['{link}'] = $info['link'];

		if (array_key_exists('realm', $info))
			$wildcards['{realm}'] = $info['realm'];

		if (array_key_exists('region', $info))
			$wildcards['{region}'] = $info['region'];

		if (array_key_exists('icons', $info))
			$wildcards['{icons}'] = $info['icons'];

		if (array_key_exists('name', $info))
			$wildcards['{name}'] = stripslashes($info['name']);

		if (array_key_exists('quality', $info))
			$wildcards['{qid}'] = $info['quality'];

		if (array_key_exists('rank', $info))
			$wildcards['{rank}'] = $info['rank'];

		if (array_key_exists('icon', $info))
			$wildcards['{icon}'] = $info['icon'];

		if (array_key_exists('class', $info))
			$wildcards['{class}'] = $info['class'];

		if (array_key_exists('gems', $info))
			$wildcards['{gems}'] = $info['gems'];

		if (array_key_exists('tooltip', $info))
			$wildcards['{tooltip}'] = $info['tooltip'];

		if (array_key_exists('npcid', $info))
			$wildcards['{npcid}'] = $info['npcid'];

		foreach ($wildcards as $key => $value)
		{
			$in = str_replace($key, stripslashes($value), $in);
		}

		return $in;
	}

	/**
	* Builds Item Enhancement String
	* @access private
	**/
	private function _buildEnhancement($args)
	{
		if (!is_array($args) || sizeof($args) == 0)
			return false;

		if (array_key_exists('gems', $args))
		{
			$gem_args = 'gems=' . str_replace(',', ':', $args['gems']);
		}

		if (array_key_exists('enchant', $args))
		{
			$enchant_args = 'ench=' . $args['enchant'];
		}

		if (!empty($gem_args) && !empty($enchant_args))
		{
			return $enchant_args . '&amp;' . $gem_args;
		}
		elseif (!empty($enchant_args))
		{
			return $enchant_args;
		}
		elseif (!empty($gem_args))
		{
			return $gem_args;
		}

		return false;
	}


	/**
	* Strips out any apostrophes to prevent any display problems
	* @access private
	**/
	private function _strip_apos($in)
	{
		return str_replace("'", "", $in);
	}
	
	/****
	 * 
	 * if the user is using php 5.1 then strip CDATA from xml
	 */
	private function _removeCData($xml) 
	{
	    $new_xml = NULL;
	    preg_match_all("/\<\!\[CDATA \[(.*)\]\]\>/U", $xml, $args);
	
	    if (is_array($args)) {
	        if (isset($args[0]) && isset($args[1])) 
	        {
	            $new_xml = $xml;
	            for ($i=0; $i<count($args[0]); $i++) 
	            {
	                $old_text = $args[0][$i];
	                $new_text = htmlspecialchars($args[1][$i]);
	                $new_xml = str_replace($old_text, $new_text, $new_xml);
	            }
	        }
	    }
	
	    return $new_xml;
	}
	
	  /**
	 * connects to remote site and gets xml or html using Curl, fopen, or fsockopen
	 * @param char $url
	 * @param char $loud default false
	 * @return xml
	 */
  	private function read_php($url, $return_Server_Response_Header = false, $loud= false) 
	{
		$errmsg1= '';
		$errmsg2= '';
		$errmsg3= '';
		$errstrfsk='';
		$read_phperror=false;
		$xml_data= '';
	    
	    if ( function_exists ( 'curl_init' )) 
		{
			 /* Create a CURL handle. */
			if (($curl = curl_init($url)) === false)
			{
				trigger_error('curl_init Failed' , E_USER_WARNING);   
			}
			
			$useragent = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9) Gecko/2008061004 Firefox/3.0';
			//$useragent='Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9) Gecko/2008052906 Firefox/3.0';
			//$useragent="Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.2) Gecko/20070319 Firefox/2.0.0.3";
			@curl_setopt ( $curl, CURLOPT_USERAGENT, $useragent );

			@curl_setopt ( $curl, CURLOPT_URL, $url );
			if ($return_Server_Response_Header == true)
			{   
			    // only for html, leave this default false if you want xml (like from armory or wowhead items)
    			@curl_setopt ( $curl, CURLOPT_HEADER, 1);
			}
			else 
			{   
			    // only for html, leave this default false if you want xml (like from armory or wowhead items)
    			@curl_setopt ( $curl, CURLOPT_HEADER, 0);
			}
			
			@curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, TRUE );
		    
			$headers = array(
				'Accept: text/xml,application/xml,application/xhtml+xml',
				'Accept-Charset: utf-8,ISO-8859-1'
				);
			@curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			
			
			if (!(ini_get("safe_mode") || ini_get("open_basedir"))) 
			{
				@curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
			}
			
			@curl_setopt ( $curl, CURLOPT_TIMEOUT, 30 );
			
			
			if (curl_errno ( $curl )) 
			{
				/*
                      CURLE_OK = 0,
                      CURLE_UNSUPPORTED_PROTOCOL,     1
                      CURLE_FAILED_INIT,              2
                      CURLE_URL_MALFORMAT,            3
                      CURLE_URL_MALFORMAT_USER,       4 - NOT USED
                      CURLE_COULDNT_RESOLVE_PROXY,    5
                      CURLE_COULDNT_RESOLVE_HOST,     6
                      CURLE_COULDNT_CONNECT,          7
                      CURLE_FTP_WEIRD_SERVER_REPLY,   8
                    */
		       
				switch ($errnum) 
				{
				    case "0" :
				         $read_phperror = false; 
				        
					case "28" :
				        $read_phperror = true; 
					    $errmsg1 = 'cURL error :' . $url . ": No response after 30 second timeout : err " . $errnum . "  ";
						break;
					case "1" :
				        $read_phperror = true;
				        $errmsg1 = 'cURL error :' . $url . " : error " . $errnum . " : UNSUPPORTED_PROTOCOL ";					
						break;
					case "2" :
   				        $read_phperror = true;
						$errmsg1 = 'cURL error :' . $url . " : error " . $errnum . " : FAILED_INIT ";				
						break;
					case "3" :
   				        $read_phperror = true;				    
					    $errmsg1 = 'cURL error :' . $url . " : error " . $errnum . " : URL_MALFORMAT ";					
						break;
					case "5" :
   				        $read_phperror = true;
						$errmsg1 = 'cURL error :' . $url . " : error " . $errnum . " : COULDNT_RESOLVE_PROXY ";
						break;
					case "6" :
   				        $read_phperror = true;
					    $errmsg1 = 'cURL error :' . $url . " : error " . $errnum . " : COULDNT_RESOLVE_HOST ";		
						break;
					case "7" :
   				        $read_phperror = true;
					    $errmsg1 = 'cURL error :' . $url . " : error " . $errnum . " : COULDNT_CONNECT ";
				}
			}
			$xml_data = @curl_exec ($curl);
			@curl_close ($curl);
		}
		
		if ( strlen (rtrim ($xml_data) ) == 0) 
		{
		
			// for file_get_contents to work allow_url_fopen must be set
		    // safe mode must be OFF
			if (@ini_get('allow_url_fopen') and !(@ini_get("safe_mode"))) 
			{
				ini_set ( 'user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9) Gecko/2008052906 Firefox/3.0' );
				$xml_data = @file_get_contents (rtrim ($url));
				$status_code = isset($http_response_header [0]) ? $http_response_header [0] : 0; 
				switch ($status_code) 
				{
					case 200 :
					    $read_phperror = false; 
					    // success
						break;
					case 503 :
					    $read_phperror = true; 
						$errmsg2 = 'file_get_contents error : HTTP error status 503 :  Service unavailable. An internal problem prevented Blizzard from returning Armory data to you.';				
						break;
					case 403 :
					    $read_phperror = true; 
						$errmsg2 = 'file_get_contents error : HTTP status 403 : Forbidden. You do not have permission to access this resource, or are over your rate limit.';		
						break;
					case 400 :
					    $read_phperror = true; 
						$errmsg2 = 'file_get_contents error : HTTP status 400. Bad request using file_get_contents. The parameters passed did not match as expected. The exact error is returned in the XML response.';
						break;
					case 500 :
					    $read_phperror = true; 
						$errmsg2 = 'file_get_contents error : HTTP status 500.  Internal Server Error. The other side is down.';
						break; 
					case 0 : 
						$read_phperror = true;
						$errmsg2 = 'file_get_contents error : No response header. The other side is down.';
					default :
					    $read_phperror = true; 
						$errmsg2 = 'file_get_contents error : Unexpected HTTP status of : ' . $status_code . '.';
				}
			}
		
		}
			
		if ( strlen (rtrim ($xml_data) ) == 0) 
		{
				$url_array = parse_url ($url);
				$remote = @fsockopen ( $url_array ['host'], 80, $errno, $errstr, 5 );
				if (! $remote) 
				{
				    $read_phperror = true; 
					$errmsg3 = "fsockopen error : socket opening failed : " . $errno . ' ' . $errstr; 
				} 
				else 
				{
				    $read_phperror = false; 
					$out = "GET " . $url_array ['path'] . "?" . $url_array ['query'] . " HTTP/1.0\r\n";
					$out .= "Host: " . $url_array ['host'] . " \r\n";
					$out .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; de; rv:1.8.0.4) Gecko/20060508 Firefox/1.5.0.4\r\n";
					$out .= "Accept: text/xml\r\n\r\n"; 
					$out .= "Connection: Close\r\n\r\n";
					 fwrite ( $remote, $out );
					
				    // Get rid of the HTTP headers
					while ( $remote && ! feof ( $remote ) ) 
					{
						$headerbuffer = fgets ( $remote, 1024 );
						if (urlencode ( $headerbuffer ) == "%0D%0A") 
						{
							break;
						}
					}
                    // now get xml data
					$received = '';
					while ( ! feof ( $remote ))
					{
					    $received .= fgets ( $remote, 128 );
					}
					fclose($remote);
					// extract xml					
					$start = strpos($received, "<?xml");
                   $endTag = "</page>";
                   $end = strpos($received, $endTag) + strlen($endTag);
                   $xml_data = substr($received, $start, $end-$start);
					
				}
		}
		
		if ($loud == true)
		{
		    if ( $read_phperror == true  )
		    {
		         trigger_error($errmsg1 . '<br />' . $errmsg2 . '<br />' . $errmsg3 , E_USER_WARNING);   
		    }
		}
		
		return $xml_data;
	}
	
	
	
}
?>