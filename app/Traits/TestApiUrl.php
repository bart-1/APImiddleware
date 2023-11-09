<?php

namespace App\Traits;

use Exception;

trait TestApiUrl
{
 /**
  *@param string - timestamp string
  *@param string - accepted time until deadline will pass
  *@return bool
  */

 public function testApiUrl(string $apiUrl): bool
 {
  if (!filter_var($apiUrl, FILTER_VALIDATE_URL)) {return false;}
  try {

   if (get_headers($apiUrl)) {
    return true;
   } else {
    return false;
   }
  } catch (Exception $err) {
   return false;
  }
 }

}
