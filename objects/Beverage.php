<?php
include_once 'BREAD.php';

class Beverage implements BREAD
{

  private $conn;
  private $table_name = 'beverages';
  
  public $id;
  public $name;
  public $type;
  public $size;
  public $preparation;
  public $description;
  public $image;
  public $price;
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
      name ASC";

    $stmt = $this->conn->prepare($query);

    $stmt->execute();

    return $stmt;
  }
  public function read(): void
  {
    $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";

    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':id', filter_var($this->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      $this->name = $row['name'];
      $this->type = $row['type'];
      $this->size = $row['size'];
      $this->preparation = $row['preparation'];
      $this->description = $row['description'];
      $this->image = $row['image'];
      $this->price = $row['price'];
      $this->is_active = $row['is_active'];
    }
  }
  public function add()
  {
    $query = "INSERT INTO
      " . $this->table_name . "
      SET
      name = :name,
      type = :type,
      size = :size,
      preparation = :preparation,
      description = :description,
      image = :image,
      price = :price,
      is_active = 1";

    $stmt = $this->conn->prepare($query);

    $this->name = filter_var($this->name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->type = filter_var($this->type, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->size = filter_var($this->size, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->preparation = filter_var($this->preparation, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->description = filter_var($this->description, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->image = filter_var($this->image, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->price = filter_var($this->price, FILTER_VALIDATE_FLOAT);

    $stmt->bindValue(':name', $this->name);
    $stmt->bindValue(':type', $this->type);
    $stmt->bindValue(':size', $this->size);
    $stmt->bindValue(':preparation', $this->preparation);
    $stmt->bindValue(':description', $this->description);
    $stmt->bindValue(':image', $this->image);
    $stmt->bindValue(':price', $this->price);

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
      name = :name,
      type = :type,
      size = :size,
      preparation = :preparation,
      description = :description,
      image = :image,
      price = :price,
      is_active = :is_active
      WHERE id = :id";

    $stmt = $this->conn->prepare($query);

    $this->name = filter_var($this->name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->type = filter_var($this->type, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->size = filter_var($this->size, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->preparation = filter_var($this->preparation, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->description = filter_var($this->description, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->image = filter_var($this->image, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->price = filter_var($this->price, FILTER_VALIDATE_FLOAT);

    $stmt->bindValue(':name', $this->name);
    $stmt->bindValue(':type', $this->type);
    $stmt->bindValue(':size', $this->size);
    $stmt->bindValue(':preparation', $this->preparation);
    $stmt->bindValue(':description', $this->description);
    $stmt->bindValue(':image', $this->image);
    $stmt->bindValue(':price', $this->price);
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
}
