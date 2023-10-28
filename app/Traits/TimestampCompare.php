<?php

namespace App\Traits;

trait TimestampCompare
{
 /**
  *@param string - timestamp string
  *@param string - accepted time until deadline will pass
  *@return bool
  */

 public function compareTimestampToNow($timestamp, $acceptableDelay)
 {
  if (\strtotime('now')-\strtotime($timestamp) <= $acceptableDelay) {
   return true;
  } else {
   return false;
  }
 }

}
