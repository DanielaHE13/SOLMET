<?php
require_once __DIR__ . '/../persistencia/Conexion.php';
require_once __DIR__ . '/../persistencia/ProductoDAO.php';

class Producto
{
    /** Obtiene un producto por id o null */
    public static function obtener(string $id): ?array
    {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new ProductoDAO($id))->consultarPorId();
            $cx->ejecutar($sql, $p);
            $r = $cx->registro(); 
            if (!$r) return null;

            return [
                'id'      => (string)$r[0],
                'nombre'  => (string)$r[1],
                'peso_g'  => is_null($r[2]) ? null : (float)$r[2],
                'cpm'     => is_null($r[3]) ? null : (float)$r[3],
            ];
        } finally {
            $cx->cerrar();
        }
    }

    /** Lista productos con filtro opcional */
    public static function listar(string $q = ""): array
    {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new ProductoDAO())->listar($q);
            $cx->ejecutar($sql, $p);
            $rows = $cx->registros();
            return array_map(fn($r) => [
                'id'      => (string)$r[0],
                'nombre'  => (string)$r[1],
                'peso_g'  => is_null($r[2]) ? null : (float)$r[2],
                'cpm'     => is_null($r[3]) ? null : (float)$r[3],
            ], $rows);
        } finally {
            $cx->cerrar();
        }
    }

    /** Crear producto */
    public static function crear(string $id, string $nombre, ?float $peso_g, ?float $cpm): array
    {
        if ($id === '' || $nombre === '') {
            return ['ok' => false, 'msg' => 'Id y nombre son obligatorios.'];
        }
        if ($peso_g !== null && $peso_g <= 0) {
            return ['ok'=>false,'msg'=>'El peso debe ser mayor a 0.'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sqlex, $pex] = (new ProductoDAO($id))->existeId();
            $cx->ejecutar($sqlex, $pex);
            if ($cx->registro()) {
                return ['ok' => false, 'msg' => 'El ID de producto ya existe.'];
            }

            [$sql, $p] = (new ProductoDAO($id, $nombre, $peso_g, $cpm))->crear();
            $cx->ejecutar($sql, $p);
            return ['ok' => true, 'msg' => 'Producto creado.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al crear producto' . (ini_get('display_errors') ? ': ' . $e->getMessage() : '.')];
        } finally {
            $cx->cerrar();
        }
    }

    /** Actualizar producto */
    public static function actualizar(string $id, string $nombre, ?float $peso_g, ?float $cpm): array
    {
        if ($id === '') {
            return ['ok' => false, 'msg' => 'Id es obligatorio.'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new ProductoDAO($id, $nombre, $peso_g, $cpm))->actualizar();
            $cx->ejecutar($sql, $p);
            $ok = $cx->filas() > 0;
            return ['ok' => $ok, 'msg' => $ok ? 'Producto actualizado.' : 'No se actualizó el producto.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al actualizar producto' . (ini_get('display_errors') ? ': ' . $e->getMessage() : '.')];
        } finally {
            $cx->cerrar();
        }
    }

    /** Eliminar producto */
    public static function eliminar(string $id): array
    {
        if ($id === '') {
            return ['ok' => false, 'msg' => 'Id es obligatorio.'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new ProductoDAO($id))->eliminar();
            $cx->ejecutar($sql, $p);
            $ok = $cx->filas() > 0;
            return ['ok' => $ok, 'msg' => $ok ? 'Producto eliminado.' : 'No se eliminó el producto.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al eliminar producto' . (ini_get('display_errors') ? ': ' . $e->getMessage() : '.')];
        } finally {
            $cx->cerrar();
        }
    }

    /** Compatibles por molde */
    public static function listarCompatiblesPorMolde(string $idMolde): array
    {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new ProductoDAO())->listarCompatiblesPorMolde($idMolde);
            $cx->ejecutar($sql, $p);
            $rows = $cx->registros();
            return array_map(fn($r) => [
                'id'      => (string)$r[0],
                'nombre'  => (string)$r[1],
                'peso_g'  => is_null($r[2]) ? null : (float)$r[2],
                'cpm'     => is_null($r[3]) ? null : (float)$r[3],
            ], $rows);
        } finally {
            $cx->cerrar();
        }
    }

    /** Buscar productos por nombre con paginación */
    public static function buscarPorNombre(string $q="", int $limit=50, int $offset=0): array
    {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new ProductoDAO())->buscarPorNombre($q, $limit, $offset);
            $cx->ejecutar($sql, $p);
            $rows = $cx->registros();
            return array_map(fn($r) => [
                'id'=>(string)$r[0],
                'nombre'=>(string)$r[1],
                'peso_g'=>is_null($r[2])?null:(float)$r[2],
                'cpm'=>is_null($r[3])?null:(float)$r[3],
            ], $rows);
        } finally {
            $cx->cerrar();
        }
    }
}
