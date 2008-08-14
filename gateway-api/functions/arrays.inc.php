<?php

function array_remove() {
   if(func_num_args() < 2) {
      trigger_error("Function expects atleast two arguements, less given.", E_USER_WARNING);
   }
   $arg_list = func_get_args();
   $arr = array_shift($arg_list);
   $start_array = $arr[0];
   $match = $arr[1];
   $remove_list = array();
   foreach($arg_list as $arg) {
      if($arg[1] == 'key') {
         $remove_list = array_merge($remove_list, array_keys($arg[0]));
      }
      else {
         $remove_list = array_merge($remove_list, array_values($arg[0]));
      }
   }
   $output_array = array();
   foreach($start_array as $key=>$value) {
      if($match == 'key') {
         if(!in_array($key, $remove_list)) {
            $output_array[$key] = $value;
         }
      }
      else {
         if(!in_array($value, $remove_list)) {
            $output_array[$key] = $value;
         }
      }
   }
   return $output_array;
}
   
   