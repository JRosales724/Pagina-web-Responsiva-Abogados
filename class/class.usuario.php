<?php
// class/class.usuario.php
// Depende de class.conexion.php (Conexion extiende mysqli)
require_once __DIR__ . '/class.conexion.php';

class Usuario
{
    // Datos que se registran en el formulario inicial
    protected $id;
    protected $id_formulario;   // INT FK a formulario (opcional)
    protected $id_cita;         // INT FK a agenda
    protected $nombre;
    protected $apellido_paterno;
    protected $apellido_materno;
    protected $num_telefono;
    protected $correo;
    protected $contrasena;      // hash bcrypt o plano (se hashea automáticamente)
    protected $dependencia_laboraba;
    protected $comentarios;
    protected $fecha_registro;

    // Datos complementarios post registro inicial
    protected $fecha_nacimiento;
    protected $fecha_fallecimiento;
    protected $CURP;
    protected $RFC;
    protected $direccion_calle;
    protected $direccion_num_casa;
    protected $direccion_colonia;
    protected $direccion_ciudad;
    protected $direccion_estado;
    protected $direccion_codigo_postal;
    protected $nacionalidad;
    protected $telefono_alternativo;

    // Datos caso
    protected $Num_expediente;
    protected $tipo_caso;
    protected $folio_dictamen;
    protected $fecha_dictamen;
    protected $fecha_riesgo;
    protected $num_caso_interno;

    // Archivos complementarios
    protected $foto_perfil;
    protected $expediente;

    // DOCUMENTOS DEL EXPEDIENTE EN PDF
    protected $estado_cuenta;
    protected $talon_pago;
    protected $INE;
    protected $doc_pdf_curp;
    protected $doc_pdf_rfc;
    protected $comp_domicilio;
    protected $dictamen_rt09;
    protected $dictamen_riesgo;
    protected $acta_matrimonio;
    protected $acta_nacimiento;

    // ESTADO DEL EXPEDIENTE
    protected $estado_validacion;
    protected $administrador_valido_archivos;

    // Personalizacion y configuracion
    protected $idioma_preferido;
    protected $zona_horaria;
    protected $preferencias_notificacion; // JSON (o array)
    protected $redes_sociales;            // JSON (o array)
    protected $metodo_pago;
    protected $pago_referente_al_concepto_de;
    protected $ultima_actualizacion_datos;
    protected $ultima_actualizacion_archivos;
    protected $ultima_conexion;
    protected $dispositivos_conectados;   // JSON (o array)
    protected $token_validacion;
    protected $token_cambio_contrasena;
    protected $ultimo_cambio_contrasena;

    // Meta
    protected $dbError;
    protected $temp_password; // clara solo en memoria para comunicar al usuario (no se guarda)

    // Opciones de hashing
    private const PASSWORD_ALGO = PASSWORD_BCRYPT;
    private const PASSWORD_OPTS = ['cost' => 11];

    public function __construct(array $data = [])
    {
        // Campos base
        $this->id = $data['id'] ?? null;
        $this->id_formulario = $data['id_formulario'] ?? null;
        $this->id_cita = $data['id_cita'] ?? null;
        $this->nombre = $data['nombre'] ?? null;
        $this->apellido_paterno = $data['apellido_paterno'] ?? null;
        $this->apellido_materno = $data['apellido_materno'] ?? null;
        $this->num_telefono = $data['num_telefono'] ?? null;
        $this->correo = $data['correo'] ?? null;
        $this->contrasena = $data['contrasena'] ?? null; // en claro (opcional) o ya hash
        $this->dependencia_laboraba = $data['dependencia_laboraba'] ?? null;
        $this->comentarios = $data['comentarios'] ?? null;
        $this->fecha_registro = $data['fecha_registro'] ?? null;

        // Complementarios
        $this->fecha_nacimiento = $data['fecha_nacimiento'] ?? null;
        $this->fecha_fallecimiento = $data['fecha_fallecimiento'] ?? null;
        $this->CURP = $data['CURP'] ?? null;
        $this->RFC = $data['RFC'] ?? null;
        $this->direccion_calle = $data['direccion_calle'] ?? null;
        $this->direccion_num_casa = $data['direccion_num_casa'] ?? null;
        $this->direccion_colonia = $data['direccion_colonia'] ?? null;
        $this->direccion_ciudad = $data['direccion_ciudad'] ?? null;
        $this->direccion_estado = $data['direccion_estado'] ?? null;
        $this->direccion_codigo_postal = $data['direccion_codigo_postal'] ?? null;
        $this->nacionalidad = $data['nacionalidad'] ?? null;
        $this->telefono_alternativo = $data['telefono_alternativo'] ?? null;

        // Caso
        $this->Num_expediente = $data['Num_expediente'] ?? null;
        $this->tipo_caso = $data['tipo_caso'] ?? null;
        $this->folio_dictamen = $data['folio_dictamen'] ?? null;
        $this->fecha_dictamen = $data['fecha_dictamen'] ?? null;
        $this->fecha_riesgo = $data['fecha_riesgo'] ?? null;
        $this->num_caso_interno = $data['num_caso_interno'] ?? null;

        // Archivos
        $this->foto_perfil = $data['foto_perfil'] ?? null;
        $this->expediente = $data['expediente'] ?? null;

        // Documentos PDF
        $this->estado_cuenta = $data['estado_cuenta'] ?? null;
        $this->talon_pago = $data['talon_pago'] ?? null;
        $this->INE = $data['INE'] ?? null;
        $this->doc_pdf_curp = $data['doc_pdf_curp'] ?? null;
        $this->doc_pdf_rfc = $data['doc_pdf_rfc'] ?? null;
        $this->comp_domicilio = $data['comp_domicilio'] ?? null;
        $this->dictamen_rt09 = $data['dictamen_rt09'] ?? null;
        $this->dictamen_riesgo = $data['dictamen_riesgo'] ?? null;
        $this->acta_matrimonio = $data['acta_matrimonio'] ?? null;
        $this->acta_nacimiento = $data['acta_nacimiento'] ?? null;

        // Estado expediente
        $this->estado_validacion = $data['estado_validacion'] ?? 'pendiente';
        $this->administrador_valido_archivos = $data['administrador_valido_archivos'] ?? null;

        // Personalización
        $this->idioma_preferido = $data['idioma_preferido'] ?? 'es';
        $this->zona_horaria = $data['zona_horaria'] ?? 'America/Mexico_City';
        $this->preferencias_notificacion = $data['preferencias_notificacion'] ?? null;
        $this->redes_sociales = $data['redes_sociales'] ?? null;
        $this->metodo_pago = $data['metodo_pago'] ?? null;
        $this->pago_referente_al_concepto_de = $data['pago_referente_al_concepto_de'] ?? null;
        $this->ultima_actualizacion_datos = $data['ultima_actualizacion_datos'] ?? null;
        $this->ultima_actualizacion_archivos = $data['ultima_actualizacion_archivos'] ?? null;
        $this->ultima_conexion = $data['ultima_conexion'] ?? null;
        $this->dispositivos_conectados = $data['dispositivos_conectados'] ?? null;
        $this->token_validacion = $data['token_validacion'] ?? null;
        $this->token_cambio_contrasena = $data['token_cambio_contrasena'] ?? null;
        $this->ultimo_cambio_contrasena = $data['ultimo_cambio_contrasena'] ?? null;

        $this->dbError = '';
        $this->temp_password = null;
    }

    /** Helper: ejecutar prepared statement con binding dinámico (todos como string) */
    private static function prepareAndExecute(mysqli $bd, string $sql, array $params)
    {
        $stmt = $bd->prepare($sql);
        if (!$stmt) {
            return [false, null, $bd->error];
        }
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // coerción a string (NULL sigue siendo NULL)
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            return [false, null, $err];
        }
        return [true, $stmt, null];
    }

    /** Normaliza entrada a JSON válido o NULL para columnas JSON */
    private static function toJsonOrNull($value): ?string
    {
        if ($value === null)
            return null;
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        $s = trim((string) $value);
        if ($s === '' || $s === 'null')
            return null;
        json_decode($s, true);
        if (json_last_error() === JSON_ERROR_NONE)
            return $s;
        return json_encode($s, JSON_UNESCAPED_UNICODE);
    }

    /** Crea la tabla si no existe */
    public static function ensureTable()
    {
        $bd = new Conexion();
        $sql = "
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_formulario INT NULL,
            id_cita INT NULL,
            nombre VARCHAR(150) NULL,
            apellido_paterno VARCHAR(150) NULL,
            apellido_materno VARCHAR(150) NULL,
            num_telefono VARCHAR(30) NULL,
            correo VARCHAR(255) NULL,
            contrasena VARCHAR(255) NULL,
            dependencia_laboraba VARCHAR(255) NULL,
            comentarios TEXT NULL,
            fecha_registro DATETIME NULL DEFAULT CURRENT_TIMESTAMP,

            fecha_nacimiento DATE NULL,
            fecha_fallecimiento DATE NULL,
            CURP VARCHAR(18) NULL,
            RFC VARCHAR(13) NULL,
            direccion_calle VARCHAR(255) NULL,
            direccion_num_casa VARCHAR(50) NULL,
            direccion_colonia VARCHAR(255) NULL,
            direccion_ciudad VARCHAR(255) NULL,
            direccion_estado VARCHAR(255) NULL,
            direccion_codigo_postal VARCHAR(10) NULL,
            nacionalidad VARCHAR(100) NULL,
            telefono_alternativo VARCHAR(30) NULL,

            Num_expediente VARCHAR(50) NULL,
            tipo_caso VARCHAR(100) NULL,
            folio_dictamen VARCHAR(100) NULL,
            fecha_dictamen DATE NULL,
            fecha_riesgo DATE NULL,
            num_caso_interno VARCHAR(100) NULL,

            foto_perfil VARCHAR(500) NULL,
            expediente VARCHAR(100) NULL,

            estado_cuenta VARCHAR(500) NULL,
            talon_pago VARCHAR(500) NULL,
            INE VARCHAR(500) NULL,
            doc_pdf_curp VARCHAR(500) NULL,
            doc_pdf_rfc VARCHAR(500) NULL,
            comp_domicilio VARCHAR(500) NULL,
            dictamen_rt09 VARCHAR(500) NULL,
            dictamen_riesgo VARCHAR(500) NULL,
            acta_matrimonio VARCHAR(500) NULL,
            acta_nacimiento VARCHAR(500) NULL,

            estado_validacion ENUM('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
            administrador_valido_archivos INT NULL,

            idioma_preferido VARCHAR(10) DEFAULT 'es',
            zona_horaria VARCHAR(64) DEFAULT 'America/Mexico_City',
            preferencias_notificacion JSON NULL,
            redes_sociales JSON NULL,
            metodo_pago VARCHAR(50) NULL,
            pago_referente_al_concepto_de VARCHAR(255) NULL,
            ultima_actualizacion_datos DATETIME NULL,
            ultima_actualizacion_archivos DATETIME NULL,
            ultima_conexion DATETIME NULL,
            dispositivos_conectados JSON NULL,
            token_validacion VARCHAR(255) NULL,
            token_cambio_contrasena VARCHAR(255) NULL,
            ultimo_cambio_contrasena DATETIME NULL,

            UNIQUE KEY uk_usuarios_correo (correo),
            UNIQUE KEY uk_usuarios_tel (num_telefono),
            KEY idx_usuarios_curp (CURP),
            KEY idx_usuarios_rfc (RFC),
            KEY idx_usuarios_idcita (id_cita),
            KEY idx_usuarios_idform (id_formulario)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        return $bd->query($sql);
    }

    /** Genera una contraseña temporal y la guarda hasheada; devuelve la clara vía getter */
    // En class/class.usuario.php
    protected function ensurePassword()
    {
        // Si ya viene hasheada (bcrypt), no tocar
        if ($this->contrasena && preg_match('/^\$2y\$/', (string) $this->contrasena)) {
            return;
        }

        if (!$this->contrasena) {
            // No vino => generar temporal aleatoria (comportamiento existente)
            $raw = rtrim(strtr(base64_encode(random_bytes(12)), '+/', '-_'), '=');
            $this->temp_password = $raw;
            $this->contrasena = $raw;
        } else {
            // VINO EN CLARO => guárdala para mostrarla y luego hashearla
            $this->temp_password = (string) $this->contrasena;
        }

        // Hashear lo que haya en claro
        if ($this->contrasena && !preg_match('/^\$2y\$/', (string) $this->contrasena)) {
            $this->contrasena = password_hash((string) $this->temp_password, self::PASSWORD_ALGO, self::PASSWORD_OPTS);
        }
    }

    /** Valida unicidad de email y teléfono */
    protected function isUniqueEmailAndPhone(): bool
    {
        $existente = self::findByEmailOrPhone($this->correo, $this->num_telefono);
        if ($existente) {
            $this->dbError = 'El correo o el teléfono ya están registrados.';
            return false;
        }
        return true;
    }

    /** Genera/Asigna número de expediente interno si no existe */
    public function ensureNumExpediente($prefijo = 'EXP')
    {
        if (!$this->Num_expediente) {
            $this->Num_expediente = sprintf('%s-%s-%04d', $prefijo, date('Ymd'), random_int(1, 9999));
        }
        return $this->Num_expediente;
    }

    /** Inserta el usuario (crea contraseña temporal si no viene) */
    public function create(): bool
    {
        $this->dbError = '';
        self::ensureTable();

        if (!$this->isUniqueEmailAndPhone()) {
            return false;
        }

        // generar expediente si falta & hashear contraseña
        $this->ensureNumExpediente();
        $this->ensurePassword();

        $bd = new Conexion();

        // JSON encode si llegan arrays
        $prefNotif = self::toJsonOrNull($this->preferencias_notificacion);
        $redes = self::toJsonOrNull($this->redes_sociales);
        $disp = self::toJsonOrNull($this->dispositivos_conectados);

        $sql = "INSERT INTO usuarios (
            id_formulario, id_cita, nombre, apellido_paterno, apellido_materno, num_telefono, correo, contrasena,
            dependencia_laboraba, comentarios, fecha_registro,
            fecha_nacimiento, fecha_fallecimiento, CURP, RFC,
            direccion_calle, direccion_num_casa, direccion_colonia, direccion_ciudad, direccion_estado, direccion_codigo_postal,
            nacionalidad, telefono_alternativo,
            Num_expediente, tipo_caso, folio_dictamen, fecha_dictamen, fecha_riesgo, num_caso_interno,
            foto_perfil, expediente,
            estado_cuenta, talon_pago, INE, doc_pdf_curp, doc_pdf_rfc, comp_domicilio, dictamen_rt09, dictamen_riesgo, acta_matrimonio, acta_nacimiento,
            estado_validacion, administrador_valido_archivos,
            idioma_preferido, zona_horaria, preferencias_notificacion, redes_sociales, metodo_pago, pago_referente_al_concepto_de,
            ultima_actualizacion_datos, ultima_actualizacion_archivos, ultima_conexion, dispositivos_conectados,
            token_validacion, token_cambio_contrasena, ultimo_cambio_contrasena
        ) VALUES (
            ?,?,?,?,?,?,?,?,
            ?,?, NOW(),
            ?,?,?,?,
            ?,?,?,?,?,?,
            ?,?,
            ?,?,?,?,?,?,
            ?,?,
            ?,?,?,?,?,?,?,?,?,?,
            ?,?,
            ?,?,?,?, ?,?,
            NOW(), NOW(), NULL, ?,
            ?,?,?
        )";

        $params = [
            $this->id_formulario,
            $this->id_cita,
            $this->nombre,
            $this->apellido_paterno,
            $this->apellido_materno,
            $this->num_telefono,
            $this->correo,
            $this->contrasena,

            $this->dependencia_laboraba,
            $this->comentarios,

            $this->fecha_nacimiento,
            $this->fecha_fallecimiento,
            $this->CURP,
            $this->RFC,

            $this->direccion_calle,
            $this->direccion_num_casa,
            $this->direccion_colonia,
            $this->direccion_ciudad,
            $this->direccion_estado,
            $this->direccion_codigo_postal,

            $this->nacionalidad,
            $this->telefono_alternativo,

            $this->Num_expediente,
            $this->tipo_caso,
            $this->folio_dictamen,
            $this->fecha_dictamen,
            $this->fecha_riesgo,
            $this->num_caso_interno,

            $this->foto_perfil,
            $this->expediente,

            $this->estado_cuenta,
            $this->talon_pago,
            $this->INE,
            $this->doc_pdf_curp,
            $this->doc_pdf_rfc,
            $this->comp_domicilio,
            $this->dictamen_rt09,
            $this->dictamen_riesgo,
            $this->acta_matrimonio,
            $this->acta_nacimiento,

            $this->estado_validacion,
            $this->administrador_valido_archivos,

            $this->idioma_preferido,
            $this->zona_horaria,
            $prefNotif,
            $redes,
            $this->metodo_pago,
            $this->pago_referente_al_concepto_de,

            $disp,

            $this->token_validacion,
            $this->token_cambio_contrasena,
            $this->ultimo_cambio_contrasena
        ];

        [$ok, $stmt, $err] = self::prepareAndExecute($bd, $sql, $params);
        if (!$ok) {
            $this->dbError = $err;
            return false;
        }

        $this->id = $stmt->insert_id;
        $stmt->close();
        return true;
    }

    /** Actualiza por ID (requiere $this->id) */
    public function update(): bool
    {
        $this->dbError = '';
        if (!$this->id) {
            $this->dbError = 'ID requerido para actualizar.';
            return false;
        }

        // Hashear si viene en claro
        if ($this->contrasena && !preg_match('/^\$2y\$/', (string) $this->contrasena)) {
            $this->contrasena = password_hash((string) $this->contrasena, self::PASSWORD_ALGO, self::PASSWORD_OPTS);
        }

        $bd = new Conexion();

        $prefNotif = self::toJsonOrNull($this->preferencias_notificacion);
        $redes = self::toJsonOrNull($this->redes_sociales);
        $disp = self::toJsonOrNull($this->dispositivos_conectados);

        $sql = "UPDATE usuarios SET
            id_formulario = ?, id_cita = ?, nombre = ?, apellido_paterno = ?, apellido_materno = ?, num_telefono = ?, correo = ?, contrasena = ?,
            dependencia_laboraba = ?, comentarios = ?,
            fecha_nacimiento = ?, fecha_fallecimiento = ?, CURP = ?, RFC = ?,
            direccion_calle = ?, direccion_num_casa = ?, direccion_colonia = ?, direccion_ciudad = ?, direccion_estado = ?, direccion_codigo_postal = ?,
            nacionalidad = ?, telefono_alternativo = ?,
            Num_expediente = ?, tipo_caso = ?, folio_dictamen = ?, fecha_dictamen = ?, fecha_riesgo = ?, num_caso_interno = ?,
            foto_perfil = ?, expediente = ?,
            estado_cuenta = ?, talon_pago = ?, INE = ?, doc_pdf_curp = ?, doc_pdf_rfc = ?, comp_domicilio = ?, dictamen_rt09 = ?, dictamen_riesgo = ?, acta_matrimonio = ?, acta_nacimiento = ?,
            estado_validacion = ?, administrador_valido_archivos = ?,
            idioma_preferido = ?, zona_horaria = ?, preferencias_notificacion = ?, redes_sociales = ?, metodo_pago = ?, pago_referente_al_concepto_de = ?,
            ultima_actualizacion_datos = NOW(), ultima_actualizacion_archivos = NOW(), ultima_conexion = ?, dispositivos_conectados = ?,
            token_validacion = ?, token_cambio_contrasena = ?, ultimo_cambio_contrasena = ?
            WHERE id = ?";

        $params = [
            $this->id_formulario,
            $this->id_cita,
            $this->nombre,
            $this->apellido_paterno,
            $this->apellido_materno,
            $this->num_telefono,
            $this->correo,
            $this->contrasena,

            $this->dependencia_laboraba,
            $this->comentarios,

            $this->fecha_nacimiento,
            $this->fecha_fallecimiento,
            $this->CURP,
            $this->RFC,

            $this->direccion_calle,
            $this->direccion_num_casa,
            $this->direccion_colonia,
            $this->direccion_ciudad,
            $this->direccion_estado,
            $this->direccion_codigo_postal,

            $this->nacionalidad,
            $this->telefono_alternativo,

            $this->Num_expediente,
            $this->tipo_caso,
            $this->folio_dictamen,
            $this->fecha_dictamen,
            $this->fecha_riesgo,
            $this->num_caso_interno,

            $this->foto_perfil,
            $this->expediente,

            $this->estado_cuenta,
            $this->talon_pago,
            $this->INE,
            $this->doc_pdf_curp,
            $this->doc_pdf_rfc,
            $this->comp_domicilio,
            $this->dictamen_rt09,
            $this->dictamen_riesgo,
            $this->acta_matrimonio,
            $this->acta_nacimiento,

            $this->estado_validacion,
            $this->administrador_valido_archivos,

            $this->idioma_preferido,
            $this->zona_horaria,
            $prefNotif,
            $redes,
            $this->metodo_pago,
            $this->pago_referente_al_concepto_de,

            $this->ultima_conexion,
            $disp,

            $this->token_validacion,
            $this->token_cambio_contrasena,
            $this->ultimo_cambio_contrasena,

            $this->id // <- IMPORTANTE: id para el WHERE
        ];

        [$ok, , $err] = self::prepareAndExecute($bd, $sql, $params);
        if (!$ok) {
            $this->dbError = $err;
            return false;
        }
        return true;
    }

    private static function asNullIfEmpty($v)
    {
        if ($v === '')
            return null;
        return $v;
    }

    /** Borra por ID */
    public static function delete($id)
    {
        $bd = new Conexion();
        $sql = "DELETE FROM usuarios WHERE id = ?";
        [$ok, ,] = self::prepareAndExecute($bd, $sql, [$id]);
        return $ok ?: false;
    }

    /** Lee uno por ID */
    public static function readById($id)
    {
        $bd = new Conexion();
        $sql = "SELECT * FROM usuarios WHERE id = ? LIMIT 1";
        [$ok, $stmt,] = self::prepareAndExecute($bd, $sql, [$id]);
        if (!$ok)
            return false;
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ?: false;
    }

    /** Lee todos (últimos primero) */
    public static function readAll($limit = 100, $offset = 0)
    {
        $bd = new Conexion();
        $sql = "SELECT * FROM usuarios ORDER BY id DESC LIMIT ? OFFSET ?";
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
        $stmt->close();
        return $rows;
    }

    /** Buscar por correo o teléfono (útil para “no duplicar”) */
    public static function findByEmailOrPhone($correo, $num_telefono)
    {
        $bd = new Conexion();
        $sql = "SELECT * FROM usuarios WHERE correo = ? OR num_telefono = ? LIMIT 1";
        [$ok, $stmt,] = self::prepareAndExecute($bd, $sql, [$correo, $num_telefono]);
        if (!$ok)
            return false;
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ?: false;
    }

    /** Autenticación segura con password_verify; rehash automático si el cost quedó viejo */
    public static function autenticar($correo, $contrasena)
    {
        $bd = new Conexion();
        $sql = "SELECT * FROM usuarios WHERE correo = ? LIMIT 1";
        [$ok, $stmt,] = self::prepareAndExecute($bd, $sql, [$correo]);
        if (!$ok)
            return false;

        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();
        if (!$user)
            return false;

        $hash = $user['contrasena'] ?? '';
        if (!$hash || !password_verify((string) $contrasena, (string) $hash)) {
            return false;
        }

        if (password_needs_rehash($hash, self::PASSWORD_ALGO, self::PASSWORD_OPTS)) {
            $nuevoHash = password_hash((string) $contrasena, self::PASSWORD_ALGO, self::PASSWORD_OPTS);
            $upd = "UPDATE usuarios SET contrasena = ?, ultimo_cambio_contrasena = NOW() WHERE id = ?";
            self::prepareAndExecute($bd, $upd, [$nuevoHash, $user['id']]);
            $user['contrasena'] = $nuevoHash;
        }

        return $user;
    }

    /* =================== Getters útiles =================== */
    public function getDbError()
    {
        return $this->dbError;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getCorreo()
    {
        return $this->correo;
    }
    public function getTelefono()
    {
        return $this->num_telefono;
    }
    public function getNumExpediente()
    {
        return $this->Num_expediente;
    }
    public function getTempPassword()
    {
        return $this->temp_password;
    } // mostrar post-registro
}
