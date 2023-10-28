<?php

namespace App\Interfaces;

interface RepositoryInterface
{
 public function getAll();

 public function create($data);

 public function update($id, $data);

 public function delete($id);

 public function find($id);

 public function findByValue($column, $value);

}
