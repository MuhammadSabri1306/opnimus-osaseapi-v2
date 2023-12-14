<?php
namespace App\Database;

use MeekroDB;

class OpnimusDatabase extends MeekroDB
{
    public function __construct(string $databaseName)
    {
        parent::__construct('10.62.175.4', 'admapp', 'weAREadmAPP!1!1', $databaseName);
    }
}