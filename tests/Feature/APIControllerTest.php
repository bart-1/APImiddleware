<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Query;
use App\Repositories\APIQueryRepository;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEquals;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class APIControllerTest extends TestCase
{

 use RefreshDatabase;


 public $realUrl = "https: //dziwnykot.pl/weather-api-second/pl/warszawa";
 public $wrongUrl = "url.test";
 public $apiName = "testApi";
 public $apiResponse = 'test response';



 public function test_controller_response_correct(): void
 {

  $query = $this->post('http://localhost:8000/api/', ['url' => $this->realUrl, 'apiName' =>$this->apiName]);
  $query->assertStatus(200);

 }
 public function test_controller_return_status_422_with_wrong_post_keys(): void
 {

  $query = $this->post('http://localhost:8000/api/', ['wrongKey' => $this->realUrl, 'apiName' =>$this->apiName]);
  $query->assertStatus(422);

 }

 public function test_controller_return_status_200_when_find_same_data_in_db()
 {

  $model      = new Query();
  $repository = new APIQueryRepository($model);
  $repository->create(['api-name' => $this->apiName, 'api-response' => $this->apiResponse]);

  $query = $this->post('http://localhost:8000/api/', ['url' => $this->realUrl, 'apiName' =>$this->apiName]);

  $query->assertStatus(200) && $query->assertContent($this->apiResponse);

 }

 public function test_controller_properly_validate_is_already_data_in_db_and_is_fresh()
 {
  $model      = new Query();
  $repository = new APIQueryRepository($model);

  $repository->create(['api-name' => $this->apiName, 'api-response' => $this->apiResponse]);
  $trackDBRecord   = $repository->findByValue('api-name', $this->apiName);
  $id              = $trackDBRecord->value('id');
  $updateTimestamp = $trackDBRecord->value('updated_at');

  sleep(5);

  $query            = $this->post('http://localhost:8000/api/', ['url' => $this->realUrl, 'apiName' =>$this->apiName]);
  $trackDBRecord2   = $repository->findByValue('api-name', $this->apiName);
  $id2              = $trackDBRecord2->value('id');
  $updateTimestamp2 = $trackDBRecord2->value('updated_at');

  $query->assertStatus(200)
  && assertEquals($updateTimestamp, $updateTimestamp2) && assertEquals($id, $id2);

 }
 public function test_controller_properly_validate_is_already_data_in_db_and_update_unfresh()
 {
  $model      = new Query();
  $repository = new APIQueryRepository($model);

  $repository->create(['api-name' => $this->apiName, 'api-response' => $this->apiResponse]);
  $trackDBRecord   = $repository->findByValue('api-name', $this->apiName);
  $id              = $trackDBRecord->value('id');
  $updateTimestamp = $trackDBRecord->value('updated_at');

  sleep(20);

  $query            = $this->post('http://localhost:8000/api/', ['url' => $this->realUrl, 'apiName' =>$this->apiName]);
  $trackDBRecord2   = $repository->findByValue('api-name', $this->apiName);
  $id2              = $trackDBRecord2->value('id');
  $updateTimestamp2 = $trackDBRecord2->value('updated_at');

  echo $updateTimestamp . "   " . $updateTimestamp2;

  $query->assertStatus(200) && assertNotEquals($updateTimestamp, $updateTimestamp2) &&
  assertEquals($id, $id2);

 }

}
