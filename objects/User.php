<?php
include_once 'BREAD.php';

class User implements BREAD
{
  private $conn;
  private $table_name = "users";
  
  public $id;
  public $email;
  public $first_name;
  public $last_name;
  public $password_hash;
  public $role;
  public $balance;
  public $is_active;
  public $email_verified;
  public $email_verification_token;
  public $email_verification_expires;
  public $avatar;

  public function __construct($db)
  {
    $this->conn = $db;
  }

  function browse()
  {
    $query = "SELECT * FROM " . $this->table_name . " ORDER BY first_name ASC";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt;
  }

  /**
   * @return void
   */
  function read()
  {
    $query = "SELECT *
      FROM " . $this->table_name . "
      WHERE id = :ID
      LIMIT 0,1";

    $stmt = $this->conn->prepare($query);
    $id = filter_var($this->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $stmt->bindValue(":ID", $id);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      $this->id = $row['id'];
      $this->first_name = $row['first_name'];
      $this->last_name = $row['last_name'];
      $this->email = $row['email'];
      $this->role = $row['role'];
      $this->balance = $row['balance'];
      $this->password_hash = $row['password_hash'];
      $this->is_active = boolval($row['is_active']);
      $this->email_verified = boolval($row['email_verified']);
      $this->email_verification_token = $row['email_verification_token'];
      $this->email_verification_expires = $row['email_verification_expires'];
      $this->avatar = $row['avatar'] ?? null;
    }
  }

  /**
   * @return boolean
   */
  public function add()
  {
    $query = "INSERT INTO " . $this->table_name . "
      SET
      email = :email,
      first_name = :first_name,
      last_name = :last_name,
      password_hash = :password_hash,
      role = 'customer',
      balance = 0.0,
      email_verified = 0,
      email_verification_token = :token,
      email_verification_expires = :expires";
      
    $stmt = $this->conn->prepare($query);
    $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);

    // tentar extrair nome do email
    $username = preg_split('/[._-]/', explode('@', $this->email)[0]);
    $this->first_name = ucfirst($username[0] ?? 'User');
    $this->last_name = ucfirst($username[1] ?? '');

    $stmt->bindValue(':first_name', $this->first_name);
    $stmt->bindValue(':last_name', $this->last_name);
    $stmt->bindValue(':email', $this->email);
    $stmt->bindValue(':password_hash', password_hash($this->password_hash, PASSWORD_DEFAULT));

    // token válido por 24h
    $this->email_verification_token = bin2hex(random_bytes(32));
    $this->email_verification_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
    $stmt->bindValue(':token', $this->email_verification_token);
    $stmt->bindValue(':expires', $this->email_verification_expires);

    if ($stmt->execute()) {
      $this->id = $this->conn->lastInsertId();
      return true;
    }

    return false;
  }

  /**
   * @return boolean
   */
  public function edit()
  {
    $password_set = !empty($this->password_hash) ? ", password_hash = :password_hash" : "";
    $avatar_set = !empty($this->avatar) ? ", avatar = :avatar" : "";

    $query = "UPDATE " . $this->table_name . "
      SET
      first_name = :first_name,
      last_name = :last_name,
      email = :email
    {$password_set}
    {$avatar_set}
    WHERE id = :id";

    $stmt = $this->conn->prepare($query);

    $this->first_name = filter_var($this->first_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->last_name = filter_var($this->last_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);
    $this->password_hash = !empty($this->password_hash) ? filter_var($this->password_hash) : '';
    $this->avatar = !empty($this->avatar) ? filter_var($this->avatar, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

    $stmt->bindValue(':first_name', $this->first_name);
    $stmt->bindValue(':last_name', $this->last_name);
    $stmt->bindValue(':email', $this->email);

    if (!empty($this->password_hash)) {
      $stmt->bindValue(':password_hash', password_hash($this->password_hash, PASSWORD_DEFAULT));
    }
    
    if (!empty($this->avatar)) {
      $stmt->bindValue(':avatar', $this->avatar);
    }

    $stmt->bindValue(':id', $this->id);

    if ($stmt->execute()) {
      return true;
    }

    return false;
  }

  /**
   * Admin edit - inclui role, balance e is_active
   * @return boolean
   */
  public function adminEdit()
  {
    $password_set = !empty($this->password_hash) ? ", password_hash = :password_hash" : "";
    $avatar_set = !empty($this->avatar) ? ", avatar = :avatar" : "";

    $query = "UPDATE " . $this->table_name . "
      SET
      first_name = :first_name,
      last_name = :last_name,
      email = :email,
      role = :role,
      balance = :balance,
      is_active = :is_active
    {$password_set}
    {$avatar_set}
    WHERE id = :id";

    $stmt = $this->conn->prepare($query);

    $this->first_name = filter_var($this->first_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->last_name = filter_var($this->last_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);
    $this->role = filter_var($this->role, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->balance = filter_var($this->balance, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $this->is_active = filter_var($this->is_active, FILTER_SANITIZE_NUMBER_INT);
    $this->password_hash = !empty($this->password_hash) ? filter_var($this->password_hash) : '';
    $this->avatar = !empty($this->avatar) ? filter_var($this->avatar, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

    $stmt->bindValue(':first_name', $this->first_name);
    $stmt->bindValue(':last_name', $this->last_name);
    $stmt->bindValue(':email', $this->email);
    $stmt->bindValue(':role', $this->role);
    $stmt->bindValue(':balance', $this->balance);
    $stmt->bindValue(':is_active', $this->is_active);

    if (!empty($this->password_hash)) {
      $stmt->bindValue(':password_hash', password_hash($this->password_hash, PASSWORD_DEFAULT));
    }
    
    if (!empty($this->avatar)) {
      $stmt->bindValue(':avatar', $this->avatar);
    }

    $stmt->bindValue(':id', $this->id);
    return $stmt->execute();
  }

  /**
   * @return boolean
   */
  function delete()
  {
    $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
    $stmt = $this->conn->prepare($query);
    $this->id = filter_var($this->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $stmt->bindValue(1, $this->id);
    return $stmt->execute();
  }

  function search($keywords)
  {
    $query = "SELECT *
      FROM " . $this->table_name . "
      WHERE
      first_name LIKE ? OR last_name LIKE ? OR email LIKE ?";

    $stmt = $this->conn->prepare($query);
    $keywords = filter_var($keywords, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $keywords = "%{$keywords}%";

    $stmt->bindValue(1, $keywords);
    $stmt->bindValue(2, $keywords);
    $stmt->bindValue(3, $keywords);
    $stmt->execute();

    return $stmt;
  }

  public function readPaging($from_record_num, $records_per_page)
  {
    $query = "SELECT *
      FROM " . $this->table_name . " 
      LIMIT ?, ?";

    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(1, $from_record_num, PDO::PARAM_INT);
    $stmt->bindValue(2, $records_per_page, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt;
  }

  public function count()
  {
    $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name;
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['total_rows'] : 0;
  }

  /**
   * @return boolean
   */
  public function emailExists()
  {
    $query = "SELECT *
      FROM " . $this->table_name . "
      WHERE email = ?
      LIMIT 0,1";

    $stmt = $this->conn->prepare($query);
    $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);
    $stmt->bindValue(1, $this->email);
    $stmt->execute();

    $num = $stmt->rowCount();

    if ($num > 0) {
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      $this->id = $row['id'];
      $this->first_name = $row['first_name'];
      $this->last_name = $row['last_name'];
      $this->password_hash = $row['password_hash'];
      $this->role = $row['role'];
      $this->balance = $row['balance'];
      $this->is_active = boolval($row['is_active']);
      $this->email_verified = boolval($row['email_verified']);
      $this->email_verification_token = $row['email_verification_token'];
      $this->email_verification_expires = $row['email_verification_expires'];
      $this->avatar = $row['avatar'] ?? null;

      return true;
    }
    return false;
  }

  /**
   * @param float $amount
   * @return boolean
   */
  public function deductBalance($amount)
  {
    if ($amount < 0 || $this->balance < $amount) {
      return false;
    }

    $this->balance -= $amount;

    $query = "UPDATE " . $this->table_name . "
      SET balance = :balance
      WHERE id = :id";

    $stmt = $this->conn->prepare($query);
    $balance = filter_var($this->balance, FILTER_VALIDATE_FLOAT);
    $stmt->bindValue(':balance', $balance);
    $stmt->bindValue(':id', $this->id);

    return $stmt->execute();
  }

  /**
   * @param float $amount
   * @return boolean
   */
  public function addBalance($amount)
  {
    if ($amount < 0) {
      return false;
    }

    $this->balance += $amount;

    $query = "UPDATE " . $this->table_name . "
      SET balance = :balance
      WHERE id = :id";

    $stmt = $this->conn->prepare($query);
    $balance = filter_var($this->balance, FILTER_VALIDATE_FLOAT);
    $stmt->bindValue(':balance', $balance);
    $stmt->bindValue(':id', $this->id);

    return $stmt->execute();
  }
}
