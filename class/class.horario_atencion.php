<?php

class atencion_cliente
{
    protected $id;
    protected $hora_inicial;
    protected $hora_termino;
    protected $dia_inicial;
    protected $dia_termino;
    protected $fines_semana;
    protected $dias_festivos;
    protected $vacaciones_inicio;
    protected $vacaciones_termino;
    protected $estatus;
    protected $actualizado_por;
    protected $fecha_utlima_actualizacion;
    protected $dbError;

    public function __construct($id, $hora_inicial, $hora_termino, $dia_inicial, $dia_termino, $fines_semana, $dias_festivos, $vacaciones_inicio, $vacaciones_termino, $estatus, $actualizado_por, $fecha_utlima_actualizacion)
    {
        $this->id = $id;
        $this->hora_inicial = $hora_inicial;
        $this->hora_termino = $hora_termino;
        $this->dia_inicial = $dia_inicial;
        $this->dia_termino = $dia_termino;
        $this->fines_semana = $fines_semana;
        $this->dias_festivos = $dias_festivos;
        $this->vacaciones_inicio = $vacaciones_inicio;
        $this->vacaciones_termino = $vacaciones_termino;
        $this->estatus = $estatus;
        $this->actualizado_por = $actualizado_por;
        $this->fecha_utlima_actualizacion = $fecha_utlima_actualizacion;
        $this->dbError = '';
    }

    public function create()
    {
        $bd = new conexion();
        $ct = "SHOW TABLES LIKE 'atencion_cliente'";
        $h = $bd->query($ct);

        $sql = "INSERT INTO atencion_cliente (HORA_INICIAL, HORA_TERMINO, DIA_INICIAL, DIA_TERMINO, FINES_SEMANA, DIAS_FESTIVOS, VACACIONES_INICIO, VACACIONES_TERMINO, ESTATUS, ACTUALIZADO_POR, FECHA_UTLIMA_ACTUALIZACION) 
                VALUES ('{$this->hora_inicial}', '{$this->hora_termino}', '{$this->dia_inicial}', '{$this->dia_termino}', '{$this->fines_semana}','{$this->dias_festivos}','{$this->vacaciones_inicio}','{$this->vacaciones_termino}','{$this->estatus}','{$this->actualizado_por}', {$this->fecha_utlima_actualizacion})";

        if ($h->num_rows < 1) {
            // Si la tabla 'admins' no existe, crea la tabla y luego realiza la inserción
            $sqlCreateTable = "CREATE TABLE atencion_cliente (
                                ID INT AUTO_INCREMENT PRIMARY KEY,
                                HORA_INICIAL VARCHAR(255),
                                HORA_TERMINO VARCHAR(255),
                                DIA_INICIAL VARCHAR(255),
                                DIA_TERMINO VARCHAR(255),
                                FINES_SEMANA VARCHAR(255),
                                DIAS_FESTIVOS VARCHAR(255),
                                VACACIONES_INICIO VARCHAR(255),
                                VACACIONES_TERMINO VARCHAR(255),
                                ESTATUS VARCHAR(255),
                                ACTUALIZADO_POR VARCHAR(255),
                                FECHA_UTLIMA_ACTUALIZACION VARCHAR(255),
                            )";

            if (!$bd->query($sqlCreateTable)) {
                $this->dbError = $bd->error;
                return false;
            }
        }

        if (!$bd->query($sql)) {
            $this->dbError = $bd->error;
            return false;
        }

        return true;
    }

    public function getDbError()
    {
        return $this->dbError;
    }

    public function read($id = null)
    {
        $bd = new Conexion();

        if ($id !== null) {
            // Si se proporciona un ID, obtén ese registro específico
            $sql = "SELECT * FROM atencion_cliente WHERE ID = {$id}";
        } else {
            // Si no se proporciona un ID, obtén todos los registros ordenados por ID de mayor a menor
            $sql = "SELECT * FROM atencion_cliente ORDER BY ID DESC";
        }

        $result = $bd->query($sql);

        // Manejo del resultado (podrías devolverlo, imprimirlo, etc.)
        return $result;
    }
}
