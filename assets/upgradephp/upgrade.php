<?php
/**
 * api:		php
 * title:	upgrade.php
 * description:	Emulates functions from new PHP versions on older interpreters.
 * version:	17
 * license:	Public Domain
 * url:		http://freshmeat.net/projects/upgradephp
 * type:	functions
 * category:	library
 * priority:	auto
 * load_if:     (PHP_VERSION<5.2)
 * sort:	-255
 * provides:	upgrade-php, api:php5, json
 *

/**
 * file-related constants
 *
 */
if (!defined("FILE_APPEND")) { define("FILE_APPEND", 8); }

/**
 * write-at-once file access (counterpart to file_get_contents)
 *
 * @param  integer $filename
 * @param  mixed   $content  
 * @param  integer $flags 
 * @param  mixed   $resource
 * @return integer
 */
if (!function_exists("file_put_contents")) {
   function file_put_contents($filename, $content, $flags=0, $resource=NULL) {

      #-- prepare
      $mode = ($flags & FILE_APPEND ? "a" : "w" ) ."b";
      $incl = $flags & FILE_USE_INCLUDE_PATH;
      $length = strlen($content);
//      $resource && trigger_error("EMULATED file_put_contents does not support \$resource parameter.", E_USER_ERROR);
      
      #-- write non-scalar?
      if (is_array($content) || is_object($content)) {
         $content = implode("", (array)$content);
      }

      #-- open for writing
      $f = fopen($filename, $mode, $incl);
      if ($f) {
      
         // locking
         if (($flags & LOCK_EX) && !flock($f, LOCK_EX)) {
            return fclose($f) && false;
         }

         // write
         $written = fwrite($f, $content);
         fclose($f);
         
         #-- only report success, if completely saved
         return($length == $written);
      }
   }
}

#-- more new constants for 5.0
if (!defined("E_STRICT")) { define("E_STRICT", 2048); }  // _STRICT is a special case of _NOTICE (_DEBUG)

/**
 * @since unknown
 */
if (!defined("E_RECOVERABLE_ERROR")) { define("E_RECOVERABLE_ERROR", 4096); }

/**
 * Lowercase first character.
 *
 * @param string
 * @return string
 */
if (!function_exists("lcfirst")) {
   function lcfirst($str) {
      return strlen($str) ? strtolower($str[0]) . substr($str, 1) : "";
   }
}
