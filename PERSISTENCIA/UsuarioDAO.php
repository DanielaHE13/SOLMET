<?php
/**
 * DAO de Usuario para Solmet
 * Esta clase SOLO construye SQL + params. La ejecución la hace Conexion.
 *
 * Orden de columnas devueltas por las consultas principales (para FETCH_NUM):
 * [0] id_usuario
 * [1] username
 * [2] nombre
 * [3] apellido
 * [4] foto
 * [5] password_hash
 * [6] id_rol
 * [7] activo
 */

class UsuarioDAO
{
    private ?string $id_usuario;
    private ?string $username;
    private ?string $password_hash;
    private ?int    $id_rol;
    private ?int    $activo;
    private ?string $nombre;
    private ?string $apellido;
    private ?string $foto;

    /**
     * @param ?string $id_usuario   Cédula (PK, no autoincremental)
     * @param ?string $username
     * @param ?string $password_hash Hash ya calculado (password_hash)
     * @param ?int    $id_rol
     * @param ?int    $activo        1/0
     * @param ?string $nombre
     * @param ?string $apellido
     * @param ?string $foto           ruta/archivo (nullable)
     */
    public function __construct(
        ?string $id_usuario = null,
        ?string $username = null,
        ?string $password_hash = null,
        ?int    $id_rol = null,
        ?int    $activo = null,
        ?string $nombre = null,
        ?string $apellido = null,
        ?string $foto = null
    ) {
        $this->id_usuario    = $id_usuario;
        $this->username      = $username;
        $this->password_hash = $password_hash;
        $this->id_rol        = $id_rol;
        $this->activo        = $activo;
        $this->nombre        = $nombre;
        $this->apellido      = $apellido;
        $this->foto          = $foto;
    }

    /**
     * Trae un usuario por username (LOGIN)
     * Devuelve SQL + params. Mantiene el ORDEN de columnas descrito arriba.
     */
    public function consultarPorUsername(): array
    {
        $sql = "SELECT
                    id_usuario,
                    username,
                    nombre,
                    apellido,
                    foto,
                    password_hash,
                    id_rol,
                    activo
                FROM usuario
                WHERE username = ?
                LIMIT 1";
        return [$sql, [$this->username]];
    }

    /**
     * Trae un usuario por id_usuario (cédula)
     * Devuelve SQL + params. Mantiene el ORDEN de columnas descrito arriba.
     */
    public function consultarPorId(): array
    {
        $sql = "SELECT
                    id_usuario,
                    username,
                    nombre,
                    apellido,
                    foto,
                    password_hash,
                    id_rol,
                    activo
                FROM usuario
                WHERE id_usuario = ?
                LIMIT 1";
        return [$sql, [$this->id_usuario]];
    }

    /**
     * Crear usuario.
     * Inserta: (id_usuario, username, nombre, apellido, foto, password_hash, id_rol, activo)
     * created_at lo maneja la BD por defecto.
     */
    public function crear(): array
    {
        $sql = "INSERT INTO usuario
                    (id_usuario, username, nombre, apellido, foto, password_hash, id_rol, activo)
                VALUES (?,?,?,?,?,?,?,?)";
        $params = [
            $this->id_usuario,
            $this->username,
            $this->nombre ?? '',
            $this->apellido ?? '',
            $this->foto,                 // puede ser null
            $this->password_hash,
            $this->id_rol,
            $this->activo ?? 1
        ];
        return [$sql, $params];
    }

    /** Cambiar password (hash ya calculado fuera) */
    public function cambiarPassword(): array
    {
        $sql = "UPDATE usuario SET password_hash = ? WHERE id_usuario = ?";
        return [$sql, [$this->password_hash, $this->id_usuario]];
    }

    /**
     * Actualizar perfil (username, nombre, apellido y opcionalmente foto)
     * Si foto es null -> no se actualiza la foto.
     */
    public function actualizarPerfil(): array
    {
        if ($this->foto === null) {
            $sql = "UPDATE usuario
                       SET username = ?,
                           nombre   = ?,
                           apellido = ?
                     WHERE id_usuario = ?";
            $params = [$this->username, $this->nombre, $this->apellido, $this->id_usuario];
        } else {
            $sql = "UPDATE usuario
                       SET username = ?,
                           nombre   = ?,
                           apellido = ?,
                           foto     = ?
                     WHERE id_usuario = ?";
            $params = [$this->username, $this->nombre, $this->apellido, $this->foto, $this->id_usuario];
        }
        return [$sql, $params];
    }

    /** Actualizar solo la foto */
    public function actualizarFoto(): array
    {
        $sql = "UPDATE usuario SET foto = ? WHERE id_usuario = ?";
        return [$sql, [$this->foto, $this->id_usuario]];
    }

    /** Cambiar estado activo (1/0) */
    public function cambiarEstado(): array
    {
        $sql = "UPDATE usuario SET activo = ? WHERE id_usuario = ?";
        return [$sql, [$this->activo, $this->id_usuario]];
    }

    /** ¿Existe username? (para registro) */
    public function existeUsername(): array
    {
        $sql = "SELECT 1 FROM usuario WHERE username = ? LIMIT 1";
        return [$sql, [$this->username]];
    }

    /** ¿Existe id_usuario? (para registro) */
    public function existeId(): array
    {
        $sql = "SELECT 1 FROM usuario WHERE id_usuario = ? LIMIT 1";
        return [$sql, [$this->id_usuario]];
    }

    /** ¿Existe username en otro usuario? (edición de perfil) */
    public function existeUsernameExceptoId(): array
    {
        $sql = "SELECT 1
                  FROM usuario
                 WHERE username = ?
                   AND id_usuario <> ?
                 LIMIT 1";
        return [$sql, [$this->username, $this->id_usuario]];
    }
}
