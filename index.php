<?php

session_start();

require __DIR__ . "/vendor/autoload.php";

use gingerberry\Test;

$test = new Test();

echo $test->sum();