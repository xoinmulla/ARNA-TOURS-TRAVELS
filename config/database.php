<?php

declare(strict_types=1);

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $host = (string) (getenv('ARNA_DB_HOST') ?: 'localhost');
            $database = (string) (getenv('ARNA_DB_NAME') ?: 'arna_tours_travels');
            $username = (string) (getenv('ARNA_DB_USER') ?: 'root');
            $password = (string) (getenv('ARNA_DB_PASSWORD') ?: '');
            $port = (int) (getenv('ARNA_DB_PORT') ?: 3306);

            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            try {
                self::$connection = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                ]);
            } catch (PDOException $e) {
                error_log('Arna database connection failed: ' . $e->getMessage());
                throw new RuntimeException('Database connection failed. Please verify the application database configuration.');
            }
        }
        return self::$connection;
    }
}
