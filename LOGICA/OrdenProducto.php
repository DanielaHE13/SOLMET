<?php
require_once __DIR__ . '/../persistencia/Conexion.php';
require_once __DIR__ . '/../persistencia/OrdenProductoDAO.php';

class OrdenProducto
{
    /** ========================== CRUD ========================== */

    /** Inserta un producto en una orden */
    public static function insertar(
        int $idOp,
        string $idProducto,
        int $cantidad,
        float $pesoTeoricoUnitario,
        float $ciclosPorMinuto
    ): array {
        if ($idOp <= 0 || $idProducto === '') {
            return ['ok' => false, 'msg' => 'Id de orden y producto requeridos.'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();
            $dao = new OrdenProductoDAO($idOp, $idProducto, $cantidad, $pesoTeoricoUnitario, $ciclosPorMinuto);
            [$sql, $p] = $dao->insertar();
            $cx->ejecutar($sql, $p);
            return ['ok' => true, 'msg' => 'Producto agregado a la orden.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al insertar producto'
                . (ini_get('display_errors') ? ': ' . $e->getMessage() : '.')];
        } finally {
            $cx->cerrar();
        }
    }

    /** Actualiza datos de un producto en una orden */
    public static function actualizar(
        int $idOp,
        string $idProducto,
        int $cantidad,
        float $pesoTeoricoUnitario,
        float $ciclosPorMinuto
    ): array {
        if ($idOp <= 0 || $idProducto === '') {
            return ['ok' => false, 'msg' => 'Id de orden y producto requeridos.'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();
            $dao = new OrdenProductoDAO($idOp, $idProducto, $cantidad, $pesoTeoricoUnitario, $ciclosPorMinuto);
            [$sql, $p] = $dao->actualizar();
            $cx->ejecutar($sql, $p);
            return ['ok' => true, 'msg' => 'Producto actualizado en la orden.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al actualizar producto'
                . (ini_get('display_errors') ? ': ' . $e->getMessage() : '.')];
        } finally {
            $cx->cerrar();
        }
    }

    /** Elimina un producto puntual de una orden */
    public static function eliminar(int $idOp, string $idProducto): array
    {
        if ($idOp <= 0 || $idProducto === '') {
            return ['ok' => false, 'msg' => 'Id de orden y producto requeridos.'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new OrdenProductoDAO($idOp, $idProducto))->eliminar();
            $cx->ejecutar($sql, $p);
            return ['ok' => true, 'msg' => 'Producto eliminado de la orden.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al eliminar producto'
                . (ini_get('display_errors') ? ': ' . $e->getMessage() : '.')];
        } finally {
            $cx->cerrar();
        }
    }

    /** Elimina todos los productos de una orden */
    public static function eliminarPorOrden(int $idOp): array
    {
        if ($idOp <= 0) {
            return ['ok' => false, 'msg' => 'Id de orden requerido.'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new OrdenProductoDAO($idOp))->eliminarPorOrden();
            $cx->ejecutar($sql, $p);
            return ['ok' => true, 'msg' => 'Productos eliminados de la orden.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al eliminar productos'
                . (ini_get('display_errors') ? ': ' . $e->getMessage() : '.')];
        } finally {
            $cx->cerrar();
        }
    }

    /** Lista productos de una orden */
    public static function listarPorOrden(int $idOp): array
    {
        if ($idOp <= 0) return [];

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new OrdenProductoDAO($idOp))->listarPorOrden();
            $cx->ejecutar($sql, $p);

            $out = [];
            while ($r = $cx->registro()) {
                $out[] = [
                    'id_producto'    => $r[0],
                    'nombre'         => $r[1],
                    'cantidad'       => (int)$r[2],
                    'peso_unitario'  => (float)$r[3],
                    'cpm'            => (float)$r[4],
                ];
            }
            return $out;
        } finally {
            $cx->cerrar();
        }
    }

    /** Verifica si un producto ya está en la orden */
    public static function existeEnOrden(int $idOp, string $idProducto): bool
    {
        if ($idOp <= 0 || $idProducto === '') return false;

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new OrdenProductoDAO($idOp, $idProducto))->existeEnOrden();
            $cx->ejecutar($sql, $p);
            return (bool)$cx->registro();
        } finally {
            $cx->cerrar();
        }
    }
}
