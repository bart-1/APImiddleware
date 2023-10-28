<?php

namespace Tests\Feature;

// use PHPUnit\Framework\TestCase;
use App\Traits\TimestampCompare;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;
use Tests\TestCase;

class TimestampCompareTest extends TestCase
{

 use TimestampCompare;
 /**
  * A basic test example.
  */
 public function test_timestamp_compare_return_true_if_difference_from_now_is_acceptable(): void
 {
  $time        = time();
  $timeToCheck = date('Y-m-d H:i:s', $time);

  $acceptableDifference = 10;

  sleep(5);

  $check = $this->compareTimestampToNow($timeToCheck, $acceptableDifference);
  echo "test: " . \strtotime('now')-\strtotime($timeToCheck) . " < " . $acceptableDifference;
  echo $check ? ' true' : ' false' . "\n";
  assertTrue($check === true);
 }

 public function test_timestamp_compare_return_false_if_difference_from_now_is_not_acceptable(): void
 {
  $time                 = time();
  $timeToCheck          = date('Y-m-d H:i:s', $time);
  $acceptableDifference = 2;

  sleep(5);

  $check = $this->compareTimestampToNow($timeToCheck, $acceptableDifference);
  echo "\n test: " . \strtotime('now')-\strtotime($timeToCheck) . " < " . $acceptableDifference;
  echo $check ? ' true' : ' false' . "\n";
  assertFalse($check);
 }

}
