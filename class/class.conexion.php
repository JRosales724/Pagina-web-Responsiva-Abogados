<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

class Conexion extends mysqli
{
    private $host = 'localhost';
    private $user = 'root';
    private $psw = '';
    private $database = 'AXM';

    public function __construct()
    {
        parent::__construct($this->host, $this->user, $this->psw, $this->database);
        if ($this->connect_errno) {
            die("Fallo la conexión: (" . $this->connect_errno . ") " . $this->connect_error);
        } else {
            $this->set_charset("utf8mb4"); // Configurar la conexión a la base de datos en UTF-8
        }
    }
}