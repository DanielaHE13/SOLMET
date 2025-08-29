<?php
require_once __DIR__ . '/Conexion.php';

class MaquinaDAO {
    private string $id_maquina;
    private string $nombre;
    private string $estado;

    public function __construct(
        string $id_maquina = "",
        string $nombre = "",
        string $estado = "activa"
    ) {
        $this->id_maquina = $id_maquina;
        $this->nombre     = $nombre;
        $this->estado     = $estado;
    }

    /* =====================================================
       Crear nueva máquina
    ===================================================== */
    public function crear(): array {
        $sql = "INSERT INTO maquina (id_maquina, nombre, estado) 
                VALUES (:id_maquina, :nombre, :estado)";
        $param = [
            ":id_maquina" => $this->id_maquina,
            ":nombre"     => $this->nombre,
            ":estado"     => $this->estado
        ];
        return [$sql, $param];
    }

    /* =====================================================
       Consultar máquina por ID
    ===================================================== */
    public function consultarPorId(): array {
        $sql = "SELECT id_maquina, nombre, estado 
                FROM maquina 
                WHERE id_maquina = :id_maquina";
        return [$sql, [":id_maquina" => $this->id_maquina]];
    }

    /* =====================================================
       Listar todas las máquinas
    ===================================================== */
    public static function listarTodas(): array {
        $sql = "SELECT id_maquina, nombre, estado 
                FROM maquina 
                ORDER BY nombre ASC";
        return [$sql, []];
    }

    /* =====================================================
       Listar máquinas activas
    ===================================================== */
    public static function listarActivas(): array {
        $sql = "SELECT id_maquina, nombre 
                FROM maquina 
                WHERE estado = 'activa' 
                ORDER BY nombre ASC";
        return [$sql, []];
    }

    /* =====================================================
       Actualizar máquina
    ===================================================== */
    public function actualizar(): array {
        $sql = "UPDATE maquina 
                SET nombre = :nombre, estado = :estado 
                WHERE id_maquina = :id_maquina";
        $param = [
            ":nombre"     => $this->nombre,
            ":estado"     => $this->estado,
            ":id_maquina" => $this->id_maquina
        ];
        return [$sql, $param];
    }

    /* =====================================================
       Eliminar lógico (cambiar estado a 'inactiva')
    ===================================================== */
    public function eliminar(): array {
        $sql = "UPDATE maquina 
                SET estado = 'inactiva' 
                WHERE id_maquina = :id_maquina";
        return [$sql, [":id_maquina" => $this->id_maquina]];
    }
    public function listarCompatibles(string $idMolde): array {
        $sql = "SELECT m.id_maquina, m.nombre
                  FROM maquina m
            INNER JOIN maquina_molde mm ON m.id_maquina = mm.id_maquina
                 WHERE mm.id_molde = ?
                   AND m.estado = 'activa'
              ORDER BY m.nombre ASC";
        return [$sql, [$idMolde]];
    }
}
