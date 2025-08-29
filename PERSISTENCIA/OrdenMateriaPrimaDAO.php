<?php
class OrdenMateriaPrimaDAO {
    private string $idOrden;
    private string $tipo;
    private ?string $codigo;
    private ?float $pPlan;
    private ?float $kgPlan;
    private ?float $kgReal;

    public function __construct(
        string $idOrden = "",
        string $tipo = "",
        ?string $codigo = null,
        ?float $pPlan = null,
        ?float $kgPlan = null,
        ?float $kgReal = null
    ) {
        $this->idOrden = $idOrden;
        $this->tipo    = $tipo;
        $this->codigo  = $codigo;
        $this->pPlan   = $pPlan;
        $this->kgPlan  = $kgPlan;
        $this->kgReal  = $kgReal;
    }

    /** INSERT o UPDATE si ya existe la clave */
    public function upsert(): array {
        $sql = "INSERT INTO orden_materia_prima (id_op, tipo, codigo_mp, porcentaje_plan, kg_plan, kg_real)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    porcentaje_plan = VALUES(porcentaje_plan),
                    kg_plan         = VALUES(kg_plan),
                    kg_real         = VALUES(kg_real)";
        return [$sql, [
            $this->idOrden,
            $this->tipo,
            $this->codigo,
            $this->pPlan,
            $this->kgPlan,
            $this->kgReal
        ]];
    }

    /** Lista materia prima asociada a una orden */
    public function listar(): array {
        $sql = "SELECT tipo, codigo_mp, porcentaje_plan, kg_plan, kg_real
                  FROM orden_materia_prima
                 WHERE id_op = ?";
        return [$sql, [$this->idOrden]];
    }

    /** Actualiza solo el kg_real de un registro existente */
    public function actualizarKgReal(): array {
        $sql = "UPDATE orden_materia_prima
                   SET kg_real = ?
                 WHERE id_op = ? AND tipo = ? AND codigo_mp = ?";
        return [$sql, [$this->kgReal, $this->idOrden, $this->tipo, $this->codigo]];
    }

    /** Elimina un registro puntual de materia prima de una OP */
    public function eliminarUno(): array {
        $sql = "DELETE FROM orden_materia_prima
                 WHERE id_op = ? AND tipo = ? AND codigo_mp = ?";
        return [$sql, [$this->idOrden, $this->tipo, $this->codigo]];
    }

    /** Elimina todas las materias primas de una OP */
    public static function eliminarPorOrden(string $idOrden): array {
        $sql = "DELETE FROM orden_materia_prima WHERE id_op = ?";
        return [$sql, [$idOrden]];
    }
}
