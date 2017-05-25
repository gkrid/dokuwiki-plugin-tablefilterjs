<?php
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_tablefilterjs extends DokuWiki_Syntax_Plugin {

  function getType() { return 'container';}
  function getPType(){ return 'block';}
  function getSort() { return 500; }
  function getAllowedTypes() {return array('container','formatting','substition');}
	function connectTo($mode) {
    $this->Lexer->addEntryPattern('<filter[^>]*>(?=.*?</filter>)',$mode,'plugin_tablefilterjs');
  }
  function postConnect() {
    $this->Lexer->addExitPattern('</filter>','plugin_tablefilterjs');
  }
  function __filter_data($str)
  {
	$filter_data = array();

	$key = '';

	//0 no 1 
	$in_str = 0;
	//0 header 1 regex
	$state = 0;
	for( $i = 0; $i < strlen( $str ); $i++ )
	{
	    if($state == 0)
	    {
		if( $str[$i] == '=' )
		{
		    $state = 1;

		    while( $str[$i] != '/'  )
		    {
			$i++;
			if( $i == strlen( $str ) )
			{
			    return $filter_data;
			}
		    }

		    //$filter_data[ trim($key) ] .= $str[ $i ];

		} else
		{

		    if( $in_str == 1 && $str[$i] == '\\' )
		    {
			$i++;
			$key .= $str[ $i ];
			continue;
		    }

		    if( $str[$i] == '"' || $str[$i] == "'")
		    {
			if( $in_str == 1 )
			    $in_str = 0;
			else
			    $in_str = 1;
			continue;
		    }

		    $key .= $str[ $i ];
		}

	    } else
	    {
		if( $str[$i] == '/' )
		{
		    $i++;
		    $state = 0;
		    while( $str[$i] != ' ' && $i < strlen( $str ) )
		    {
			$filter_data[ trim($key) ][1] .= $str[ $i ];
			$i++;
			if( $i == strlen( $str ) )
			{
			    return $filter_data;
			}
		    }
		    $key = '';
		    continue;
		}
		$filter_data[ trim($key) ][0] .= $str[ $i ];
	    }
	}
	return $filter_data;
  }
  function __encodeHTML($str)
  {
      return str_replace(array('"', '\'', '&', '<'), array('&quot;', '&#39;', '&amp;', '&lt;'), $str);
  }
  function handle($match, $state, $pos, Doku_Handler $handler){
    
    switch ($state) {
      case DOKU_LEXER_ENTER :
        $match = substr($match,8,-1);

        return array($state, $this->__filter_data($match));
        break;
      case DOKU_LEXER_UNMATCHED :
        return array($state, $match);
        break;
      case DOKU_LEXER_EXIT :
        return array($state, "");
        break;
    }
    return array();
  }

  function render($mode, Doku_Renderer $renderer, $data) {
    list($state,$match) = $data;
    if ($mode == 'xhtml'){
      switch ($state) {
        case DOKU_LEXER_ENTER :
	  $json = new JSON();
          $renderer->doc .= '<div class="tablefilterjs" data-filters="'.$this->__encodeHTML($json->encode($match)).'">';
          break;
	case DOKU_LEXER_UNMATCHED :  
          $instructions = p_get_instructions($match);
          foreach ($instructions as $instruction) {
            call_user_func_array(array(&$renderer, $instruction[0]),$instruction[1]);
          }
	break;
        case DOKU_LEXER_EXIT :
          $renderer->doc .=  "</div>";
          break;
      }
      return true;
    } 
  }
}
