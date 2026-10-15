<?php
// Conexión PDO a la base de datos briefingtool.
// Los datos de acceso se leen de db_config.php (excluido del repositorio).

require_once __DIR__ . '/db_config.php';

function getConnection(): PDO
{
    global $host, $db_name, $username, $password;

    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}
