<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Query;
use App\Repositories\Repository;
use function PHPUnit\Framework\assertTrue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseTest extends TestCase
{
 /**
  * A basic test example.
  */
 use RefreshDatabase;

 protected $repository;
 protected $model;

 protected $apiUrl = "api.url";
 protected $apiName = 'database-test';
   protected $apiResponse = "{testKey:'test-data'}";


public function test_database_works_with_create(): void
{
     $this->model      = new Query();
     $this->repository = new Repository($this->model);
//   $model      = new Query();
//   $repository = new APIQueryRepository($model);
  $this->repository->create(['api-response' => $this->apiResponse, 'api-name' => $this->apiName, 'api-url'=> $this->apiUrl]);

  $findApiResponse = $this->repository->findByValue("api-response", $this->apiResponse);
  $findApiName = $this->repository->findByValue("api-name", $this->apiName);
  $findApiUrl = $this->repository->findByValue("api-url", $this->apiUrl);
  assertTrue($findApiResponse !== \null && $findApiName !== \null && $findApiUrl !== \null);

 }
}
