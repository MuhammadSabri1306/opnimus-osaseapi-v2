<?php
namespace App\Database;

use App\Database\Database;

class OpnimusDatabase extends Database
{
    public function __construct(string $databaseName)
    {
        parent::__construct('10.62.175.4', 'admapp', '4dm1N4Pp5!!', $databaseName);
    }
}