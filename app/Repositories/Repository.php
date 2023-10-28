<?php

namespace App\Repositories;

use App\Interfaces\RepositoryInterface;

class Repository implements RepositoryInterface
{

 protected $model;

 public function __construct($model)
 {
  $this->model = $model;
 }

 public function getAll()
 {
  return $this->model->all();
 }

 public function create($data)
 {
  $this->model->create($data);
 }

 public function update($id, $data)
 {
  $this->model->where('id', $id)->update($data);
 }

 public function delete($id)
 {
  $this->model->findOrFail($id)->delete();
 }

 public function find($id)
 {
  return $this->model->find($id)->get();
 }

 public function findByValue($column, $value)
 {
  return $this->model->where($column, $value)->first();
 }
}
