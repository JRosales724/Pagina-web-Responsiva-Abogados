<?php
// class/class.agenda.php

require_once __DIR__ . '/class.conexion.php';

class Agenda
{
    protected $id;
    protected $id_cliente;
    protected $id_formulario;
    protected $nom_cliente;
    protected $num_tel_cliente;
    protected $correo_cliente;
    protected $direccion_cliente;
    protected $dependencia_laboraba;
    protected $tipo_de_cita;     // PRESENCIAL | TELEFONICA | VIDEO | CORREO
    protected $fecha_cita;       // YYYY-MM-DD
    protected $hora_cita;        // HH:MM:SS
    protected $lugar_cita;
    protected $enlace_cita;
    protected $estado_cita;      // programada | cancelada | realizada | no_asistio
    protected $notas_cita;
    protected $fecha_registro_cita;
    protected $cliente_registro_cita;
    protected $recordatorio_enviado_cita_cliente;
    protected $fecha_recordatorio_cita_cliente;
    protected $abogado_asignado;
    protected $recordatorio_enviado_cita_abogado;
    protected $fecha_recordatorio_cita_abogado;
    protected $comentarios_posteriores_cita_abogado;
    protected $desea_agendar_nuevamente_cliente;
    protected $recurrente;
    protected $prioridad_cita;   // baja | media | alta | critica
    protected $color;
    protected $archivos_adjuntos;
    protected $dbError;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->id_cliente = $data['id_cliente'] ?? null;
        $this->id_formulario = $data['id_formulario'] ?? null;
        $this->nom_cliente = $data['nom_cliente'] ?? null;
        $this->num_tel_cliente = $data['num_tel_cliente'] ?? null;
        $this->correo_cliente = $data['correo_cliente'] ?? null;
        $this->direccion_cliente = $data['direccion_cliente'] ?? null;
        $this->dependencia_laboraba = $data['dependencia_laboraba'] ?? null;
        $this->tipo_de_cita = $data['tipo_de_cita'] ?? 'VIDEO';
        $this->fecha_cita = $data['fecha_cita'] ?? null;
        $this->hora_cita = $data['hora_cita'] ?? null;
        $this->lugar_cita = $data['lugar_cita'] ?? null;
        $this->enlace_cita = $data['enlace_cita'] ?? null;
        $this->estado_cita = $data['estado_cita'] ?? 'programada';
        $this->notas_cita = $data['notas_cita'] ?? null;
        $this->fecha_registro_cita = $data['fecha_registro_cita'] ?? null;
        $this->cliente_registro_cita = $data['cliente_registro_cita'] ?? 'web';
        $this->recordatorio_enviado_cita_cliente = $data['recordatorio_enviado_cita_cliente'] ?? 0;
        $this->fecha_recordatorio_cita_cliente = $data['fecha_recordatorio_cita_cliente'] ?? null;
        $this->abogado_asignado = $data['abogado_asignado'] ?? null;
        $this->recordatorio_enviado_cita_abogado = $data['recordatorio_enviado_cita_abogado'] ?? 0;
        $this->fecha_recordatorio_cita_abogado = $data['fecha_recordatorio_cita_abogado'] ?? null;
        $this->comentarios_posteriores_cita_abogado = $data['comentarios_posteriores_cita_abogado'] ?? null;
        $this->desea_agendar_nuevamente_cliente = $data['desea_agendar_nuevamente_cliente'] ?? 0;
        $this->recurrente = $data['recurrente'] ?? 0;
        $this->prioridad_cita = $data['prioridad_cita'] ?? 'media';
        $this->color = $data['color'] ?? null;
        $this->archivos_adjuntos = $data['archivos_adjuntos'] ?? null;
        $this->dbError = '';
    }

    /* =================== Helpers base =================== */

    private static function prepareAndExecute($bd, string $sql, array $params)
    {
        $stmt = $bd->prepare($sql);
        if (!$stmt)
            return [false, null, $bd->error];
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute())
            return [false, null, $stmt->error];
        return [true, $stmt, null];
    }

    /** Comprueba si existe un constraint por nombre */
    private static function constraintExists(mysqli $db, string $constraint): bool
    {
        $sql = "SELECT 1
                FROM information_schema.REFERENTIAL_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = ?";
        [$ok, $stmt] = self::prepareAndExecute($db, $sql, [$constraint]);
        if (!$ok)
            return false;
        $res = $stmt->get_result();
        return $res && $res->num_rows > 0;
    }

    /** Comprueba si existe una tabla en el schema actual */
    private static function tableExists(mysqli $db, string $table): bool
    {
        $sql = "SELECT 1
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
        [$ok, $stmt] = self::prepareAndExecute($db, $sql, [$table]);
        if (!$ok)
            return false;
        $res = $stmt->get_result();
        return $res && $res->num_rows > 0;
    }

    /** Crea agenda, ajusta tipos y agrega FKs solo si las tablas existen y los tipos coinciden */
    public static function ensureTable(): bool
    {
        $bd = new Conexion();

        // 1) Crear base SIN FKs
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS agenda (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

          -- ¡OJO!: tipos pensados para coincidir con tablas de destino
          -- usuarios.id  -> INT (firmado)
          id_cliente INT NULL,

          -- formulario.id -> INT UNSIGNED
          id_formulario INT UNSIGNED NULL,

          nom_cliente VARCHAR(255) NULL,
          num_tel_cliente VARCHAR(30) NULL,
          correo_cliente VARCHAR(255) NULL,
          direccion_cliente VARCHAR(255) NULL,
          dependencia_laboraba VARCHAR(255) NULL,
          tipo_de_cita ENUM('PRESENCIAL','TELEFONICA','VIDEO','CORREO') NOT NULL DEFAULT 'VIDEO',
          fecha_cita DATE NOT NULL,
          hora_cita TIME NOT NULL,
          lugar_cita VARCHAR(255) NULL,
          enlace_cita VARCHAR(500) NULL,
          estado_cita ENUM('programada','cancelada','realizada','no_asistio') NOT NULL DEFAULT 'programada',
          notas_cita TEXT NULL,
          fecha_registro_cita DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          cliente_registro_cita VARCHAR(255) NULL,
          recordatorio_enviado_cita_cliente TINYINT(1) NOT NULL DEFAULT 0,
          fecha_recordatorio_cita_cliente DATETIME NULL,
          abogado_asignado VARCHAR(255) NULL,
          recordatorio_enviado_cita_abogado TINYINT(1) NOT NULL DEFAULT 0,
          fecha_recordatorio_cita_abogado DATETIME NULL,
          comentarios_posteriores_cita_abogado TEXT NULL,
          desea_agendar_nuevamente_cliente TINYINT(1) NOT NULL DEFAULT 0,
          recurrente TINYINT(1) NOT NULL DEFAULT 0,
          prioridad_cita ENUM('baja','media','alta','critica') DEFAULT 'media',
          color VARCHAR(20) NULL,
          archivos_adjuntos VARCHAR(500) NULL,

          UNIQUE KEY uk_agenda_slot (fecha_cita, hora_cita, tipo_de_cita),
          KEY idx_agenda_cliente_fecha (id_cliente, fecha_cita),
          KEY idx_agenda_formulario (id_formulario)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL;

        if (!$bd->query($sql)) {
            return false;
        }

        // 2) Si existen las tablas referenciadas, agregamos FKs (evitando error 150)
        $usuariosOk = self::tableExists($bd, 'usuarios');
        $formularioOk = self::tableExists($bd, 'formulario');

        // FK -> usuarios(id)  (id_cliente INT firmado)
        if ($usuariosOk && !self::constraintExists($bd, 'fk_agenda_usuarios')) {
            // Por seguridad, aseguremos que id_cliente sea INT firmado
            $bd->query("ALTER TABLE agenda MODIFY COLUMN id_cliente INT NULL");
            $sqlFk1 = "ALTER TABLE agenda
                       ADD CONSTRAINT fk_agenda_usuarios
                       FOREIGN KEY (id_cliente)
                       REFERENCES usuarios(id)
                       ON DELETE SET NULL
                       ON UPDATE CASCADE";
            if (!$bd->query($sqlFk1)) {
                // Si algo pasa, no fallamos toda la función; dejamos registrada la razón
                // error típico: tipo no coincide en tablas antiguas
                // Puedes revisar con $this->dbError si conviertes a no-estático
            }
        }

        // FK -> formulario(id) (id_formulario INT UNSIGNED)
        if ($formularioOk && !self::constraintExists($bd, 'fk_agenda_formulario')) {
            // Asegurar UNSIGNED
            $bd->query("ALTER TABLE agenda MODIFY COLUMN id_formulario INT UNSIGNED NULL");
            $sqlFk2 = "ALTER TABLE agenda
                       ADD CONSTRAINT fk_agenda_formulario
                       FOREIGN KEY (id_formulario)
                       REFERENCES formulario(id)
                       ON DELETE SET NULL
                       ON UPDATE CASCADE";
            if (!$bd->query($sqlFk2)) {
                // idem comentario anterior
            }
        }

        return true;
    }

    /* =============== Reglas de disponibilidad =============== */

    public static function isSlotDisponible(string $fecha, string $hora, string $tipo): bool
    {
        $bd = new Conexion();
        $sql = "SELECT 1 FROM agenda WHERE fecha_cita=? AND hora_cita=? AND tipo_de_cita=? AND estado_cita='programada' LIMIT 1";
        [$ok, $stmt] = self::prepareAndExecute($bd, $sql, [$fecha, $hora, $tipo]);
        if (!$ok)
            return false;
        $res = $stmt->get_result();
        return $res->num_rows === 0;
    }

    public static function clienteTieneCitaEseDia(int $idCliente, string $fecha): bool
    {
        $bd = new Conexion();
        $sql = "SELECT 1 FROM agenda WHERE id_cliente=? AND fecha_cita=? AND estado_cita='programada' LIMIT 1";
        [$ok, $stmt] = self::prepareAndExecute($bd, $sql, [$idCliente, $fecha]);
        if (!$ok)
            return true;
        $res = $stmt->get_result();
        return $res->num_rows > 0;
    }

    /* =================== CRUD principal =================== */

    public function create(): bool
    {
        self::ensureTable();
        $this->dbError = '';

        $bd = new Conexion();
        $sql = "INSERT INTO agenda
            (id_cliente, id_formulario, nom_cliente, num_tel_cliente, correo_cliente, direccion_cliente, dependencia_laboraba,
             tipo_de_cita, fecha_cita, hora_cita, lugar_cita, enlace_cita, estado_cita, notas_cita,
             cliente_registro_cita, prioridad_cita, color, archivos_adjuntos)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $params = [
            $this->id_cliente,
            $this->id_formulario,
            $this->nom_cliente,
            $this->num_tel_cliente,
            $this->correo_cliente,
            $this->direccion_cliente,
            $this->dependencia_laboraba,
            $this->tipo_de_cita,
            $this->fecha_cita,
            $this->hora_cita,
            $this->lugar_cita,
            $this->enlace_cita,
            $this->estado_cita,
            $this->notas_cita,
            $this->cliente_registro_cita,
            $this->prioridad_cita,
            $this->color,
            $this->archivos_adjuntos
        ];

        [$ok, $stmt, $err] = self::prepareAndExecute($bd, $sql, $params);
        if (!$ok) {
            $this->dbError = $err;
            return false;
        }
        $this->id = $stmt->insert_id;
        return true;
    }

    public static function readById(int $id)
    {
        $bd = new Conexion();
        $sql = "SELECT * FROM agenda WHERE id=? LIMIT 1";
        [$ok, $stmt] = self::prepareAndExecute($bd, $sql, [$id]);
        if (!$ok)
            return false;
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    public static function readAll(int $limit = 100, int $offset = 0)
    {
        $bd = new Conexion();
        $sql = "SELECT * FROM agenda ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = $bd->prepare($sql);
        if (!$stmt)
            return false;
        $stmt->bind_param('ii', $limit, $offset);
        if (!$stmt->execute())
            return false;
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc())
            $rows[] = $r;
        return $rows;
    }

    public static function listByDateRange(string $from, string $to)
    {
        $bd = new Conexion();
        $sql = "SELECT * FROM agenda WHERE fecha_cita BETWEEN ? AND ? ORDER BY fecha_cita ASC, hora_cita ASC";
        [$ok, $stmt] = self::prepareAndExecute($bd, $sql, [$from, $to]);
        if (!$ok)
            return false;
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc())
            $rows[] = $r;
        return $rows;
    }

    public static function cancel(int $id, string $motivo = null): bool
    {
        $bd = new Conexion();
        $sql = "UPDATE agenda SET estado_cita='cancelada', notas_cita=CONCAT(COALESCE(notas_cita,''), ?, CASE WHEN ? IS NULL THEN '' ELSE CONCAT(' Motivo: ', ?) END) WHERE id=?";
        [$ok, , $err] = self::prepareAndExecute($bd, $sql, ["", $motivo, $motivo, $id]);
        return $ok ?: false;
    }

    public static function markAs(int $id, string $estado): bool
    {
        $permitidos = ['programada', 'realizada', 'no_asistio', 'cancelada'];
        if (!in_array($estado, $permitidos, true))
            return false;
        $bd = new Conexion();
        $sql = "UPDATE agenda SET estado_cita=? WHERE id=?";
        [$ok, ,] = self::prepareAndExecute($bd, $sql, [$estado, $id]);
        return $ok ?: false;
    }

    public function getId()
    {
        return $this->id;
    }
    public function getDbError()
    {
        return $this->dbError;
    }

    public function setFechaHora(string $fecha, string $hora)
    {
        $this->fecha_cita = $fecha;
        $this->hora_cita = $hora;
    }
    public function setTipo(string $tipo)
    {
        $this->tipo_de_cita = $tipo;
    }
    public function setCliente(int $idCliente, ?int $idFormulario = null)
    {
        $this->id_cliente = $idCliente;
        $this->id_formulario = $idFormulario;
    }
}
