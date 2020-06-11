<?php

session_start();

require __DIR__ . "/vendor/autoload.php";

use gingerberry\db\DB;

$dbConn = DB::getInstance()::getPDO();

$dbConn = null;