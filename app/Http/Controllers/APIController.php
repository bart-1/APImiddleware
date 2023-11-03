<?php

namespace App\Http\Controllers;

use App\Repositories\APIQueryRepository;
use App\Traits\TimestampCompare;
use Illuminate\Http\Request;

class APIController extends Controller
{

 use TimestampCompare;

 protected $repository;
 public $apiResponseData;
 protected $apiUrl;
 protected $apiName;

 public function __construct(APIQueryRepository $repository)
 {
  $this->repository = $repository;
 }

 public function testApiUrl(string $apiUrl): bool
 {
  if (filter_var($apiUrl, FILTER_VALIDATE_URL)
   && get_headers($apiUrl)) {
   return true;
  } else {
   return false;
  }
 }

 public function verifyLocalData($localData): void
 {

  $verifyTime = $this->compareTimestampToNow($localData->value('updated_at'), 10);
  if ($verifyTime) {
      $this->apiResponseData = $localData->value('api-response');

    } else {
   $this->fetchAPI($this->apiUrl);
   $id = $localData->value('id');
   $this->repository->update($id, ["api-response" => $this->apiResponseData, "api-name" => $this->apiName]);

  }
 }

 public function manageQuery()
 {
  $localData = $this->repository->findByValue('api-url', $this->apiUrl);

  switch (true) {
   case $localData !== null:
    $this->verifyLocalData($localData);
    break;
   case $localData === null:
    $this->fetchAPI($this->apiUrl);
    $this->repository->create(["api-response" => $this->apiResponseData, "api-name" => $this->apiName, "api-url"=> $this->apiUrl]);
    break;

  }
 }

 public function postQueryHandler(Request $request)
 {
if ($request->input('apiUrl') && $request->input('apiName') && $this->testApiUrl($request->input('apiUrl'))) {
    $this->apiUrl     = $request->input('apiUrl');
    $this->apiName = $request->input('apiName');
    $this->manageQuery();
    return $this->apiResponseData;
} else if($request->input('apiUrl') && $request->input('apiName') && !$this->testApiUrl($this->apiUrl)) {
    return response('wrong API URL', 422);
  } else {
   return response('not accepted query body', 422);
  }
 }

 public function fetchAPI($apiUrl): void
 {
  if ($this->testAPIUrl($apiUrl)) {
   $conn = \curl_init($apiUrl);
   curl_setopt($conn, CURLOPT_URL, $apiUrl);
   curl_setopt($conn, CURLOPT_HEADER, false);
   curl_setopt($conn, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

   curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
   $data = curl_exec($conn);

   $this->apiResponseData = $data;
   curl_close($conn);
  } else {
   $this->apiResponseData = '{"connection": "error"}';

  }
 }
}
