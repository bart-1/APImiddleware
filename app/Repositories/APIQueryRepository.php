<?php

namespace App\Repositories;

use App\Models\Query;

class APIQueryRepository extends Repository
{

 protected $model;

 public function __construct(Query $model)
 {
  $this->model = $model;
 }

 }
