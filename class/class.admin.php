<?php

class Admin
{
    // Identidad base
    protected $id;
    protected $nombre;
    protected $apellido_paterno;
    protected $apellido_materno;
    protected $email;
    protected $telefono;
    protected $username;
    protected $password;                   // se guarda hash
    protected $fecha_creacion;

    // Medios secundarios de comunicación
    protected $email_secundario;
    protected $num_tel_secundario;

    // Complementos del perfil
    protected $descripcion;
    protected $especialidad;
    protected $estudios;
    protected $ciudad_ejerce;
    protected $direccion;                  // <— añadido
    protected $departamento;               // <— añadido
    protected $puesto;                     // <— añadido

    // Archivos adjuntos
    protected $foto_perfil;
    protected $foto_portada;

    // Configuraciones del perfil
    protected $idioma_preferido;
    protected $tema_visual;
    protected $preferencia_notificaciones; // JSON (texto)

    // Seguridad y estado
    protected $fecha_ultima_notificacion;
    protected $medio_en_el_que_fue_notificado;
    protected $registrado_el;
    protected $ultima_conexion;
    protected $estado;                     // activo | inactivo | suspendido (sugerido)
    protected $rol;                        // superadmin | admin | editor ...
    protected $token_recuperacion;
    protected $token_expira;               // expiración de token recuperación
    protected $verif_dos_pasos;            // <— añadido (0/1)
    protected $fecha_ultimo_cambio_contra; // <— añadido (DATETIME)
    protected $dispositivo_ultimo_login;   // <— añadido (UA/resumen)
    protected $dbError;

    // Config de hashing seguro
    private const PASSWORD_ALGO = PASSWORD_BCRYPT;
    private const PASSWORD_OPTS = ['cost' => 11];

    public function __construct(array $data = [])
    {
        // Base
        $this->id = $data['id'] ?? null;
        $this->nombre = $data['nombre'] ?? null;
        $this->apellido_paterno = $data['apellido_paterno'] ?? null;
        $this->apellido_materno = $data['apellido_materno'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->telefono = $data['telefono'] ?? null;
        $this->username = $data['username'] ?? null;
        $this->password = $data['password'] ?? null; // en plano; se hashea en create/update
        $this->fecha_creacion = $data['fecha_creacion'] ?? null;

        // Medios secundarios
        $this->email_secundario = $data['email_secundario'] ?? null;
        $this->num_tel_secundario = $data['num_tel_secundario'] ?? null;

        // Perfil
        $this->descripcion = $data['descripcion'] ?? null;
        $this->especialidad = $data['especialidad'] ?? null;
        $this->estudios = $data['estudios'] ?? null;
        $this->ciudad_ejerce = $data['ciudad_ejerce'] ?? null;
        $this->direccion = $data['direccion'] ?? null;
        $this->departamento = $data['departamento'] ?? null;
        $this->puesto = $data['puesto'] ?? null;

        // Archivos
        $this->foto_perfil = $data['foto_perfil'] ?? null;
        $this->foto_portada = $data['foto_portada'] ?? null;

        // Config
        $this->idioma_preferido = $data['idioma_preferido'] ?? 'es';
        $this->tema_visual = $data['tema_visual'] ?? 'claro';
        $this->preferencia_notificaciones = $data['preferencia_notificaciones'] ?? null;

        // Seguridad/estado
        $this->fecha_ultima_notificacion = $data['fecha_ultima_notificacion'] ?? null;
        $this->medio_en_el_que_fue_notificado = $data['medio_en_el_que_fue_notificado'] ?? null;
        $this->registrado_el = $data['registrado_el'] ?? null;
        $this->ultima_conexion = $data['ultima_conexion'] ?? null;
        $this->estado = $data['estado'] ?? 'activo';
        $this->rol = $data['rol'] ?? 'admin';
        $this->token_recuperacion = $data['token_recuperacion'] ?? null;
        $this->token_expira = $data['token_expira'] ?? null;
        $this->verif_dos_pasos = isset($data['verif_dos_pasos']) ? (int) $data['verif_dos_pasos'] : 0;
        $this->fecha_ultimo_cambio_contra = $data['fecha_ultimo_cambio_contra'] ?? null;
        $this->dispositivo_ultimo_login = $data['dispositivo_ultimo_login'] ?? null;

        $this->dbError = '';
    }

    /* =================== Utilidad interna =================== */

    private static function prepareAndExecute($bd, string $sql, array $params)
    {
        $stmt = $bd->prepare($sql);
        if (!$stmt)
            return [false, null, $bd->error];
        if ($params) {
            $types = str_repeat('s', count($params)); // casteo a string
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute())
            return [false, null, $stmt->error];
        return [true, $stmt, null];
    }

    public static function ensureTable()
    {
        $bd = new conexion();
        $sql = "
        CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(150) NULL,
            apellido_paterno VARCHAR(150) NULL,
            apellido_materno VARCHAR(150) NULL,
            email VARCHAR(255) NULL,
            telefono VARCHAR(50) NULL,
            username VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            email_secundario VARCHAR(255) NULL,
            num_tel_secundario VARCHAR(50) NULL,

            descripcion TEXT NULL,
            especialidad VARCHAR(255) NULL,
            estudios VARCHAR(255) NULL,
            ciudad_ejerce VARCHAR(255) NULL,
            direccion VARCHAR(500) NULL,
            departamento VARCHAR(255) NULL,
            puesto VARCHAR(255) NULL,

            foto_perfil VARCHAR(500) NULL,
            foto_portada VARCHAR(500) NULL,

            idioma_preferido VARCHAR(10) DEFAULT 'es',
            tema_visual VARCHAR(20) DEFAULT 'claro',
            preferencia_notificaciones JSON NULL,

            fecha_ultima_notificacion DATETIME NULL,
            medio_en_el_que_fue_notificado VARCHAR(100) NULL,
            registrado_el DATETIME NULL,
            ultima_conexion DATETIME NULL,
            estado ENUM('activo','inactivo','suspendido') DEFAULT 'activo',
            rol VARCHAR(30) NOT NULL DEFAULT 'admin',
            token_recuperacion VARCHAR(255) NULL,
            token_expira DATETIME NULL,
            verif_dos_pasos TINYINT(1) NOT NULL DEFAULT 0,
            fecha_ultimo_cambio_contra DATETIME NULL,
            dispositivo_ultimo_login VARCHAR(255) NULL,

            UNIQUE KEY uk_admins_username (username),
            UNIQUE KEY uk_admins_email (email),
            KEY idx_admins_estado (estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        return $bd->query($sql);
    }

    /* =================== CRUD =================== */

    public function create()
    {
        $this->dbError = '';
        self::ensureTable();

        if (!$this->username || !$this->password) {
            $this->dbError = 'USERNAME y PASSWORD son obligatorios.';
            return false;
        }

        // Hash seguro si llega en texto plano
        if ($this->password && !preg_match('/^\$2y\$/', (string) $this->password)) {
            $this->password = password_hash((string) $this->password, self::PASSWORD_ALGO, self::PASSWORD_OPTS);
            $this->fecha_ultimo_cambio_contra = date('Y-m-d H:i:s');
        }

        // Unicidad básica
        if (self::existsByUsername($this->username)) {
            $this->dbError = 'El USERNAME ya existe.';
            return false;
        }
        if ($this->email && self::existsByEmail($this->email)) {
            $this->dbError = 'El EMAIL ya está registrado.';
            return false;
        }

        $bd = new conexion();
        $sql = "INSERT INTO admins (
            nombre, apellido_paterno, apellido_materno, email, telefono, username, password,
            email_secundario, num_tel_secundario,
            descripcion, especialidad, estudios, ciudad_ejerce, direccion, departamento, puesto,
            foto_perfil, foto_portada,
            idioma_preferido, tema_visual, preferencia_notificaciones,
            fecha_ultima_notificacion, medio_en_el_que_fue_notificado, registrado_el, ultima_conexion,
            estado, rol, token_recuperacion, token_expira, verif_dos_pasos, fecha_ultimo_cambio_contra, dispositivo_ultimo_login
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $params = [
            $this->nombre,
            $this->apellido_paterno,
            $this->apellido_materno,
            $this->email,
            $this->telefono,
            $this->username,
            $this->password,
            $this->email_secundario,
            $this->num_tel_secundario,
            $this->descripcion,
            $this->especialidad,
            $this->estudios,
            $this->ciudad_ejerce,
            $this->direccion,
            $this->departamento,
            $this->puesto,
            $this->foto_perfil,
            $this->foto_portada,
            $this->idioma_preferido,
            $this->tema_visual,
            $this->preferencia_notificaciones,
            $this->fecha_ultima_notificacion,
            $this->medio_en_el_que_fue_notificado,
            $this->registrado_el,
            $this->ultima_conexion,
            $this->estado,
            $this->rol,
            $this->token_recuperacion,
            $this->token_expira,
            (string) $this->verif_dos_pasos,
            $this->fecha_ultimo_cambio_contra,
            $this->dispositivo_ultimo_login
        ];

        [$ok, $stmt, $err] = self::prepareAndExecute($bd, $sql, $params);
        if (!$ok) {
            $this->dbError = $err;
            return false;
        }
        $this->id = $stmt->insert_id;
        return true;
    }


    public function update()
    {
        if (!$this->id) {
            $this->dbError = 'ID requerido.';
            return false;
        }

        // Si viene password en plano, re-hashear
        if ($this->password && !preg_match('/^\$2y\$/', (string) $this->password)) {
            $this->password = password_hash((string) $this->password, self::PASSWORD_ALGO, self::PASSWORD_OPTS);
            $this->fecha_ultimo_cambio_contra = date('Y-m-d H:i:s');
        }

        // Unicidad si cambian username/email
        if ($this->username && self::existsByUsername($this->username, $this->id)) {
            $this->dbError = 'El USERNAME ya existe.';
            return false;
        }
        if ($this->email && self::existsByEmail($this->email, $this->id)) {
            $this->dbError = 'El EMAIL ya está registrado.';
            return false;
        }

        $bd = new conexion();
        $sql = "UPDATE admins SET
            nombre=?, apellido_paterno=?, apellido_materno=?, email=?, telefono=?, username=?, password=?,
            email_secundario=?, num_tel_secundario=?,
            descripcion=?, especialidad=?, estudios=?, ciudad_ejerce=?, direccion=?, departamento=?, puesto=?,
            foto_perfil=?, foto_portada=?,
            idioma_preferido=?, tema_visual=?, preferencia_notificaciones=?,
            fecha_ultima_notificacion=?, medio_en_el_que_fue_notificado=?, registrado_el=?, ultima_conexion=?,
            estado=?, rol=?, token_recuperacion=?, token_expira=?, verif_dos_pasos=?, fecha_ultimo_cambio_contra=?, dispositivo_ultimo_login=?
            WHERE id = ?";

        $params = [
            $this->nombre,
            $this->apellido_paterno,
            $this->apellido_materno,
            $this->email,
            $this->telefono,
            $this->username,
            $this->password,
            $this->email_secundario,
            $this->num_tel_secundario,
            $this->descripcion,
            $this->especialidad,
            $this->estudios,
            $this->ciudad_ejerce,
            $this->direccion,
            $this->departamento,
            $this->puesto,
            $this->foto_perfil,
            $this->foto_portada,
            $this->idioma_preferido,
            $this->tema_visual,
            $this->preferencia_notificaciones,
            $this->fecha_ultima_notificacion,
            $this->medio_en_el_que_fue_notificado,
            $this->registrado_el,
            $this->ultima_conexion,
            $this->estado,
            $this->rol,
            $this->token_recuperacion,
            $this->token_expira,
            (string) $this->verif_dos_pasos,
            $this->fecha_ultimo_cambio_contra,
            $this->dispositivo_ultimo_login,
            $this->id
        ];

        [$ok, , $err] = self::prepareAndExecute($bd, $sql, $params);
        if (!$ok) {
            $this->dbError = $err;
            return false;
        }
        return true;
    }

    public static function read($id = null)
    {
        $bd = new conexion();
        if ($id !== null) {
            $sql = "SELECT * FROM admins WHERE id = ? LIMIT 1";
            [$ok, $stmt,] = self::prepareAndExecute($bd, $sql, [$id]);
            if (!$ok)
                return false;
            return $stmt->get_result()->fetch_assoc();
        } else {
            $sql = "SELECT * FROM admins ORDER BY id DESC";
            $res = $bd->query($sql);
            if (!$res)
                return false;
            $rows = [];
            while ($r = $res->fetch_assoc()) {
                $rows[] = $r;
            }
            return $rows;
        }
    }

    public static function readAll($limit = 100, $offset = 0)
    {
        $bd = new conexion();
        $sql = "SELECT * FROM admins ORDER BY id DESC LIMIT ? OFFSET ?";
        [$ok, $stmt,] = self::prepareAndExecute($bd, $sql, [$limit, $offset]);
        if (!$ok)
            return false;
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        return $rows;
    }

    public static function existsByUsername($username, $excludeId = null)
    {
        $bd = new conexion();
        $sql = "SELECT id FROM admins WHERE username = ?";
        $params = [$username];
        if ($excludeId) {
            $sql .= " AND id <> ?";
            $params[] = $excludeId;
        }
        [$ok, $stmt,] = self::prepareAndExecute($bd, $sql, $params);
        if (!$ok)
            return false;
        return (bool) $stmt->get_result()->fetch_assoc();
    }

    public static function existsByEmail($email, $excludeId = null)
    {
        $bd = new conexion();
        $sql = "SELECT id FROM admins WHERE email = ?";
        $params = [$email];
        if ($excludeId) {
            $sql .= " AND id <> ?";
            $params[] = $excludeId;
        }
        [$ok, $stmt,] = self::prepareAndExecute($bd, $sql, $params);
        if (!$ok)
            return false;
        return (bool) $stmt->get_result()->fetch_assoc();
    }

    /* =================== Login y recuperación =================== */

    /**
     * Login seguro.
     * @return int 1=ok, 0=credenciales inválidas
     */
    public static function login($username, $password, $deviceInfo = null)
    {
        $bd = new conexion();
        $sql = "SELECT * FROM admins WHERE username = ? LIMIT 1";
        [$ok, $stmt,] = self::prepareAndExecute($bd, $sql, [$username]);
        if (!$ok)
            return 0;

        $row = $stmt->get_result()->fetch_assoc();
        if (!$row)
            return 0;

        if (!password_verify($password, $row['password'])) {
            return 0;
        }

        // Rehash si ya no cumple cost/algoritmo
        if (password_needs_rehash($row['password'], self::PASSWORD_ALGO, self::PASSWORD_OPTS)) {
            $nuevo = password_hash($password, self::PASSWORD_ALGO, self::PASSWORD_OPTS);
            $upd = "UPDATE admins SET password=?, fecha_ultimo_cambio_contra=NOW() WHERE id=?";
            self::prepareAndExecute($bd, $upd, [$nuevo, $row['id']]);
            $row['password'] = $nuevo;
        }

        // Actualizar última conexión y dispositivo
        $upd2 = "UPDATE admins SET ultima_conexion = NOW(), dispositivo_ultimo_login = ? WHERE id = ?";
        self::prepareAndExecute($bd, $upd2, [$deviceInfo ?? '', $row['id']]);

        // Sesión
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        $_SESSION['ID'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['name'] = trim(($row['nombre'] ?? '') . ' ' . ($row['apellido_paterno'] ?? '')) ?: 'Administrador';
        $_SESSION['rol'] = $row['rol'] ?? 'admin';
        $_SESSION['admin'] = 'admin';
        $_SESSION['tipo'] = '2'; // SE DEFINE EL ROL ADMIN PARA EL INICIO DE SESION

        return 1;
    }

    /** Inicia recuperación de contraseña (genera token y expiración 1h) */
    public static function iniciarRecuperacion($usernameOrEmail)
    {
        $bd = new conexion();
        $sql = "SELECT id, email FROM admins WHERE (username = ? OR email = ?) LIMIT 1";
        [$ok, $stmt,] = self::prepareAndExecute($bd, $sql, [$usernameOrEmail, $usernameOrEmail]);
        if (!$ok)
            return false;

        $row = $stmt->get_result()->fetch_assoc();
        if (!$row)
            return false;

        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', time() + 3600);

        $upd = "UPDATE admins SET token_recuperacion=?, token_expira=? WHERE id=?";
        self::prepareAndExecute($bd, $upd, [$token, $expira, $row['id']]);

        // Aquí envías el token por correo fuera de la clase
        return ['id' => $row['id'], 'email' => $row['email'], 'token' => $token, 'expira' => $expira];
    }

    /** Completa el reseteo con el token y establece nueva contraseña */
    public static function resetearConToken($token, $nuevaPassword)
    {
        $bd = new conexion();
        $sql = "SELECT id, token_expira FROM admins WHERE token_recuperacion = ? LIMIT 1";
        [$ok, $stmt,] = self::prepareAndExecute($bd, $sql, [$token]);
        if (!$ok)
            return false;

        $row = $stmt->get_result()->fetch_assoc();
        if (!$row)
            return false;

        if (!$row['token_expira'] || strtotime($row['token_expira']) < time()) {
            return false; // token expirado
        }

        $hash = password_hash($nuevaPassword, self::PASSWORD_ALGO, self::PASSWORD_OPTS);
        $upd = "UPDATE admins SET password=?, token_recuperacion=NULL, token_expira=NULL, fecha_ultimo_cambio_contra=NOW() WHERE id=?";
        [$ok2, ,] = self::prepareAndExecute($bd, $upd, [$hash, $row['id']]);
        return $ok2 ?: false;
    }

    /* =================== Getters mínimos =================== */
    public function getDbError()
    {
        return $this->dbError;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getUsername()
    {
        return $this->username;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getRol()
    {
        return $this->rol;
    }
}
