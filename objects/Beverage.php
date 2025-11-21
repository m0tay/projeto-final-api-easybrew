<?php

class Beverage implements BREAD
{

  private $conn;
  private $table_name = 'beverages';

  /**
   * @param mixed $db
   */
  public function __construct($db)
  {
    $this->conn = $db;
  }

  public function browse(): void
  {
    throw new \Exception('Not implemented');
  }
  public function read(): void
  {
    throw new \Exception('Not implemented');
  }
  public function add(): void
  {
    throw new \Exception('Not implemented');
  }
  public function edit(): void
  {
    throw new \Exception('Not implemented');
  }
  public function delete(): void
  {
    throw new \Exception('Not implemented');
  }
}
