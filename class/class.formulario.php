<?php
// class/class.formulario.php
require_once __DIR__ . '/class.conexion.php';

class Formulario
{
    /** @var Conexion */
    protected $db;
    protected $dbError = '';

    // Identificadores
    protected $id;
    protected $id_usuario; // FK a usuarios.id (nullable)

    // Preguntas base
    protected $pensionado_federal;
    protected $tipo_tramite;
    protected $trabajador_activo;

    // Cambio de régimen
    protected $cambio_regimen_pre2007;
    protected $cambio_regimen_continua;
    protected $cambio_regimen_reingreso_2007;
    protected $cambio_regimen_eligio_cuent_indivi;
    protected $cambio_regimen_post2007;

    // Incremento de pensión
    protected $incre_pensi_rango;
    protected $incre_pensi_importe;

    // Jubilación / Invalidez
    protected $jubil_invalid_rango;
    protected $jubil_invalid_importe;

    // Riesgo de trabajo
    protected $riesgo_trab_modalidad;
    protected $riesgo_trab_rango;
    protected $riesgo_trab_importe;
    protected $riesgo_trab_total_fecha;
    protected $riesgo_trab_parcial_fecha;
    protected $riesgo_trab_parcial_porcentaje;

    // Cancelada / no pagan
    protected $descuento_en_talon;

    // Pensión negada
    protected $negaron_pension;
    protected $tipo_pension_negada;
    protected $riesgo_trab_dictamen;
    protected $riesgo_trab_dictamen_estado;
    protected $invalidez_dictamen;

    // Contacto
    protected $nombre;
    protected $apellido_paterno;
    protected $apellido_materno;
    protected $telefono;
    protected $correo;
    protected $dependencia_laboraba;
    protected $comentarios;

    // Preferencia de contacto / cita
    protected $pref_contact;
    protected $appt_date;
    protected $appt_time;

    // Tiempos
    protected $created_at;

    // Flujo
    protected $flow_answers = [];
    protected $flow_last_end;

    // Nombres de tablas
    private const TABLE_MAIN = 'formulario';
    private const TABLE_RESP = 'formulario_respuestas';

    // ============================================================
    // CONSTRUCTOR
    // ============================================================
    public function __construct(Conexion $db, array $data = [])
    {
        $this->db = $db;
        foreach ($data as $k => $v) {
            if (property_exists($this, $k))
                $this->$k = $v;
        }
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->flow_answers = is_array($data['flow_answers'] ?? null) ? $data['flow_answers'] : [];
        $this->flow_last_end = $data['flow_last_end'] ?? null;
    }

    // ============================================================
    // ESTRUCTURA DE TABLAS
    // ============================================================
    protected function ensureTable(): bool
    {
        // 1) Crear la tabla básica (sin FK incrustada)
        $sqlCreate = "
    CREATE TABLE IF NOT EXISTS `formulario` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_usuario` INT NULL,
        `PENSIONADO_FEDERAL`    ENUM('si','no') NULL,
        `TIPO_TRAMITE`          ENUM('incremento','jubilacion_invalidez','riesgo_trabajo','cancelada_no_pagan') NULL,
        `TRABAJADOR_ACTIVO`     ENUM('si','no') NULL,
        `CAMBIO_REGIMEN_PRE2007`            ENUM('si','no') NULL,
        `CAMBIO_REGIMEN_CONTINUA`           ENUM('si','no') NULL,
        `CAMBIO_REGIMEN_REINGRESO_2007`     ENUM('si','no') NULL,
        `CAMBIO_REGIMEN_ELIGIO_CUENT_INDIVI` ENUM('si','no') NULL,
        `CAMBIO_REGIMEN_POST2007`           ENUM('si','no') NULL,

        `INCRE_PENSI_RANGO`     VARCHAR(16) NULL,
        `INCRE_PENSI_IMPORTE`   DECIMAL(12,2) NULL,

        `JUBIL_INVALID_RANGO`   VARCHAR(16) NULL,
        `JUBIL_INVALID_IMPORTE` DECIMAL(12,2) NULL,

        `RIESGO_TRAB_MODALIDAD` ENUM('parcial','total') NULL,
        `RIESGO_TRAB_RANGO`     VARCHAR(16) NULL,
        `RIESGO_TRAB_IMPORTE`   DECIMAL(12,2) NULL,
        `RIESGO_TRAB_TOTAL_FECHA`   DATE NULL,
        `RIESGO_TRAB_PARCIAL_FECHA` DATE NULL,
        `RIESGO_TRAB_PARCIAL_PORCENTAJE` VARCHAR(8) NULL,

        `DESCUENTO_EN_TALON`    ENUM('si','no') NULL,

        `NEGARON_PENSION`       ENUM('si','no') NULL,
        `TIPO_PENSION_NEGADA`   ENUM('viudez','riesgo','invalidez') NULL,
        `RIESGO_TRAB_DICTAMEN`  ENUM('si','no') NULL,
        `RIESGO_TRAB_DICTAMEN_ESTADO` ENUM('aprobado','negado') NULL,
        `INVALIDEZ_DICTAMEN`    ENUM('si_negado','no') NULL,

        `NOMBRE`                VARCHAR(120) NULL,
        `APELLIDO_PATERNO`      VARCHAR(120) NULL,
        `APELLIDO_MATERNO`      VARCHAR(120) NULL,
        `TELEFONO`              VARCHAR(32) NULL,
        `CORREO`                VARCHAR(150) NULL,
        `DEPENDENCIA_LABORABA`  VARCHAR(200) NULL,
        `COMENTARIOS`           TEXT NULL,

        `PREF_CONTACT`          ENUM('mail','call','none') NULL,
        `APPT_DATE`             DATE NULL,
        `APPT_TIME`             TIME NULL,

        `CREATED_AT`            DATETIME NULL,

        `flow_json`             JSON NULL,
        `flow_path`             TEXT NULL,
        `flow_last_end`         VARCHAR(64) NULL,

        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        if (!$this->db->query($sqlCreate)) {
            $this->dbError = $this->db->error;
            return false;
        }

        // 2) Asegurar índice sobre id_usuario solo si no existe
        $idxName = 'idx_formulario_id_usuario';
        $qIdx = "SELECT 1
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'formulario'
               AND INDEX_NAME = '{$idxName}'
             LIMIT 1";
        $hasIdx = $this->db->query($qIdx);
        if ($hasIdx && $hasIdx->num_rows === 0) {
            if (!$this->db->query("CREATE INDEX `{$idxName}` ON `formulario`(`id_usuario`)")) {
                $this->dbError = $this->db->error;
                return false;
            }
        }

        // 3) Asegurar la FK solo si no existe (nombre ÚNICO en todo el schema)
        $fkName = 'fk_formulario_usuarios_id_usuario';
        $qFk = "SELECT 1
            FROM information_schema.REFERENTIAL_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND CONSTRAINT_NAME = '{$fkName}'
            LIMIT 1";
        $hasFk = $this->db->query($qFk);
        if ($hasFk && $hasFk->num_rows === 0) {
            $sqlFk = "ALTER TABLE `formulario`
                  ADD CONSTRAINT `{$fkName}`
                  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`)
                  ON DELETE SET NULL
                  ON UPDATE CASCADE";
            if (!$this->db->query($sqlFk)) {
                $this->dbError = $this->db->error;
                return false;
            }
        }

        return true;
    }

    protected function ensureRespuestasTable(): bool
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `" . self::TABLE_RESP . "` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `formulario_id` INT UNSIGNED NOT NULL,
            `orden` INT UNSIGNED NOT NULL,
            `pregunta` VARCHAR(120) NOT NULL,
            `valor` TEXT NULL,
            `valor_label` TEXT NULL,
            `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_formulario_respuestas_form` (`formulario_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        return (bool) $this->db->query($sql);
    }

    // ============================================================
    // CRUD
    // ============================================================
    public function create(): bool
    {
        if (!$this->ensureTable() || !$this->ensureRespuestasTable())
            return false;

        $data = $this->toColumnArray();
        if (empty($data)) {
            $this->dbError = 'No hay datos para insertar.';
            return false;
        }

        // Campos de flujo
        if (!empty($this->flow_answers)) {
            $data['flow_json'] = json_encode($this->flow_answers, JSON_UNESCAPED_UNICODE);
            $data['flow_path'] = implode(' > ', array_map(
                fn($k, $v) => "$k=" . (is_scalar($v) ? $v : json_encode($v)),
                array_keys($this->flow_answers),
                $this->flow_answers
            ));
        }
        if (!empty($this->flow_last_end))
            $data['flow_last_end'] = $this->flow_last_end;

        // Insert principal
        $cols = array_keys($data);
        $sql = "INSERT INTO `" . self::TABLE_MAIN . "` (`" . implode('`,`', $cols) . "`) VALUES (" .
            implode(',', array_fill(0, count($cols), '?')) . ")";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            $this->dbError = $this->db->error;
            return false;
        }
        $types = str_repeat('s', count($cols));
        $vals = array_values($data);
        $stmt->bind_param($types, ...$vals);
        if (!$stmt->execute()) {
            $this->dbError = $stmt->error;
            $stmt->close();
            return false;
        }
        $this->id = (int) $this->db->insert_id;
        $stmt->close();

        // Respuestas normalizadas
        if (!empty($this->flow_answers)) {
            $stmtR = $this->db->prepare("INSERT INTO `" . self::TABLE_RESP . "` 
                (formulario_id, orden, pregunta, valor, valor_label) VALUES (?,?,?,?,?)");
            if ($stmtR) {
                $orden = 1;
                foreach ($this->flow_answers as $k => $v) {
                    $val = is_scalar($v) ? (string) $v : json_encode($v);
                    $stmtR->bind_param('iisss', $this->id, $orden, $k, $val, $val);
                    $stmtR->execute();
                    $orden++;
                }
                $stmtR->close();
            }
        }
        return true;
    }

    protected function toColumnArray(): array
    {
        $fields = get_object_vars($this);
        $ignore = ['db', 'dbError', 'flow_answers'];
        foreach ($ignore as $i)
            unset($fields[$i]);
        $filtered = array_filter($fields, fn($v) => $v !== null && $v !== '');
        return $filtered;
    }

    // ============================================================
    // GETTERS
    // ============================================================
    public function getId()
    {
        return $this->id;
    }
    public function getDbError(): string
    {
        return $this->dbError;
    }
    public function getFlowAnswers(): array
    {
        return $this->flow_answers;
    }

    // ============================================================
    // STATIC CONSTRUCTOR
    // ============================================================
    public static function fromPayload(Conexion $db, array $payload): self
    {
        $data = $payload;
        if (isset($payload['respuestas']))
            $data['flow_answers'] = $payload['respuestas'];
        if (isset($payload['last_end']))
            $data['flow_last_end'] = $payload['last_end'];
        if (isset($payload['id_usuario']))
            $data['id_usuario'] = $payload['id_usuario'];
        return new self($db, $data);
    }
}
