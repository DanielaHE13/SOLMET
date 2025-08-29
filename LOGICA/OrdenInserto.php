<?php
require_once __DIR__ . '/../persistencia/Conexion.php';
require_once __DIR__ . '/../persistencia/OrdenInsertoDAO.php';

class OrdenInserto {
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
       Crear relación Orden ↔ Inserto
    ============================== */
    public function crear(): bool {
        try {
            $cx = new Conexion();
            $cx->abrir();
            [$sql, $param] = (new OrdenInsertoDAO(
                $this->idOrden,
                $this->idInserto,
                $this->cantidad
            ))->crear();
            $ok = $cx->ejecutar($sql, $param);
            $cx->cerrar();
            return (bool) $ok;
        } catch (Throwable $e) {
            return false;
        }
    }

    /* ==============================
       Listar insertos de una orden
    ============================== */
    public static function listarPorOrden(string $idOrden): array {
        $cx = new Conexion();
        $cx->abrir();
        [$sql, $param] = (new OrdenInsertoDAO($idOrden))->listarPorOrden();
        $cx->ejecutar($sql, $param);

        $resultados = [];
        while ($r = $cx->registro()) {
            $resultados[] = [
                "id_inserto"  => $r["id_inserto"] ?? $r[0],
                "descripcion" => $r["descripcion"] ?? $r[1],
                "cantidad"    => $r["cantidad"] ?? $r[2]
            ];
        }
        $cx->cerrar();
        return $resultados;
    }

    /* ==============================
       Actualizar cantidad de un inserto
    ============================== */
    public function actualizar(): bool {
        try {
            $cx = new Conexion();
            $cx->abrir();
            [$sql, $param] = (new OrdenInsertoDAO(
                $this->idOrden,
                $this->idInserto,
                $this->cantidad
            ))->actualizar();
            $ok = $cx->ejecutar($sql, $param);
            $cx->cerrar();
            return (bool) $ok;
        } catch (Throwable $e) {
            return false;
        }
    }

    /* ==============================
       Eliminar un inserto de la orden
    ============================== */
    public function eliminar(): bool {
        try {
            $cx = new Conexion();
            $cx->abrir();
            [$sql, $param] = (new OrdenInsertoDAO(
                $this->idOrden,
                $this->idInserto
            ))->eliminar();
            $ok = $cx->ejecutar($sql, $param);
            $cx->cerrar();
            return (bool) $ok;
        } catch (Throwable $e) {
            return false;
        }
    }

    /* ==============================
       Eliminar todos los insertos de la orden
    ============================== */
    public static function eliminarPorOrden(string $idOrden): bool {
        try {
            $cx = new Conexion();
            $cx->abrir();
            [$sql, $param] = OrdenInsertoDAO::eliminarPorOrden($idOrden);
            $ok = $cx->ejecutar($sql, $param);
            $cx->cerrar();
            return (bool) $ok;
        } catch (Throwable $e) {
            return false;
        }
    }

    /* ==============================
       Getters y Setters
    ============================== */
    public function getIdOrden(): string { return $this->idOrden; }
    public function setIdOrden(string $idOrden): void { $this->idOrden = $idOrden; }

    public function getIdInserto(): string { return $this->idInserto; }
    public function setIdInserto(string $idInserto): void { $this->idInserto = $idInserto; }

    public function getCantidad(): int { return $this->cantidad; }
    public function setCantidad(int $cantidad): void { $this->cantidad = $cantidad; }
}
