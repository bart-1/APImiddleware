<?php

namespace App\Http\Controllers;

use App\Repositories\APIQueryRepository;
use App\Traits\TestApiUrl;
use App\Traits\TimestampCompare;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class APIController extends Controller
{

 use TimestampCompare;
 use TestApiUrl;

 protected $repository;
 public $apiResponseData;
 public $dbResponseData;
 protected $apiUrl;
 protected $apiName;
 public $apiAcceptedDataFreshness;

 public function __construct(APIQueryRepository $repository)
 {
  $this->apiAcceptedDataFreshness === null && $this->apiAcceptedDataFreshness = 10;
  $this->repository                                                           = $repository;
 }

/**
 * Controller main method. Verifies request, validates it elements and return response. Manages errors responses.
 * @param \Illuminate\Http\Request $request
 * @return Response
 */

 public function apiController(Request $request)
 {
  switch (false) {
   case $this->apiKeyVerification($request):
    return response('Wrong API key. Access denied.', 403);
   case $this->validatePOSTContent($request):
    return response('not accepted query body', 422);
   case $this->validateUrlPOSTContent($this->apiUrl):
    return response('wrong API URL', 422);
   case $this->checkLocalDataExist('api-url', $this->apiUrl):
    $this->fetchAPI($this->apiUrl);
    $this->repository->create(["api-response" => $this->apiResponseData, "api-name" => $this->apiName, "api-url" => $this->apiUrl]);
    break;
   case !$this->checkLocalDataExist('api-url', $this->apiUrl):
    $this->loadFindedDataFromDB($this->apiUrl);
    if (!$this->verifyLocalDataFreshness($this->dbResponseData)) {
     $this->updateFindedDataInDB($this->apiUrl, $this->dbResponseData);
    }
    $this->apiResponseData = $this->dbResponseData->value('api-response');
  }

  return $this->apiResponseData;
 }

/**
 * Method verifies apiKey value from POST request by comparing it with API_KEY line in .env file.
 * @param \Illuminate\Http\Request $request
 * @return bool
 */

 public function apiKeyVerification($request): bool
 {
  return ($request->input('apiKey') === \env('API_KEY')) ? true : false;
 }

 /**
  * Method verifies are all required/optional elements in POST body request and if yes it sets is into class property.
  * @param \Illuminate\Http\Request $request
  * @return bool
  */

 public function validatePOSTContent($request): bool
 {
  switch (false) {
   case $request->input('apiUrl') && $request->input('apiUrl') !== null:
    return false;
   case $request->input('apiName') && $request->input('apiName') !== null:
    return false;
  }

  $this->apiUrl  = $request->input('apiUrl');
  $this->apiName = $request->input('apiName');

  if ($request->input('apiAcceptedDataFreshness') && $request->input('apiAcceptedDataFreshness') !== null && gettype($request->input('apiAcceptedDataFreshness')) === "integer") {
   $this->apiAcceptedDataFreshness = $request->input('apiAcceptedDataFreshness');
  }

  return true;
 }

/**
 * Method verifies is argument a proper URL string.
 * @param string $url
 * @return bool
 */

 public function validateUrlPOSTContent($url): bool
 {
  return $this->testApiUrl($url);
 }

 /**
  * Method findes record in DB by column and value arguments.
  * @param string $column
  * @param string $value
  * @return bool
  */

 public function checkLocalDataExist($column, $value): bool
 {
  return $this->repository->findByValue($column, $value) !== null ? true : false;
 }


 /**
 * Method loads model from DB finded by searching value argument and sets is into class property.
 * @param string $column
 * @param string $value
 * @return bool
 */

 public function loadFindedDataFromDB($searchingValue): void
 {
  $this->dbResponseData = $this->repository->findByValue('api-url', $searchingValue);
 }

 public function updateFindedDataInDB($sourceUrl, $modelToUpdate): void
 {
  $id = $modelToUpdate->value('id');
  $this->fetchAPI($sourceUrl);
  $this->repository->update($id, ["api-response" => $this->apiResponseData, "api-name" => $this->apiName]);
  $data                  = $this->repository->findByValue('id', $id);
  $this->apiResponseData = $data->value('api-response');

 }

 /**
  * Method compares timestamps between given as argument Collection and actual timestamp with compareTimestampToNow trait.
  * @param Collection $localData
  * @return bool
  */

 public function verifyLocalDataFreshness($localData): bool
 {
  $verifyTime = $this->compareTimestampToNow($localData->value('updated_at'), $this->apiAcceptedDataFreshness);

  if ($verifyTime) {
   return true;
  } else {
   return false;
  }
 }

 /**
  * Method fetches data from API and sets it into apiResponseData class property.
  * @param string $apiUrl
  * @return void
  */

 public function fetchAPI($apiUrl): void
 {
  $conn = \curl_init($apiUrl);

  curl_setopt($conn, CURLOPT_URL, $apiUrl);
  curl_setopt($conn, CURLOPT_HEADER, false);
  curl_setopt($conn, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
  curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);

  if (!curl_error($conn)) {
   $this->apiResponseData = curl_exec($conn);
  } else {
   $this->apiResponseData = response('connection error', 500);
  }

  curl_close($conn);
 }
}
