<?php
include_once 'BREAD.php';

class Transaction implements BREAD
{

  private $conn;
  private $table_name = 'transactions';
  
  public $id;
  public $user_id;
  public $machine_id;
  public $beverage_id;
  public $beverage_name;
  public $preparation_chosen;
  public $amount;
  public $type;
  public $status;
  public $created_at;

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
      created_at DESC";

    $stmt = $this->conn->prepare($query);

    $stmt->execute();

    return $stmt;
  }

  public function browseByUser()
  {
    $query = "SELECT
      *
      FROM
      " . $this->table_name . "
      WHERE
      user_id = :user_id
      ORDER BY
      created_at DESC";

    $stmt = $this->conn->prepare($query);

    $stmt->bindValue(':user_id', filter_var($this->user_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    $stmt->execute();

    return $stmt;
  }

  public function read()
  {
    $query = "SELECT
      *
      FROM
      " . $this->table_name . "
      WHERE
      id = :id
      LIMIT 1";

    $stmt = $this->conn->prepare($query);

    $stmt->bindValue(':id', filter_var($this->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      $this->id = $row['id'];
      $this->user_id = $row['user_id'];
      $this->machine_id = $row['machine_id'];
      $this->beverage_id = $row['beverage_id'];
      $this->beverage_name = $row['beverage_name'];
      $this->preparation_chosen = $row['preparation_chosen'];
      $this->amount = $row['amount'];
      $this->type = $row['type'];
      $this->status = $row['status'];
      $this->created_at = $row['created_at'];
      return true;
    }
    
    return false;
  }
  public function add()
  {
    // Gerar UUID v4
    $this->id = sprintf(
      '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0x0fff) | 0x4000,
      mt_rand(0, 0x3fff) | 0x8000,
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    $query = 'INSERT INTO ' . $this->table_name . '
      (id, user_id, machine_id, beverage_id, beverage_name, preparation_chosen, amount, type, status)
      VALUES
      (:id, :user_id, :machine_id, :beverage_id, :beverage_name, :preparation_chosen, :amount, :type, :status)';

    $stmt = $this->conn->prepare($query);

    $stmt->bindValue(':id', $this->id);
    $stmt->bindValue(':user_id', filter_var($this->user_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $stmt->bindValue(':machine_id', $this->machine_id === null ? null : filter_var($this->machine_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $stmt->bindValue(':beverage_id', filter_var($this->beverage_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $stmt->bindValue(':beverage_name', filter_var($this->beverage_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $stmt->bindValue(':preparation_chosen', $this->preparation_chosen === null ? null : filter_var($this->preparation_chosen, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $stmt->bindValue(':amount', filter_var($this->amount, FILTER_VALIDATE_FLOAT));
    $stmt->bindValue(':type', filter_var($this->type, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $stmt->bindValue(':status', filter_var($this->status, FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    if ($stmt->execute()) {
      return true;
    }

    return false;
  }
  public function edit()
  {
    $query = "UPDATE
      " . $this->table_name . "
      SET
      type = :type,
      status = :status
      WHERE id = :id";

    $stmt = $this->conn->prepare($query);

    $this->type = filter_var($this->type, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->status = filter_var($this->status, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $stmt->bindValue(':type', $this->type);
    $stmt->bindValue(':status', $this->status);
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
}
