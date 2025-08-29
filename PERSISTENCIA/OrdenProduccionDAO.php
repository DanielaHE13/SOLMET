<?php
/**
 * DAO para la tabla orden_produccion
 */
class OrdenProduccionDAO
{
    private string $idOp;
    private ?string $idMolde;
    private ?string $idMaquina;
    private ?string $fechaInicioProg;
    private ?string $fechaFinEstimada;
    private ?string $estado;

    public function __construct(
        string $idOp = '',
        ?string $idMolde = null,
        ?string $idMaquina = null,
        ?string $fechaInicioProg = null,
        ?string $fechaFinEstimada = null,
        ?string $estado = null
    ) {
        $this->idOp            = $idOp;
        $this->idMolde         = $idMolde;
        $this->idMaquina       = $idMaquina;
        $this->fechaInicioProg = $fechaInicioProg;
        $this->fechaFinEstimada = $fechaFinEstimada;
        $this->estado          = $estado;
    }

    /* =========================================================
     * ================ QUERIES PRINCIPALES ====================
     * ========================================================= */

    /** Insertar nueva orden */
    public function insertar(): array {
        $sql = "INSERT INTO orden_produccion 
                  (id_op, id_molde, id_maquina, fecha_inicio_prog, fecha_fin_estimada, estado) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $params = [
            $this->idOp,
            $this->idMolde,
            $this->idMaquina,
            $this->fechaInicioProg,
            $this->fechaFinEstimada,
            $this->estado
        ];
        return [$sql, $params];
    }

    /** Actualizar una orden existente */
    public function actualizar(): array {
        $sql = "UPDATE orden_produccion 
                   SET id_molde = ?, id_maquina = ?, fecha_inicio_prog = ?, fecha_fin_estimada = ?, estado = ?
                 WHERE id_op = ?";
        $params = [
            $this->idMolde,
            $this->idMaquina,
            $this->fechaInicioProg,
            $this->fechaFinEstimada,
            $this->estado,
            $this->idOp
        ];
        return [$sql, $params];
    }

    /** Eliminar una orden */
    public function eliminar(): array {
        $sql = "DELETE FROM orden_produccion WHERE id_op = ?";
        return [$sql, [$this->idOp]];
    }

    /** Consultar una orden por su id */
    public function consultar(): array {
        $sql = "SELECT id_op, id_molde, id_maquina, fecha_inicio_prog, fecha_fin_estimada, estado
                  FROM orden_produccion
                 WHERE id_op = ?";
        return [$sql, [$this->idOp]];
    }

    /** Listar todas las órdenes */
    public function listarTodos(): array {
        $sql = "SELECT id_op, id_molde, id_maquina, fecha_inicio_prog, fecha_fin_estimada, estado
                  FROM orden_produccion
              ORDER BY fecha_inicio_prog DESC";
        return [$sql, []];
    }

    /** Consultar último consecutivo */
    public function maxId(): array {
        $sql = "SELECT MAX(id_op) AS max_id FROM orden_produccion";
        return [$sql, []];
    }

    /** Consultar por estado (ej: 'pendiente', 'en_proceso', 'finalizada') */
    public function listarPorEstado(): array {
        $sql = "SELECT id_op, id_molde, id_maquina, fecha_inicio_prog, fecha_fin_estimada, estado
                  FROM orden_produccion
                 WHERE estado = ?
              ORDER BY fecha_inicio_prog DESC";
        return [$sql, [$this->estado]];
    }

    /* =========================================================
     * ================ QUERIES EXTRA OPCIONALES ===============
     * ========================================================= */

    /** Cambiar solo el estado de una orden */
    public function cambiarEstado(): array {
        $sql = "UPDATE orden_produccion SET estado = ? WHERE id_op = ?";
        return [$sql, [$this->estado, $this->idOp]];
    }

    /** Listar órdenes entre un rango de fechas */
    public function listarPorRangoFechas(string $desde, string $hasta): array {
        $sql = "SELECT id_op, id_molde, id_maquina, fecha_inicio_prog, fecha_fin_estimada, estado
                  FROM orden_produccion
                 WHERE fecha_inicio_prog BETWEEN ? AND ?
              ORDER BY fecha_inicio_prog ASC";
        return [$sql, [$desde, $hasta]];
    }

    /** Listar detallado con nombres de molde y máquina */
    public function listarDetallado(): array {
        $sql = "SELECT o.id_op, o.estado, o.fecha_inicio_prog, o.fecha_fin_estimada,
                       m.nombre AS molde, ma.nombre AS maquina
                  FROM orden_produccion o
            INNER JOIN molde m   ON o.id_molde   = m.id_molde
            INNER JOIN maquina ma ON o.id_maquina = ma.id_maquina
              ORDER BY o.fecha_inicio_prog DESC";
        return [$sql, []];
    }
}
