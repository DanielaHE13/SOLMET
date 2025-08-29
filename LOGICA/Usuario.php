<?php
require_once __DIR__ . '/../persistencia/Conexion.php';
require_once __DIR__ . '/../persistencia/UsuarioDAO.php';

class Usuario {

    private string  $id;
    private string  $username;
    private string  $nombre;
    private string  $apellido;
    private string  $passwordPlano;
    private string  $idRol;
    private ?string $foto;

    public function __construct(
        string $id = '',
        string $username = '',
        string $nombre = '',
        string $apellido = '',
        string $passwordPlano = '',
        string $idRol = '',
        ?string $foto = null
    ) {
        $this->id            = $id;
        $this->username      = $username;
        $this->nombre        = $nombre;
        $this->apellido      = $apellido;
        $this->passwordPlano = $passwordPlano;
        $this->idRol         = $idRol;
        $this->foto          = $foto;
    }

    /* =========================================================
       LOGIN: valida usuario y contraseña
       Devuelve: ['ok'=>bool,'msg'=>string?,'usuario'=>array?]
       Orden de columnas esperado (FETCH_NUM o BOTH):
       [0] id_usuario,[1] username,[2] nombre,[3] apellido,
       [4] foto,[5] password_hash,[6] id_rol,[7] activo
       ========================================================= */
    public function login(string $username, string $password): array {
        $dao = new UsuarioDAO(null, $username);
        [$sql, $params] = $dao->consultarPorUsername();

        $cx = new Conexion();
        try {
            $cx->abrir();
            $cx->ejecutar($sql, $params);
            $row = $cx->registro(); // ideal: FETCH_NUM o BOTH

            if (!$row) {
                return ['ok' => false, 'msg' => 'Usuario no encontrado'];
            }

            // Soporta BOTH/ASSOC/NUM con el operador null coalescing
            $activo        = (int)($row[7] ?? $row['activo'] ?? 0);
            $password_hash = (string)($row[5] ?? $row['password_hash'] ?? '');

            if ($activo !== 1) {
                return ['ok' => false, 'msg' => 'Usuario inactivo'];
            }
            if (!password_verify($password, $password_hash)) {
                return ['ok' => false, 'msg' => 'Contraseña incorrecta'];
            }

            return [
                'ok' => true,
                'usuario' => [
                    'id_usuario' => $row[0] ?? $row['id_usuario'] ?? null,
                    'username'   => $row[1] ?? $row['username'] ?? '',
                    'nombre'     => $row[2] ?? $row['nombre'] ?? '',
                    'apellido'   => $row[3] ?? $row['apellido'] ?? '',
                    'foto'       => $row[4] ?? $row['foto'] ?? null,
                    'id_rol'     => (int)($row[6] ?? $row['id_rol'] ?? 0),
                    'activo'     => $activo,
                ]
            ];
        } finally {
            $cx->cerrar();
        }
    }

    /* =========================================================
       CREAR: registra nuevo usuario (hash dentro del método)
       Usa UsuarioDAO->existeId(), existeUsername() y crear()
       ========================================================= */
    public static function crear(
        string $id,
        string $username,
        string $nombre,
        string $apellido,
        string $passwordPlano,
        string $idRol,
        ?string $foto = null
    ): array {
        if ($id === '' || $username === '' || $nombre === '' || $apellido === '' || $passwordPlano === '' || $idRol === '') {
            return ['ok' => false, 'msg' => 'Todos los campos son obligatorios'];
        }

        $hash = password_hash($passwordPlano, PASSWORD_DEFAULT);
        $cx = new Conexion();

        try {
            $cx->abrir();

            // Validar id (cédula)
            [$sqlId, $pId] = (new UsuarioDAO($id))->existeId();
            $cx->ejecutar($sqlId, $pId);
            if ($cx->registro()) {
                return ['ok' => false, 'msg' => 'Cédula ya registrada'];
            }

            // Validar username
            [$sqlU, $pU] = (new UsuarioDAO(null, $username))->existeUsername();
            $cx->ejecutar($sqlU, $pU);
            if ($cx->registro()) {
                return ['ok' => false, 'msg' => 'Usuario ya registrado'];
            }

            // Insertar (usar el orden del constructor del DAO)
            // __construct($id_usuario, $username, $password_hash, $id_rol, $activo, $nombre, $apellido, $foto)
            $daoInsert = new UsuarioDAO(
                $id,
                $username,
                $hash,
                (int)$idRol,
                1,
                $nombre,
                $apellido,
                $foto
            );
            [$sqlIns, $pIns] = $daoInsert->crear();
            $cx->ejecutar($sqlIns, $pIns);

            return ['ok' => true, 'msg' => 'Usuario creado correctamente'];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al crear usuario: ' . $e->getMessage()];
        } finally {
            $cx->cerrar();
        }
    }

    /* =========================================================
       Consultar por id (útil para poblar sesión/encabezados)
       Devuelve arreglo ASOCIATIVO con claves esperadas
       ========================================================= */
    public function consultarPorId(): ?array {
        if ($this->id === '') return null;

        $dao = new UsuarioDAO($this->id, null);
        [$sql, $params] = $dao->consultarPorId();

        $cx = new Conexion();
        try {
            $cx->abrir();
            $cx->ejecutar($sql, $params);
            $row = $cx->registro();
            if (!$row) return null;

            // Normaliza a asociativo
            return [
                'id_usuario'    => $row['id_usuario']    ?? ($row[0] ?? null),
                'username'      => $row['username']      ?? ($row[1] ?? ''),
                'nombre'        => $row['nombre']        ?? ($row[2] ?? ''),
                'apellido'      => $row['apellido']      ?? ($row[3] ?? ''),
                'foto'          => $row['foto']          ?? ($row[4] ?? null),
                'password_hash' => $row['password_hash'] ?? ($row[5] ?? null),
                'id_rol'        => (int)($row['id_rol']  ?? ($row[6] ?? 0)),
                'activo'        => (int)($row['activo']  ?? ($row[7] ?? 0)),
            ];
        } finally {
            $cx->cerrar();
        }
    }
}
