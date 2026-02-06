<?php

namespace App\Models;

use App\Base\BaseModel;

class ExampleModel extends BaseModel {
    protected $table = 'examples';
    protected $primaryKey = 'id';
    
    /**
     * Example custom method
     * @param string $name
     * @return array|null
     */
    public function findByName($name) {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE name = :name",
            ['name' => $name]
        );
    }
}
