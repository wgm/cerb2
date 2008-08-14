<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2002, WebGroup Media LLC 
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
/*!
	\file languages.php
	\brief Translation / language handling functions
	
	Parse languages files in a given includes directory and make	them selectable in user preferences
	
	\author Jeff Standen, jeff@webgroupmedia.com
	\date 2002-2003
*/
define("PATH_LANG","includes/languages/");

//! Cerberus language translation class
/*!
	Functions that handle string language translation & customization of locale.
*/
class cer_translate
	{
    //! Translation equivilent of sprintf() that takes a variable number of arguments
    /*!
    	\note Takes variable number of arguments.
			\note This is heavily used in printing translated strings from the language resource files where dynamic data needs to be displayed.
			\return A translated, formatted string.
    */
    function translate_sprintf()
    	{
      $string = func_get_arg(0);
      $numargs = func_num_args();

      if($numargs > 1) { // [JAS]: Only use sprintf if we have additional args
         for($x=1;$x+1<=$numargs;$x++)
         	{ 
          	$arg = func_get_arg($x);
          	$arg = str_replace('$','\$',$arg); // [JAS]: Escape $, we don't want arguments to be evaluated as variables
          	$string = preg_replace('/%s/',$arg,$string,1);
          }
       	}
      return $string;
      }
    
     // [JSJ]: Added translation function for ticket priority
     //! Translate ticket priority from English to the user's current language preference
     /*!
       \note The destination language is defined in GUI:Preferences
       \param $priority ticket priority in English
       \return A translated priority string
     */
     function translate_priority($priority="")
       {
       switch(strtolower($priority))
               {
         case "unassigned": { 
               $translated = LANG_PRIORITY_UNASSIGNED;
               break; }
         case "none": { 
               $translated = LANG_PRIORITY_NONE;
               break; }
         case "low": { 
               $translated = LANG_PRIORITY_LOW;
               break; }
         case "medium": { 
               $translated = LANG_PRIORITY_MEDIUM;
               break; }
         case "high": { 
               $translated = LANG_PRIORITY_HIGH;
               break; }
         case "critical": { 
               $translated = LANG_PRIORITY_CRITICAL;
               break; }
         case "emergency": { 
               $translated = LANG_PRIORITY_EMERGENCY;
               break; }
         default: {
               $translated = $status;
           break; }
         }      
       return $translated;
       }  

	//! Translate ticket status from English to the user's current language preference
    /*!
		\note The destination language is defined in GUI:Preferences
    	\param $status ticket status in English
    	\return A translated status string
    */
    function translate_status($status="")
    	{
      switch(strtolower($status))
      	{
        case "new": { 
        	$translated = LANG_STATUS_NEW;
        	break; }
        case "awaiting-reply": {
        	$translated = LANG_STATUS_AWAITING_REPLY;
        	break; }
        case "bounced": {
        	$translated = LANG_STATUS_BOUNCED;
        	break; }
        case "customer-reply": {
        	$translated = LANG_STATUS_CUSTOMER_REPLY;
        	break; }
        case "resolved": {
        	$translated = LANG_STATUS_RESOLVED;
        	break; }
        case "dead": {
        	$translated = LANG_STATUS_DEAD;
        	break; }
		default: {
        	$translated = $status;
          break; }
        }      
      return $translated;
      }  

  };

//! Cerberus language object
/*!
	A language object
*/
class cer_language
	{
  var $lang_name; //!< Language name
  var $lang_code; //!< Language country code (e.g., Spanish = "es", French = "fr")
  
  //! Constructor
  /*!
		Populate the language object from data provided on initialization
  */
  function cer_language($l_name,$l_code)
  	{
    $this->lang_name = $l_name;
    $this->lang_code = $l_code;
    }
  };

//! Cerberus language handler
/*!
	Functions to import language files from the filesystem
*/
class cer_languages_obj
	{
	var $languages; //!< Array of language objects
  var $default_language_code; //!< The default language code from site.config.php
  
  //! Constructor
  /*!
		Set initial language values as determined by user preferences.
		Set the default language from site.config.php
		Read in	languages from the filesystem.
  */
  function cer_languages_obj($def_lang="en")
  	{
    $this->languages = array();
    $this->default_language_code = $def_lang;
    $this->read_language_resources();
    }
  
  //! Return the default language
  /*!
  	\return The default language code
  */
  function get_default_language()
  	{
    return($this->default_language_code);
    }

  //! Set the default language
  /*!
		Set initial language values as determined by user preferences
  */
  function set_default_language($l_code)
  	{
    $this->default_language_code = $l_code;
    }

  //! Add a new language to the language handler
  /*!
  	\param $l_name language name
  	\param $l_code language code
  */
  function add_language($l_name,$l_code)
  	{
    $tmp_language = new cer_language($l_name,$l_code);
    array_push($this->languages,$tmp_language);
    }
  
  //! Read language resources from the filesystem
  /*!
		This will look in /includes/languages/* for 'strings.php' files -- where the 
		directory name under ./languages is the country code.
	
  	\return void
  */
	function read_language_resources()
  	{
      $lang_path = PATH_LANG;
      if ($handle = opendir($lang_path)) {
          while (false !== ($file = readdir($handle))) { 
          		// [JAS]: only pull language directories, exclude the ., the .. 
              //	and CVS dirs
				  // [User IBF] Need to skip the langdiff.php file too.
				  if($file != "." && $file != ".." && $file !="CVS" && $file != "langdiff.php") 
              	{ 
                // [JAS]: make sure a strings file exists in this directory, 
                //	or abandon it.
                if(file_exists($lang_path . $file . "/strings.php"))
                	{
                  	// [JAS]: Our language file exists in this dir, parse it.
                    if($lang_handle = fopen ($lang_path . $file . "/strings.php", "r"))
                    	{
                      $found=0;
                      while(!feof($lang_handle) && $found==0)
                      	{
                        $line = fgets($lang_handle,512);
                        if(strstr($line,"LANG_NAME") != false)
                        	{
                          $line = str_replace("define(\"LANG_NAME\",\"","",$line);
                          // [JAS]: Find the semicolon line terminator, to exclude anything
                          //	after the line, such as comments.
                          $line_terminator = strpos($line,";");
                          if(!$line_terminator) $line_terminator = strlen($line)-1;
                          $lang_name = substr($line,0,$line_terminator-2);
                          $this->add_language($lang_name,$file); // [JAS]: add to languages array
                          $found++;
                          }
                        }
                      }
                    fclose($lang_handle);
                  }
                }
          }
          closedir($handle); 
      }
    }
  }
?>