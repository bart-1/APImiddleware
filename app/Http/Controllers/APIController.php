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
 protected $url;
 protected $apiName;

 public function __construct(APIQueryRepository $repository)
 {
  $this->repository = $repository;

 }

 public function testAPIConnection(string $url): bool
 {

  if (filter_var($url, FILTER_VALIDATE_URL)
   && get_headers($url)) {
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
   $this->fetchAPI($this->url);
   $id = $localData->value('id');
   $this->repository->update($id, ["api-response" => $this->apiResponseData, "api-name" => $this->apiName]);

  }

 }

 public function manageQuery(): void
 {
  $localData = $this->repository->findByValue('api-name', $this->apiName);
  switch (true) {
   case $localData !== null:
    $this->verifyLocalData($localData);
    break;
   case $localData === null:
    $this->fetchAPI($this->url);
    $this->repository->create(["api-response" => $this->apiResponseData, "api-name" => $this->apiName]);
    break;

  }
 }

 public function postQueryHandler(Request $request)
 {

  if ($request->input('url') && $request->input('apiName')) {
   $this->url     = $request->input('url');
   $this->apiName = $request->input('apiName');
   $this->manageQuery();
   return $this->apiResponseData;
  } else {
   return response('not accepted query body', 422);
  }
 }

 public function fetchAPI($url): void
 {

  if ($this->testAPIConnection($url)) {
   $conn = \curl_init($url);
   curl_setopt($conn, CURLOPT_URL, $url);
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
