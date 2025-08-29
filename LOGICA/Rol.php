<?php
require_once __DIR__ . '/../persistencia/Conexion.php';
require_once __DIR__ . '/../persistencia/RolDAO.php';

class Rol
{
    /** Obtener un rol por id */
    public static function obtener(string $id): ?array
    {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new RolDAO($id))->consultarPorId();
            $cx->ejecutar($sql, $p);
            $r = $cx->registro(); // [id_rol, nombre]
            if (!$r) return null;

            return [
                'id'     => (string)$r['id_rol'],
                'nombre' => (string)$r['nombre'],
            ];
        } finally {
            $cx->cerrar();
        }
    }

    /** Listar todos los roles */
    public static function listar(): array
    {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new RolDAO())->listarTodos();
            $cx->ejecutar($sql, $p);
            $rows = $cx->registros(); // [[id_rol, nombre], ...]
            return array_map(fn($r) => [
                'id'     => (string)$r['id_rol'],
                'nombre' => (string)$r['nombre'],
            ], $rows);
        } finally {
            $cx->cerrar();
        }
    }

    /** Crear rol */
    public static function crear(string $id, string $nombre): array
    {
        if ($id === '' || $nombre === '') {
            return ['ok' => false, 'msg' => 'Id y nombre son obligatorios.'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();

            // Validar si ya existe
            [$sqlex, $pex] = (new RolDAO($id))->existeId();
            $cx->ejecutar($sqlex, $pex);
            if ($cx->registro()) {
                return ['ok' => false, 'msg' => 'El ID de rol ya existe.'];
            }

            [$sql, $p] = (new RolDAO($id, $nombre))->crear();
            $cx->ejecutar($sql, $p);
            return ['ok' => true, 'msg' => 'Rol creado.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al crear rol' . (ini_get('display_errors') ? ': ' . $e->getMessage() : '.')];
        } finally {
            $cx->cerrar();
        }
    }

    /** Actualizar rol */
    public static function actualizar(string $id, string $nombre): array
    {
        if ($id === '') {
            return ['ok' => false, 'msg' => 'Id es obligatorio.'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new RolDAO($id, $nombre))->actualizar();
            $cx->ejecutar($sql, $p);
            return ['ok' => true, 'msg' => 'Rol actualizado.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al actualizar rol' . (ini_get('display_errors') ? ': ' . $e->getMessage() : '.')];
        } finally {
            $cx->cerrar();
        }
    }

    /** Eliminar rol */
    public static function eliminar(string $id): array
    {
        if ($id === '') {
            return ['ok' => false, 'msg' => 'Id es obligatorio.'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new RolDAO($id))->eliminar();
            $cx->ejecutar($sql, $p);
            $ok = $cx->filas() > 0;
            return ['ok' => $ok, 'msg' => $ok ? 'Rol eliminado.' : 'No se eliminó el rol.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al eliminar rol' . (ini_get('display_errors') ? ': ' . $e->getMessage() : '.')];
        } finally {
            $cx->cerrar();
        }
    }
}
