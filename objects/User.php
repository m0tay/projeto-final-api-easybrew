<?php
include_once 'BREAD.php';

class User implements BREAD
{

  // Ligação à BD e tabela
  private $conn;
  private $table_name = "users";
  // Propriedades
  public $id;
  public $email;
  public $first_name;
  public $last_name;
  public $password_hash;
  public $role;
  public $balance;
  public $is_active;

  // Construtor regista a ligação à Base de Dados
  public function __construct($db)
  {
    $this->conn = $db;
  }

  /**
   * Método para ler todos os elementos da tabela
   * @return PDOStatement Devolve PDOStatement com todos os elementos da tabela
   */
  function browse()
  {

    // SQL
    $query = "SELECT
      *
      FROM
      " . $this->table_name . "
      ORDER BY
      first_name ASC";

    // Preparar
    $stmt = $this->conn->prepare($query);

    // Executar
    $stmt->execute();

    return $stmt;
  }


  /**
   * Método para a inserção de um novo User na DB
   * @return boolean
   */
  public function add()
  {

    // insert query
    $query = "INSERT INTO " . $this->table_name . "
      SET
      email = :email,
      first_name = :first_name,
      last_name = :last_name,
      password_hash = :password_hash,
      role = 'customer',
      balance = 0.0";
    // prepare the query
    $stmt = $this->conn->prepare($query);

    // sanitize
    $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);

    // take first part from user email to attempt to form first and last names out of it
    $username = preg_split('/[._-]/', explode('@', $this->email)[0]);

    $this->first_name = ucfirst($username[0] ?? 'User');
    $this->last_name = ucfirst($username[1] ?? '');

    // bind the values
    $stmt->bindValue(':first_name', $this->first_name);
    $stmt->bindValue(':last_name', $this->last_name);
    $stmt->bindValue(':email', $this->email);

    // hash the password before saving to database
    // the name password_hash may be deceiving, but the $this->password_hash
    // at this point will be plain text still, only after this execution
    // it will be then definitely hashed
    $password_hash = password_hash($this->password_hash, PASSWORD_DEFAULT);
    $stmt->bindValue(':password_hash', $password_hash);

    // execute the query, also check if query was successful
    if ($stmt->execute()) {
      // Retrieve the auto-generated UUID
      $this->id = $this->conn->lastInsertId();
      return true;
    }

    return false;
  }

  /**
   * Método para carregar a informação de um registo da Base de Dados
   */
  function read()
  {

    // Gerar SQL para obter apenas um registo
    $query = "SELECT
      *
      FROM
      " . $this->table_name . "
      WHERE
      id = :ID
      LIMIT  0,1";

    // Preparar a query
    $stmt = $this->conn->prepare($query);

    // Filtar vairável e associar valor à query
    $id = filter_var($this->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $stmt->bindValue(":ID", $id);

    // Executar query
    $stmt->execute();

    // Obter resultado
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Instanciar propriedades da Classe
    if ($row) {
      $this->id = $row['id'];
      $this->first_name = $row['first_name'];
      $this->last_name = $row['last_name'];
      $this->email = $row['email'];
      $this->role = $row['role'];
      $this->balance = $row['balance'];
      $this->password_hash = $row['password_hash'];
      $this->is_active = $row['is_active'];
    }
  }

  /**
   * Atualizar um utilizador na base de dados
   * @return boolean
   */
  public function edit()
  {

    // if password needs to be updated
    $password_set = !empty($this->password_hash) ? ", password_hash = :password_hash" : "";

    // if no posted password, do not update the password
    $query = "UPDATE " . $this->table_name . "
      SET
      first_name = :first_name,
      last_name = :last_name,
      email = :email
    {$password_set}
    WHERE id = :id";

    // prepare the query
    $stmt = $this->conn->prepare($query);

    // sanitize
    $this->first_name = filter_var($this->first_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->last_name = filter_var($this->last_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);
    $this->password_hash = !empty($this->password_hash) ? filter_var($this->password_hash) : '';

    // bind the values from the form
    $stmt->bindValue(':first_name', $this->first_name);
    $stmt->bindValue(':last_name', $this->last_name);
    $stmt->bindValue(':email', $this->email);

    // hash the password before saving to database
    if (!empty($this->password_hash)) {
      $password_hash = password_hash($this->password_hash, PASSWORD_DEFAULT);
      $stmt->bindValue(':password_hash', $password_hash);
    }

    // unique ID of record to be edited
    $stmt->bindValue(':id', $this->id);

    // execute the query
    if ($stmt->execute()) {
      return true;
    }

    return false;
  }

  /**
   * Atualizar um utilizador (admin) - permite editar role, balance e is_active
   * @return boolean
   */
  public function adminEdit()
  {

    // if password needs to be updated
    $password_set = !empty($this->password_hash) ? ", password_hash = :password_hash" : "";

    $query = "UPDATE " . $this->table_name . "
      SET
      first_name = :first_name,
      last_name = :last_name,
      email = :email,
      role = :role,
      balance = :balance,
      is_active = :is_active
    {$password_set}
    WHERE id = :id";

    // prepare the query
    $stmt = $this->conn->prepare($query);

    // sanitize
    $this->first_name = filter_var($this->first_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->last_name = filter_var($this->last_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);
    $this->role = filter_var($this->role, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->balance = filter_var($this->balance, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $this->is_active = filter_var($this->is_active, FILTER_SANITIZE_NUMBER_INT);
    $this->password_hash = !empty($this->password_hash) ? filter_var($this->password_hash) : '';

    // bind the values from the form
    $stmt->bindValue(':first_name', $this->first_name);
    $stmt->bindValue(':last_name', $this->last_name);
    $stmt->bindValue(':email', $this->email);
    $stmt->bindValue(':role', $this->role);
    $stmt->bindValue(':balance', $this->balance);
    $stmt->bindValue(':is_active', $this->is_active);

    // hash the password before saving to database
    if (!empty($this->password_hash)) {
      $password_hash = password_hash($this->password_hash, PASSWORD_DEFAULT);
      $stmt->bindValue(':password_hash', $password_hash);
    }

    // unique ID of record to be edited
    $stmt->bindValue(':id', $this->id);

    // execute the query
    if ($stmt->execute()) {
      return true;
    }

    return false;
  }

  /**
   * Apaga registo
   * @return boolean
   */
  function delete()
  {

    // delete query
    $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

    // prepare query
    $stmt = $this->conn->prepare($query);

    // sanitize
    $this->id = filter_var($this->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // bind id of record to delete
    $stmt->bindValue(1, $this->id);

    // execute query
    if ($stmt->execute()) {
      return true;
    }

    return false;
  }

  // search 
  function search($keywords)
  {

    // select all query
    $query = "SELECT
      *
      FROM " . $this->table_name . "
      WHERE
      first_name LIKE ? OR last_name LIKE ? OR email LIKE ?";

    // prepare query statement
    $stmt = $this->conn->prepare($query);

    // sanitize
    $keywords = filter_var($keywords, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $keywords = "%{$keywords}%";

    // bind
    $stmt->bindValue(1, $keywords);
    $stmt->bindValue(2, $keywords);
    $stmt->bindValue(3, $keywords);

    // execute query
    $stmt->execute();

    return $stmt;
  }

  // read products with pagination
  public function readPaging($from_record_num, $records_per_page)
  {

    // select query
    $query = "SELECT
      *
      FROM
      " . $this->table_name . " 
      LIMIT ?, ?";

    // prepare query statement
    $stmt = $this->conn->prepare($query);

    // bind variable values
    $stmt->bindValue(1, $from_record_num, PDO::PARAM_INT);
    $stmt->bindValue(2, $records_per_page, PDO::PARAM_INT);

    // execute query
    $stmt->execute();

    // return values from database
    return $stmt;
  }

  // used for paging products
  public function count()
  {
    $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . "";

    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? $row['total_rows'] : 0;
  }

  /**
   * Verifica se um email existe na tabela de utilizadores
   * @return boolean
   */
  public function emailExists()
  {

    // query to check if email exists
    $query = "SELECT *
      FROM " . $this->table_name . "
      WHERE email = ?
      LIMIT 0,1";

    // prepare the query
    $stmt = $this->conn->prepare($query);

    // sanitize
    $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);

    // bind given email value
    $stmt->bindValue(1, $this->email);

    // execute the query
    $stmt->execute();

    // get number of rows
    $num = $stmt->rowCount();

    // if email exists, assign values to object properties for easy access and use for php sessions
    if ($num > 0) {

      // get record details / values
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      // assign values to object properties
      $this->id = $row['id'];
      $this->first_name = $row['first_name'];
      $this->last_name = $row['last_name'];
      $this->password_hash = $row['password_hash'];
      $this->role = $row['role'];
      $this->balance = $row['balance'];
      $this->is_active = $row['is_active'];

      // return true because email exists in the database
      return true;
    }
    // return false if email does not exist in the database
    return false;
  }
}
