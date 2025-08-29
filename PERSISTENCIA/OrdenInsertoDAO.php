<?php
class OrdenInsertoDAO {
    private string $idOrden;
    private string $idInserto;
    private int $cantidad;

    public function __construct(
        string $idOrden = "",
        string $idInserto = "",
        int $cantidad = 0
    ) {
        $this->idOrden   = $idOrden;
        $this->idInserto = $idInserto;
        $this->cantidad  = $cantidad;
    }

    /* ==============================
       Insertar relación Orden ↔ Inserto
    ============================== */
    public function crear(): array {
        $sql = "INSERT INTO orden_inserto (id_orden, id_inserto, cantidad)
                VALUES (:idOrden, :idInserto, :cantidad)";
        $param = [
            ":idOrden"   => $this->idOrden,
            ":idInserto" => $this->idInserto,
            ":cantidad"  => $this->cantidad
        ];
        return [$sql, $param];
    }

    /* ==============================
       Consultar insertos de una orden
    ============================== */
    public function listarPorOrden(): array {
        $sql = "SELECT oi.id_inserto, i.descripcion, oi.cantidad
                  FROM orden_inserto oi
            INNER JOIN inserto i ON oi.id_inserto = i.id_inserto
                 WHERE oi.id_orden = :idOrden";
        $param = [":idOrden" => $this->idOrden];
        return [$sql, $param];
    }

    /* ==============================
       Actualizar cantidad de un inserto en la orden
    ============================== */
    public function actualizar(): array {
        $sql = "UPDATE orden_inserto 
                   SET cantidad = :cantidad
                 WHERE id_orden = :idOrden AND id_inserto = :idInserto";
        $param = [
            ":cantidad"  => $this->cantidad,
            ":idOrden"   => $this->idOrden,
            ":idInserto" => $this->idInserto
        ];
        return [$sql, $param];
    }

    /* ==============================
       Eliminar un inserto de la orden
    ============================== */
    public function eliminar(): array {
        $sql = "DELETE FROM orden_inserto 
                 WHERE id_orden = :idOrden AND id_inserto = :idInserto";
        $param = [
            ":idOrden"   => $this->idOrden,
            ":idInserto" => $this->idInserto
        ];
        return [$sql, $param];
    }

    /* ==============================
       Eliminar todos los insertos de una orden
    ============================== */
    public static function eliminarPorOrden(string $idOrden): array {
        $sql = "DELETE FROM orden_inserto WHERE id_orden = :idOrden";
        $param = [":idOrden" => $idOrden];
        return [$sql, $param];
    }
}
