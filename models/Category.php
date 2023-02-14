<?php

namespace App\models;

use PDO;

class Category extends Model
{
    private $table = 'categories';

    public $id;
    public $name;

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

    public function validationRules(): array
    {
        return [];
    }
}