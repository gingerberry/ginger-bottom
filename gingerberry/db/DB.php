<?php

namespace gingerberry\db;

use gingerberry\Config;

class DB
{
	private static $dbConnection;
	private static $instance = null;

	private function __construct()
	{
		try {
			self::$dbConnection = new \PDO(
				"mysql:host=" . Config::DB_HOST . ";port=" . Config::DB_PORT . ";charset=utf8mb4;dbname=" . Config::DB_NAME . ";",
				Config::DB_USR,
				Config::DB_PWD
			);
			self::$dbConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		} catch (\PDOException $e) {
			throw new \Exception("Грешка при свързване с базата от данни: " . $e->getMessage());
		}
	}

	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new DB();
		}

		return self::$instance;
	}

	public static function getPDO()
	{
		return self::$dbConnection;
	}
}
