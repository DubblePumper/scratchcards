<?php
// Connection to database using credentials in config.php, also has functions for databases
require_once("databaseConfig.php");

// Make connection to database
function connect_to_database()
{
    // Set the DSN (Data Source Name)
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_DATABASE . ';port=' . DB_PORT . ';charset=utf8mb4';

    try {
        // Create a new PDO instance
        $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);

        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Set default fetch mode to associative array
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Set PDO to use prepared statements by default
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $pdo;
    } catch (PDOException $e) {
        // Handle connection error
        error_log("Connection failed: " . $e->getMessage(), 0);
        die("Connection failed. Please try again later.");
    }
}

?>