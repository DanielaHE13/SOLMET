<?php
/**
 * DAO para la tabla orden_producto
 * Relación: una orden de producción puede tener hasta N productos con cantidades y métricas.
 */

class OrdenProductoDAO
{
    private ?int $idOp;
    private ?string $idProducto;
    private ?int $cantidad;
    private ?float $pesoTeoricoUnitario;
    private ?float $ciclosPorMinuto;

    public function __construct(
        ?int $idOp = null,
        ?string $idProducto = null,
        ?int $cantidad = null,
        ?float $pesoTeoricoUnitario = null,
        ?float $ciclosPorMinuto = null
    ) {
        $this->idOp                = $idOp;
        $this->idProducto          = $idProducto;
        $this->cantidad            = $cantidad;
        $this->pesoTeoricoUnitario = $pesoTeoricoUnitario;
        $this->ciclosPorMinuto     = $ciclosPorMinuto;
    }

    /** Inserta un producto en una orden */
    public function insertar(): array
    {
        $sql = "INSERT INTO orden_producto (id_op, id_producto, cantidad, peso_teorico_unitario_g, ciclos_por_minuto)
                VALUES (?, ?, ?, ?, ?)";
        $params = [
            $this->idOp,
            $this->idProducto,
            $this->cantidad,
            $this->pesoTeoricoUnitario,
            $this->ciclosPorMinuto
        ];
        return [$sql, $params];
    }

    /** Actualiza la cantidad o métricas de un producto en una orden */
    public function actualizar(): array
    {
        $sql = "UPDATE orden_producto
                   SET cantidad = ?, peso_teorico_unitario_g = ?, ciclos_por_minuto = ?
                 WHERE id_op = ? AND id_producto = ?";
        $params = [
            $this->cantidad,
            $this->pesoTeoricoUnitario,
            $this->ciclosPorMinuto,
            $this->idOp,
            $this->idProducto
        ];
        return [$sql, $params];
    }

    /** Elimina un producto de una orden */
    public function eliminar(): array
    {
        $sql = "DELETE FROM orden_producto WHERE id_op = ? AND id_producto = ?";
        return [$sql, [$this->idOp, $this->idProducto]];
    }

    /** Elimina todos los productos de una orden */
    public function eliminarPorOrden(): array
    {
        $sql = "DELETE FROM orden_producto WHERE id_op = ?";
        return [$sql, [$this->idOp]];
    }

    /** Consulta los productos de una orden */
    public function listarPorOrden(): array
    {
        $sql = "SELECT op.id_producto, p.nombre, op.cantidad,
                       op.peso_teorico_unitario_g, op.ciclos_por_minuto
                  FROM orden_producto op
            INNER JOIN producto p ON op.id_producto = p.id_producto
                 WHERE op.id_op = ?
              ORDER BY p.nombre ASC";
        return [$sql, [$this->idOp]];
    }

    /** Consulta si un producto ya está registrado en una orden */
    public function existeEnOrden(): array
    {
        $sql = "SELECT 1 FROM orden_producto WHERE id_op = ? AND id_producto = ? LIMIT 1";
        return [$sql, [$this->idOp, $this->idProducto]];
    }
}
