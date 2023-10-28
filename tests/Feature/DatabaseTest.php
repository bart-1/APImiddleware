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



public function test_database_works_with_create(): void
{
     $this->model      = new Query();
     $this->repository = new Repository($this->model);
//   $model      = new Query();
//   $repository = new APIQueryRepository($model);
$apiName = 'database-test';
  $data = "{testKey:'test-data'}";
  $this->repository->create(['api-response' => $data, 'api-name' => $apiName]);

  $findApiResponse = $this->repository->findByValue("api-response", $data);
  $findApiName = $this->repository->findByValue("api-name", $apiName);
  assertTrue($findApiResponse !== \null && $findApiName !== \null);

 }
}
