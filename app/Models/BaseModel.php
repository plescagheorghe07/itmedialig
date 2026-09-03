<?php

namespace App\Models;

use App\Core\DbHelper;
use PDO;

abstract class BaseModel
{
    public function __construct(protected PDO $db) {}

    protected function newUuid(): string
    {
        return DbHelper::newUuid($this->db);
    }

    protected function nowSql(): string
    {
        return DbHelper::nowSql($this->db);
    }

    protected function matchesTable(): string
    {
        return DbHelper::isMysql($this->db) ? '`matches`' : 'matches';
    }
}
