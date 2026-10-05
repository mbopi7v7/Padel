<?php
final class Conex
{
    public $conexion;

    public function conectar()
    {
        $host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'mariadb';
        $usuario = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
        $password = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: 'root';
        $db = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'dw2_agenda';
        $puerto = (int) (getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306);

        $this->conexion = new mysqli($host, $usuario, $password, $db, $puerto);
        if ($this->conexion->connect_error) {
            error_log('Database connection failed: ' . $this->conexion->connect_error);
            die('Error de conexion');
        }

        return $this->conexion;
    }
}
