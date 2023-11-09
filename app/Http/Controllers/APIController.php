<?php

namespace App\Http\Controllers;

use App\Repositories\APIQueryRepository;
use App\Traits\TimestampCompare;
use Exception;
use Illuminate\Http\Request;

class APIController extends Controller
{

 use TimestampCompare;

 protected $repository;
 public $apiResponseData;
 protected $apiUrl;
 protected $apiName;
 public $apiAcceptedDataFreshness;

 public function __construct(APIQueryRepository $repository)
 {
    $this->apiAcceptedDataFreshness === null && $this->apiAcceptedDataFreshness = 10;
  $this->repository = $repository;
 }

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

 public function verifyLocalData($localData): void
 {

  $verifyTime = $this->compareTimestampToNow($localData->value('updated_at'), $this->apiAcceptedDataFreshness);
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
    $this->repository->create(["api-response" => $this->apiResponseData, "api-name" => $this->apiName, "api-url" => $this->apiUrl]);
    break;

  }
 }

 public function apiKeyVerification (Request $request) {
    if($request->input('apiKey') === \env('API_KEY'))
    return $this->postQueryHandler($request);
    else return response('Wrong API key. Access denied.', 403);
 }

 public function postQueryHandler($request)
 {
  if ($request->input('apiUrl') && $request->input('apiName') && $this->testApiUrl($request->input('apiUrl'))) {
   $this->apiUrl  = $request->input('apiUrl');
   $this->apiName = $request->input('apiName');
   $request->input('apiAcceptedDataFreshness') !==null && $this->apiAcceptedDataFreshness = $request->input('apiAcceptedDataFreshness');
   $this->manageQuery();
   return $this->apiResponseData;
  } else if ($request->input('apiUrl') && $request->input('apiName') && !$this->testApiUrl($this->apiUrl)) {
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
