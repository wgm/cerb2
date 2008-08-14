<?php

// Handle the cases of people not upgrading to a recent version of PHP (ie < 4.3.0)

if(!function_exists('file_get_contents')) {
   function file_get_contents($filename) {
      return implode('', file($filename));
   }
}
