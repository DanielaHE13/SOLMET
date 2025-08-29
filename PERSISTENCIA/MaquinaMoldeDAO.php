<?php
require_once __DIR__ . '/Conexion.php';

class MaquinaMoldeDAO {
    private string $id_molde;
    private string $id_maquina;

    public function __construct(string $id_molde = "", string $id_maquina = "") {
        $this->id_molde   = $id_molde;
        $this->id_maquina = $id_maquina;
    }

    /* =====================================================
       Crear relación máquina ↔ molde
    ===================================================== */
    public function crear(): array {
        $sql = "INSERT INTO maquina_molde (id_molde, id_maquina) 
                VALUES (:id_molde, :id_maquina)";
        $param = [
            ":id_molde"   => $this->id_molde,
            ":id_maquina" => $this->id_maquina
        ];
        return [$sql, $param];
    }

    /* =====================================================
       Listar máquinas compatibles con un molde
       (solo activas en la tabla maquina)
    ===================================================== */
    public static function listarCompatibles(string $idMolde): array {
        $sql = "SELECT m.id_maquina, m.nombre, m.estado
                FROM maquina m
                INNER JOIN maquina_molde mm ON m.id_maquina = mm.id_maquina
                WHERE mm.id_molde = :id_molde
                ORDER BY m.nombre ASC";
        return [$sql, [":id_molde" => $idMolde]];
    }

    /* =====================================================
       Listar máquinas compatibles activas
    ===================================================== */
    public static function listarCompatiblesActivas(string $idMolde): array {
        $sql = "SELECT m.id_maquina, m.nombre
                FROM maquina m
                INNER JOIN maquina_molde mm ON m.id_maquina = mm.id_maquina
                WHERE mm.id_molde = :id_molde
                  AND m.estado = 'activa'
                ORDER BY m.nombre ASC";
        return [$sql, [":id_molde" => $idMolde]];
    }

    /* =====================================================
       Eliminar relación máquina ↔ molde
    ===================================================== */
    public function eliminar(): array {
        $sql = "DELETE FROM maquina_molde 
                WHERE id_molde = :id_molde AND id_maquina = :id_maquina";
        $param = [
            ":id_molde"   => $this->id_molde,
            ":id_maquina" => $this->id_maquina
        ];
        return [$sql, $param];
    }
}
