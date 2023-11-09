<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Http\Controllers\APIController;
use App\Models\Query;
use App\Repositories\APIQueryRepository;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertTrue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class APIControllerTest extends TestCase
{

 use RefreshDatabase;

 public $realUrl     = "https://dziwnykot.pl/weather-api-second/pl/warszawa";
 public $wrongUrl    = "url.test";
 public $apiName     = "testApi";
 public $apiResponse = 'test response';

 public function test_wrong_apiKey_response()
 {

  $query = $this->post('http://localhost:8000/api/', ['apiUrl' => $this->realUrl, 'apiName' => $this->apiName, 'apiKey' => "wrong api key"]);
  $query->assertStatus(403);
 }

 public function test_controller_response_correct(): void
 {

  $query = $this->post('http://localhost:8000/api/', ['apiUrl' => $this->realUrl, 'apiName' => $this->apiName, 'apiKey' => env('API_KEY')]);
  $query->assertStatus(200);

 }
 public function test_controller_return_status_422_with_wrong_post_keys(): void
 {

  $query = $this->post('http://localhost:8000/api/', ['wrongKey' => $this->realUrl, 'apiName' => $this->apiName, 'apiKey' => env('API_KEY')]);
  $query->assertStatus(422);

 }

 public function test_controller_return_status_200_when_find_same_data_in_db()
 {

  $model      = new Query();
  $repository = new APIQueryRepository($model);
  $repository->create(['api-name' => $this->apiName, 'api-response' => $this->apiResponse, 'api-url' => $this->realUrl]);

  $query = $this->post('http://localhost:8000/api/', ['apiUrl' => $this->realUrl, 'apiName' => $this->apiName, 'apiKey' => env('API_KEY')]);

  $query->assertStatus(200) && $query->assertContent($this->apiResponse);

 }

 public function test_controller_properly_validate_is_already_data_in_db_and_is_fresh()
 {
  $model      = new Query();
  $repository = new APIQueryRepository($model);

  $repository->create(['api-name' => $this->apiName, 'api-response' => $this->apiResponse, 'api-url' => $this->realUrl]);
  $trackDBRecord   = $repository->findByValue('api-url', $this->realUrl);
  $id              = $trackDBRecord->value('id');
  $updateTimestamp = $trackDBRecord->value('updated_at');

  sleep(5);

  $query            = $this->post('http://localhost:8000/api/', ['apiUrl' => $this->realUrl, 'apiName' => $this->apiName, 'apiKey' => env('API_KEY'), 'apiAcceptedDataFreshness' => 7]);
  $trackDBRecord2   = $repository->findByValue('api-url', $this->realUrl);
  $id2              = $trackDBRecord2->value('id');
  $updateTimestamp2 = $trackDBRecord2->value('updated_at');

  $query->assertStatus(200)
  && assertEquals($updateTimestamp, $updateTimestamp2) && assertEquals($id, $id2);

 }
 public function test_controller_properly_validate_is_already_data_in_db_and_update_unfresh()
 {
  $model      = new Query();
  $repository = new APIQueryRepository($model);

  $repository->create(['api-name' => $this->apiName, 'api-response' => $this->apiResponse, 'api-url' => $this->realUrl]);
  $trackDBRecord   = $repository->findByValue('api-url', $this->realUrl);
  $id              = $trackDBRecord->value('id');
  $updateTimestamp = $trackDBRecord->value('updated_at');

  sleep(5);

  $query            = $this->post('http://localhost:8000/api/', ['apiUrl' => $this->realUrl, 'apiName' => $this->apiName, 'apiKey' => env('API_KEY'), 'apiAcceptedDataFreshness' => 3]);
  $trackDBRecord2   = $repository->findByValue('api-url', $this->realUrl);
  $id2              = $trackDBRecord2->value('id');
  $updateTimestamp2 = $trackDBRecord2->value('updated_at');

  $query->assertStatus(200) && assertNotEquals($updateTimestamp, $updateTimestamp2) &&
  assertEquals($id, $id2);

 }

 public function test_url_validation_works_with_incorect_url(): void
 {

  $incorrectUrl = "h://op.pllpl";
  $model        = new Query();
  $repository   = new APIQueryRepository($model);
  $controller   = new APIController($repository);

  assertFalse($controller->testApiUrl($incorrectUrl));

 }

 public function test_url_validation_works_with_correct_url(): void
 {

  $incorrectUrl = "https://dziwnykot.pl";
  $model        = new Query();
  $repository   = new APIQueryRepository($model);
  $controller   = new APIController($repository);

  assertTrue($controller->testApiUrl($incorrectUrl));

 }

}
