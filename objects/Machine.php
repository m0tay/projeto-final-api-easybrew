<?php
include_once 'BREAD.php';

class Machine implements BREAD
{

  private $conn;
  private $table_name = 'machines';
  public $id;
  public $machine_code;
  public $location_name;
  public $api_address;
  public $is_active;

  /**
   * @param mixed $db
   */
  public function __construct($db)
  {
    $this->conn = $db;
  }

  public function browse()
  {
    $query = "SELECT
      *
      FROM
      " . $this->table_name . "
      ORDER BY
      machine_code ASC";

    $stmt = $this->conn->prepare($query);

    $stmt->execute();

    return $stmt;
  }
  public function read()
  {
    $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";

    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':id', filter_var($this->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      $this->id = $row['id'];
      $this->machine_code = $row['machine_code'];
      $this->location_name = $row['location_name'];
      $this->api_address = $row['api_address'];
      $this->is_active = $row['is_active'];

      return true;
    }

    return false;
  }
  public function add()
  {
    $query = "INSERT INTO
      " . $this->table_name . "
      SET
      machine_code = :machine_code,
      location_name = :location_name,
      api_address = :api_address";

    $stmt = $this->conn->prepare($query);

    $stmt->bindValue(':machine_code', $this->machine_code);
    $stmt->bindValue(':location_name', $this->location_name);
    $stmt->bindValue(':api_address', $this->api_address);

    if ($stmt->execute()) {
      $this->id = $this->conn->lastInsertId();
      return true;
    }

    return false;
  }
  public function edit()
  {
    $query = "UPDATE
      " . $this->table_name . "
      SET
      machine_code = :machine_code,
      location_name = :location_name,
      api_address = :api_address,
      is_active = :is_active
      WHERE id = :id ";

    $stmt = $this->conn->prepare($query);

    $this->machine_code = filter_var($this->machine_code, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->location_name = filter_var($this->location_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->api_address = filter_var($this->api_address, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $stmt->bindValue(':machine_code', $this->machine_code);
    $stmt->bindValue(':location_name', $this->location_name);
    $stmt->bindValue(':api_address', $this->api_address);
    $stmt->bindValue(':is_active', $this->is_active);
    $stmt->bindValue(':id', $this->id);

    if ($stmt->execute()) {
      return true;
    }

    return false;
  }
  public function delete()
  {
    $query = "DELETE
      FROM
      " . $this->table_name . "
      WHERE id = :id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindValue(':id', filter_var($this->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    if ($stmt->execute()) {
      return true;
    }

    return false;
  }

  public function count()
  {
    $query = "SELECT
      COUNT(*) as total_rows 
      FROM " . $this->table_name;

    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? $row['total_rows'] : 0;
  }
  public function search($keywords)
  {
    $query = "SELECT
        *
        FROM
        " . $this->table_name . "
        WHERE
        machine_code LIKE :machine_code OR location_name LIKE :location_name
        ORDER BY
        machine_code ASC";

    $stmt = $this->conn->prepare($query);

    $keywords = filter_var($keywords, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $keywords = "%{$keywords}%";

    $stmt->bindValue(':machine_code', $keywords);
    $stmt->bindValue(':location_name', $keywords);

    $stmt->execute();

    return $stmt;
  }
}
