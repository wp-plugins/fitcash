<?php

global $arr_fitcash_host_blog_type;

class fitcash_lastRSS 
{
	// -------------------------------------------------------------------
	// Public properties
	// -------------------------------------------------------------------
	var $default_cp = 'UTF-8';
	var $CDATA = 'nochange';
	var $cp = '';
	var $items_limit = 0;
	var $stripHTML = False;
	var $date_format = '';

	// -------------------------------------------------------------------
	// Private variables
	// -------------------------------------------------------------------
	var $channeltags = array ('title', 'link', 'description', 'language', 'copyright', 'managingEditor', 'webMaster', 'lastBuildDate', 'rating', 'docs');
	var $itemtags = array('title', 'link', 'description', 'author', 'category', 'comments', 'enclosure', 'guid', 'pubDate', 'source','content','published','summary','is_error_msg','post_tags');
	var $imagetags = array('title', 'url', 'link', 'width', 'height');
	var $textinputtags = array('title', 'description', 'name', 'link');

  // -------------------------------------------------------------------
  // Parse RSS file and returns associative array.
  // -------------------------------------------------------------------
  function Get ($rss_url) 
  {
    $result = $this->Parse($rss_url);
    return $result;
  }
	
	// -------------------------------------------------------------------
	// Modification of preg_match(); return trimed field with index 1
	// from 'classic' preg_match() array output
	// -------------------------------------------------------------------
	function my_preg_match ($pattern, $subject) {
		// start regullar expression
		preg_match($pattern, $subject, $out);

		// if there is some result... process it and return it
		if(isset($out[1])) {
			// Process CDATA (if present)
			if ($this->CDATA == 'content') { // Get CDATA content (without CDATA tag)
				$out[1] = strtr($out[1], array('<![CDATA['=>'', ']]>'=>''));
			} elseif ($this->CDATA == 'strip') { // Strip CDATA
				$out[1] = strtr($out[1], array('<![CDATA['=>'', ']]>'=>''));
			}

			// If code page is set convert character encoding to required
			if ($this->cp != '')
				//$out[1] = $this->MyConvertEncoding($this->rsscp, $this->cp, $out[1]);
				$out[1] = iconv($this->rsscp, $this->cp.'//TRANSLIT', $out[1]);
			// Return result
			return trim($out[1]);
		} else {
		// if there is NO result, return empty string
			return '';
		}
	}

	// -------------------------------------------------------------------
	// Replace HTML entities &something; by real characters
	// -------------------------------------------------------------------
	function unhtmlentities ($string) {
		// Get HTML entities table
		$trans_tbl = get_html_translation_table (HTML_ENTITIES, ENT_QUOTES);
		// Flip keys<==>values
		$trans_tbl = array_flip ($trans_tbl);
		// Add support for &apos; entity (missing in HTML_ENTITIES)
		$trans_tbl += array('&apos;' => "'");
		// Replace entities by values
		return strtr ($string, $trans_tbl);
	}

	// -------------------------------------------------------------------
	// Parse() is private method used by Get() to load and parse RSS file.
	// Don't use Parse() in your scripts - use Get($rss_file) instead.
	// -------------------------------------------------------------------
        
         function get_rss_content($rss_url){
                  // Set up the CURL object
            $ch = curl_init($rss_url);
            
            // Fake out the User Agent
            curl_setopt( $ch, CURLOPT_USERAGENT, "Internet Explorer" );
            
            // Start the output buffering
            ob_start();
            
            // Get the HTML from MetaCritic
            curl_exec( $ch );
            curl_close( $ch );
            
            // Get the contents of the output buffer
            $str = ob_get_contents();
            ob_end_clean();
            return $str;
        }

  function Parse ($rss_url) 
  {
    $rss_content = '';
    // Open and load RSS file
    if ( $rss_content = file_getContents($rss_url) )
    {
      // Parse document encoding
      $result['encoding'] = $this->my_preg_match("'encoding=[\'\"](.*?)[\'\"]'si", $rss_content);

      // get document type
      if ( !(strpos( $rss_content, '<rss version="2.0"') === false) )
        $result['type'] = 'feed';
      if ( !(strpos( $rss_content, '<feed') === false) )
        $result['type'] = 'atom';

      // if document codepage is specified, use it
      if ($result['encoding'] != '')
      { 
        // This is used in my_preg_match()
        $this->rsscp = $result['encoding']; 
      } 
      // otherwise use the default codepage
      else
      { 
        $this->rsscp = $this->default_cp; 
      } // This is used in my_preg_match()

      // Parse CHANNEL info
      preg_match("'<channel.*?>(.*?)</channel>'si", $rss_content, $out_channel);
      foreach($this->channeltags as $channeltag)
      {
        $temp = $this->my_preg_match("'<$channeltag.*?>(.*?)</$channeltag>'si", $out_channel[1]);
        if ($temp != '') 
          $result[$channeltag] = $temp; // Set only if not empty
      }

      // If date_format is specified and lastBuildDate is valid
      if ($this->date_format != '' && ($timestamp = strtotime($result['lastBuildDate'])) !==-1) 
      {
	// convert lastBuildDate to specified date format
	$result['lastBuildDate'] = date($this->date_format, $timestamp);
      }

      // Parse TEXTINPUT info
      preg_match("'<textinput(|[^>]*[^/])>(.*?)</textinput>'si", $rss_content, $out_textinfo);
				// This a little strange regexp means:
				// Look for tag <textinput> with or without any attributes, but skip truncated version <textinput /> (it's not beggining tag)
      if (isset($out_textinfo[2])) 
      {
        foreach($this->textinputtags as $textinputtag) 
        {
					$temp = $this->my_preg_match("'<$textinputtag.*?>(.*?)</$textinputtag>'si", $out_textinfo[2]);
					if ($temp != '') $result['textinput_'.$textinputtag] = $temp; // Set only if not empty
        }
      }

      // Parse IMAGE info
      preg_match("'<image.*?>(.*?)</image>'si", $rss_content, $out_imageinfo);
      if (isset($out_imageinfo[1])) 
      {
        foreach($this->imagetags as $imagetag) 
        {
					$temp = $this->my_preg_match("'<$imagetag.*?>(.*?)</$imagetag>'si", $out_imageinfo[1]);
					if ($temp != '') $result['image_'.$imagetag] = $temp; // Set only if not empty
        }
      }

      // Parse ITEMS
      switch( $result['type'] )
      {
        case 'atom':
          preg_match_all("'<entry(| .*?)>(.*?)</entry>'si", $rss_content, $items);
          break;
        case 'feed':
          preg_match_all("'<item(| .*?)>(.*?)</item>'si", $rss_content, $items);
          break;
        default:
          preg_match_all("'<entry(| .*?)>(.*?)</entry>'si", $rss_content, $items);
          break;
      }
      $rss_items = $items[2];
      $i = 0;
      $result['items'] = array(); // create array even if there are no items

      foreach($rss_items as $rss_item) 
      {
        // If number of items is lower then limit: Parse one item
        if ($i < $this->items_limit || $this->items_limit == 0) 
        {
          foreach($this->itemtags as $itemtag) 
          {
            $temp = $this->my_preg_match("'<$itemtag.*?>(.*?)</$itemtag.*?>'si", $rss_item);
            if ($temp != '') 
              $result['items'][$i][$itemtag] = htmlspecialchars($temp); // Set only if not empty
          }
					// Strip HTML tags and other bullshit from DESCRIPTION
					if ($this->stripHTML && $result['items'][$i]['description'])
						$result['items'][$i]['description'] = strip_tags($this->unhtmlentities(strip_tags($result['items'][$i]['description'])));
					// Strip HTML tags and other bullshit from TITLE
					if ($this->stripHTML && $result['items'][$i]['title'])
						$result['items'][$i]['title'] = strip_tags($this->unhtmlentities(strip_tags($result['items'][$i]['title'])));
					// If date_format is specified and pubDate is valid
					if ($this->date_format != '' && ($timestamp = strtotime($result['items'][$i]['pubDate'])) !==-1) {
						// convert pubDate to specified date format
						$result['items'][$i]['pubDate'] = date($this->date_format, $timestamp);
					}
					// Item counter
					$i++;
        }
      }

      $result['items_count'] = $i;
      return $result;
    }
    else // Error in opening return False
    {
      return False;
    }
  }
}


function file_getContents($url) 
{
  // URL zerlegen
  $parsedurl = @parse_url($url);
  // Host ermitteln, ungültigen Aufruf abfangen
  if (empty($parsedurl['host']))
    return null;
  $host = $parsedurl['host'];
  // Pfadangabe ermitteln
  if (empty($parsedurl['path']))
    $documentpath = '/';
  else
    $documentpath = $parsedurl['path'];
  // Parameter ermitteln
  if (!empty($parsedurl['query'])) 
    $documentpath .= '?'.$parsedurl['query'];
  // Port ermitteln
  if (!empty($parsedurl['port']))
    $port = $parsedurl['port'];
  else
    $port = 80;
  // Socket öffnen
  $fp = fsockopen ($host, $port, $errno, $errstr, 30);
  if (!$fp)
    return null;
  // Request senden
    fputs ($fp, "GET {$documentpath} HTTP/1.0\r\nHost: {$host}\r\n\r\n");
  // Header auslesen
  do 
  {
    $line = chop(fgets($fp));
  } while (!empty($line) and !feof($fp));
  // Daten auslesen
  $result = '';
  while (!feof($fp)) 
  {
    $result .= fgets($fp);
  }
  // Socket schliessen
  fclose($fp);
  // Ergebnis zurückgeben
  return $result;
}

?>