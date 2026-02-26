<?php
// CLASE DEDICADA A LA ACTUALIZACION DE LOS DATOS DEL INDEX
class datosindex
{
    protected $id;
    protected $nombre;
    protected $años_experiencia;
    protected $experiencias_clientes;
    protected $enlace_red_social_fb;
    protected $enlace_red_social_linkedin;
    protected $enlace_red_social_twitter;
    protected $enlace_red_social_instagram;
    protected $enlace_talon_pago;
    protected $enlace_tripticos;
    protected $enlace_calendario_anual;
    protected $enlace_blog_pensiones;
    protected $contacto_numero;
    protected $contacto_correo;
    protected $contacto_horas_atencion;
    protected $contacto_dias_atencion;
    protected $foto_logo_icono;
    protected $foto_superior_logo;
    protected $foto_banner_inferior;
    protected $dbError;

    // AQUI SE CONSTRULLEN TODAS PREGUNTAS, DEPENDIENDO EL FLUJO ALGUNAS QUEDARAN VACIAS
    public function __construct($id, $nombre, $años_experiencia, $experiencias_clientes, $enlace_red_social_fb, $enlace_red_social_linkedin, $enlace_red_social_twitter, $enlace_red_social_instagram, $enlace_talon_pago, $enlace_tripticos, $enlace_calendario_anual, $enlace_blog_pensiones, $contacto_numero, $contacto_correo, $contacto_horas_atencion, $contacto_dias_atencion, $foto_logo_icono, $foto_superior_logo, $foto_banner_inferior)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->años_experiencia = $años_experiencia;
        $this->experiencias_clientes = $experiencias_clientes;
        $this->enlace_red_social_fb = $enlace_red_social_fb;
        $this->enlace_red_social_linkedin = $enlace_red_social_linkedin;
        $this->enlace_red_social_twitter = $enlace_red_social_twitter;
        $this->enlace_red_social_instagram = $enlace_red_social_instagram;
        $this->enlace_talon_pago = $enlace_talon_pago;
        $this->enlace_tripticos = $enlace_tripticos;
        $this->enlace_calendario_anual = $enlace_calendario_anual;
        $this->enlace_blog_pensiones = $enlace_blog_pensiones;
        $this->contacto_numero = $contacto_numero;
        $this->contacto_correo = $contacto_correo;
        $this->contacto_horas_atencion = $contacto_horas_atencion;
        $this->contacto_dias_atencion = $contacto_dias_atencion;
        $this->foto_logo_icono = $foto_logo_icono;
        $this->foto_superior_logo = $foto_superior_logo;
        $this->foto_banner_inferior = $foto_banner_inferior;
        $this->dbError = '';
    }

    // FUNCION PARA CREAR Y INSERTAR DATOS EN LA TABLA FORMULARIO, EN CASO DE QUE NO EXISTA LA TABLA SE CREA SOLA
    public function create()
    {
        $bd = new conexion();
        $ct = "SHOW TABLES LIKE 'datosindex'";
        $h = $bd->query($ct);

        $sql = "INSERT INTO datosindex (ID, NOMBRE, AÑOS_EXPERIENCIA, EXPERIENCIAS_CLIENTES, ENLACE_RED_SOCIAL_FB, ENLACE_RED_SOCIAL_LINKEDIN, ENLACE_RED_SOCIAL_TWITTER, ENLACE_RED_SOCIAL_INSTAGRAM, ENLACE_TALON_PAGO, ENLACE_TRIPTICOS,  ENLACE_CALENDARIO_ANUAL, ENLACE_BLOG_PENSIONES, CONTACTO_NUMERO, CONTACTO_CORREO, CONTACTO_HORAS_ATENCION, CONTACTO_DIAS_ATENCION, FOTO_LOGO_ICONO, FOTO_SUPERIOR_LOGO, FOTO_BANNER_INFERIOR) 
                VALUES ('{$this->id}','{$this->nombre}','{$this->años_experiencia}','{$this->experiencias_clientes}','{$this->enlace_red_social_fb}','{$this->enlace_red_social_linkedin}','{$this->enlace_red_social_twitter}','{$this->enlace_red_social_instagram}','{$this->enlace_talon_pago}','{$this->enlace_tripticos}','{$this->enlace_calendario_anual}','{$this->enlace_blog_pensiones}','{$this->contacto_numero}','{$this->contacto_correo}','{$this->contacto_horas_atencion}','{$this->contacto_dias_atencion}','{$this->foto_logo_icono}','{$this->foto_superior_logo}','{$this->foto_banner_inferior}')";

        if ($h->num_rows < 1) {
            // Si la tabla 'admins' no existe, crea la tabla y luego realiza la inserción
            $sqlCreateTable = "IF NOT EXISTS CREATE TABLE datosindex (
                                ID INT AUTO_INCREMENT PRIMARY KEY,
                                NOMBRE VARCHAR(255),
                                AÑOS_EXPERIENCIA VARCHAR(255),
                                EXPERIENCIAS_CLIENTES VARCHAR(255),
                                ENLACE_RED_SOCIAL_FB VARCHAR(255),
                                ENLACE_RED_SOCIAL_LINKEDIN VARCHAR(255),
                                ENLACE_RED_SOCIAL_TWITTER VARCHAR(255),
                                ENLACE_RED_SOCIAL_INSTAGRAM VARCHAR(255),
                                ENLACE_TALON_PAGO VARCHAR(255),
                                ENLACE_TRIPTICOS VARCHAR(255),
                                ENLACE_CALENDARIO_ANUAL VARCHAR(255),
                                ENLACE_BLOG_PENSIONES VARCHAR(255),
                                CONTACTO_NUMERO VARCHAR(255),
                                CONTACTO_CORREO VARCHAR(255),
                                CONTACTO_HORAS_ATENCION VARCHAR(255),
                                CONTACTO_DIAS_ATENCION VARCHAR(255),
                                FOTO_LOGO_ICONO VARCHAR(255),
                                FOTO_SUPERIOR_LOGO VARCHAR(255),
                                FOTO_BANNER_INFERIOR VARCHAR(255)
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
            $sql = "SELECT * FROM datosindex WHERE ID = {$id}";
        } else {
            // Si no se proporciona un ID, obtén todos los registros ordenados por ID de mayor a menor
            $sql = "SELECT * FROM datosindex ORDER BY ID DESC";
        }

        $result = $bd->query($sql);

        // Manejo del resultado (podrías devolverlo, imprimirlo, etc.)
        return $result;
    }
}
