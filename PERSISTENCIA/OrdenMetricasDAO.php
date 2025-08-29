<?php
class OrdenMetricasDAO
{
    private string $idOrden;
    private ?float $pesoTeorico;
    private ?float $pesoReal;
    private ?float $devolucionTeorica;
    private ?float $devolucionReal;

    public function __construct(
        string $idOrden = "",
        ?float $pesoTeorico = null,
        ?float $pesoReal = null,
        ?float $devolucionTeorica = null,
        ?float $devolucionReal = null
    ) {
        $this->idOrden          = $idOrden;
        $this->pesoTeorico      = $pesoTeorico;
        $this->pesoReal         = $pesoReal;
        $this->devolucionTeorica= $devolucionTeorica;
        $this->devolucionReal   = $devolucionReal;
    }

    /**
     * Inserta o actualiza métricas de una orden (UPSERT)
     */
    public function upsert(): array {
        $sql = "INSERT INTO orden_metricas
                   (id_op, peso_teorico_total_kg, peso_real_total_kg, devolucion_teorica_kg, devolucion_real_kg)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                   peso_teorico_total_kg = VALUES(peso_teorico_total_kg),
                   peso_real_total_kg    = VALUES(peso_real_total_kg),
                   devolucion_teorica_kg = VALUES(devolucion_teorica_kg),
                   devolucion_real_kg    = VALUES(devolucion_real_kg)";
        return [$sql, [
            $this->idOrden,
            $this->pesoTeorico,
            $this->pesoReal,
            $this->devolucionTeorica,
            $this->devolucionReal
        ]];
    }

    /**
     * Consulta métricas de una orden
     */
    public function consultar(): array {
        $sql = "SELECT peso_teorico_total_kg, peso_real_total_kg,
                       devolucion_teorica_kg, devolucion_real_kg
                  FROM orden_metricas
                 WHERE id_op = ?";
        return [$sql, [$this->idOrden]];
    }

    /**
     * Elimina métricas de una orden
     */
    public function eliminarPorOrden(): array {
        $sql = "DELETE FROM orden_metricas WHERE id_op = ?";
        return [$sql, [$this->idOrden]];
    }

    /**
     * Verifica si existen métricas para una orden
     */
    public function existe(): array {
        $sql = "SELECT 1 FROM orden_metricas WHERE id_op = ? LIMIT 1";
        return [$sql, [$this->idOrden]];
    }
}
