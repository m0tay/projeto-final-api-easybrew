<?php

interface BREAD
{
  private $conn { set; }

  private $table_name { set; }

  public function browse();

  public function read();

  public function add();

  public function edit();

  public function delete();
}
