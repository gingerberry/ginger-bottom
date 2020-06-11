<?php

namespace gingerberry\db;

class DB
{
	private static $dbConnection;
	private static $instance = null;

	private function __construct()
	{
		$host = 'gingerberry.cwch0ro4xne5.us-east-1.rds.amazonaws.com';
		$port = '3306';
		$db = 'gingerberry';
		$usr = 'admin';
        $pwd = 'gingerberry';
		
		try {
			self::$dbConnection = new \PDO("mysql:host=$host;port=$port;charset=utf8mb4;dbname=$db;",
				$usr, $pwd);
			self::$dbConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		} catch (\PDOException $e) {
			throw new \Exception("Грешка при свързване с базата от данни: " . $e->getMessage());
		}
	}

	public static function getInstance()
	{
		if(!self::$instance) {
			self::$instance = new DB();
		}

		return self::$instance;
	}

	public static function getPDO()
	{
		return self::$dbConnection;
	}
}