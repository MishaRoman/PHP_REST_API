<?php 

class Category
{
    private $conn;
    private $table = 'categories';

    public $id;
    public $name;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read(): array
    {
        $query = "SELECT
            id,
            name
          FROM " . $this->table;

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}