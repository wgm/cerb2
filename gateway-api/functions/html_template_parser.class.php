<?php

require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");

/**
 * Very basic HTML template parser used for the visitor chat windows
 *
 */
class html_template_parser
{
   /**
    * Array of tokens to search and replace for
    *
    * @var array
    */
   var $tokens;
   /**
    * The path to the templates
    *
    * @var string
    */
   var $path;
   
   /**
    * Class constructor
    *
    * @param string $path The base path of the templates, defaults to FILESYSTEM_PATH/visitor-api/html/
    * @return html_template_parser
    */
   function html_template_parser($path = NULL) {
      if(empty($path)) {
         $this->path = FILESYSTEM_PATH . "/visitor-api/html/";
      }
      else {
         $this->path = $path;
      }   
      $this->tokens = array();
   }
   
   /**
    * Assigns a new token to be parsed from the template
    *
    * @param string $token_name name of token to search for
    * @param string $value what to replace it with
    */
   function assign($token_name, $value) {
      $this->tokens[strtoupper($token_name)] = $value;
   }
   
   /**
    * Recurses over $this->tokens building the search array for the str_replace
    *
    * @return unknown
    */
   function _get_search_array($tokens) {
      $array = array();
      foreach($tokens as $name=>$value) {
         $array[] = "%%" . $name . "%%";
      }
      return $array;
   }
   
   /**
    * Recurses over $this->tokens building the replace array for the str_replace
    *
    * @return array
    */
   function _get_replace_array($tokens) {
      $array = array();
      foreach($tokens as $name=>$value) {
         $array[] = $value;
      }
      return $array;
   }
   
   /**
    * Parses a template file and returns back the HTML
    *
    * @param string $filename The filename to parse
    * @params array $tokens Associative array of tokens to parse (added to ones assigned through ->assign())
    * @return string Parsed HTML
    */
   function parse($filename, $tokens = array()) {
      $tokens = array_merge($tokens, $this->tokens);
      $search = $this->_get_search_array($tokens);
      $replace = $this->_get_replace_array($tokens);
      $template = file_get_contents($this->path . $filename);
      $content = str_replace($search, $replace, $template);
      return $content;
   }
}