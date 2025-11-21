<?php

class User
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
  function read()
  {

    // SQL
    $query = "SELECT
                *
            FROM
                " . $this->table_name . "
            ORDER BY
                username ASC";

    // Preparar
    $stmt = $this-conn->prepare($query);

    // Executar
    $stmt->execute();

    return $stmt;
  }


  /**
   * Método para a inserção de um novo User na DB
   * @return boolean
   */
  public function create()
  {

    // insert query
    $query = "INSERT INTO " . $this->table_name . "
            SET
                email = :email,
                first_name = :first_name,
                last_name = :last_name,
                password_hash = :password_hash,
                role = :role,
                balance = 0.0";
    // prepare the query
    $stmt = $this->conn->prepare($query);

    // sanitize
    $this->username = filter_var($this->username, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);
    $this->avatar = filter_var($this->avatar, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->password = filter_var($this->password);

    // bind the values
    $stmt->bindValue(':username', $this->username);
    $stmt->bindValue(':email', $this->email);
    $stmt->bindValue(':avatar', $this->avatar);

    // hash the password before saving to database
    $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
    $stmt->bindValue(':password', $password_hash);

    // execute the query, also check if query was successful
    if ($stmt->execute()) {
      return true;
    }

    return false;
  }

  /**
   * Método para carregar a informação de um registo da Base de Dados
   */
  function readOne(): void
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
    $id = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
    $stmt->bindValue(":ID", $id, PDO::PARAM_INT);

a   // Executar query
    $stmt->execute();

    // Obter resultado
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Instanciar propriedades da Classe
    $this->username = $row['username'];
    $this->email = $row['email'];
    $this->avatar = $row['avatar'];
    $this->password = $row['password'];
  }

  /**
   * Atualizar um utilizador na base de dados
   * @return boolean
   */
  public function update()
  {

    // if password needs to be updated
    $password_set = !empty($this->password) ? ", password = :password" : "";
    $avatar_set = !empty($this->avatar) ? ", avatar = :avatar" : "";

    // if no posted password, do not update the password
    $query = "UPDATE " . $this->table_name . "
            SET
                username = :username,
                email = :email
                {$avatar_set}
                {$password_set}
            WHERE id = :id";

    // prepare the query
    $stmt = $this->conn->prepare($query);

    // sanitize
    $this->username = filter_var($this->username, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->avatar = filter_var($this->avatar, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);
    $this->password = !empty($this->password) ? filter_var($this->password) : '';

    // bind the values from the form
    $stmt->bindValue(':username', $this->username);
    $stmt->bindValue(':email', $this->email);

    // hash the password before saving to database
    if (!empty($this->password)) {
      $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
      $stmt->bindValue(':password', $password_hash);
    }

    // 
    if (!empty($this->avatar)) {
      $stmt->bindValue(':avatar', $this->avatar);
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
    $this->id = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);

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
                username LIKE ? OR email LIKE ?";

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

    return $row['total_rows'];
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

      // return true because email exists in the database
      return true;
    }
    // return false if email does not exist in the database
    return false;
  }
}
