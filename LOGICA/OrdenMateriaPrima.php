<?php
require_once __DIR__ . '/../persistencia/Conexion.php';
require_once __DIR__ . '/../persistencia/OrdenMateriaPrimaDAO.php';

class OrdenMateriaPrima
{
    /**
     * Lista materias primas de una orden
     */
    public static function listar(string $idOrden): array {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new OrdenMateriaPrimaDAO($idOrden))->listar();
            $cx->ejecutar($sql, $p);

            $rows = [];
            while ($r = $cx->registro()) {
                $rows[] = [
                    'tipo'            => (string)$r[0],
                    'codigo_mp'       => (string)$r[1],
                    'porcentaje_plan' => $r[2] === null ? null : (float)$r[2],
                    'kg_plan'         => $r[3] === null ? null : (float)$r[3],
                    'kg_real'         => $r[4] === null ? null : (float)$r[4],
                ];
            }
            return $rows;
        } finally {
            $cx->cerrar();
        }
    }

    /**
     * Inserta o actualiza (UPSERT) una materia prima de la orden
     */
    public static function upsert(
        string $idOrden,
        string $tipo,
        string $codigo,
        ?float $pPlan,
        ?float $kgPlan,
        ?float $kgReal = null
    ): array {
        if ($idOrden === '' || $tipo === '' || $codigo === '') {
            return ['ok' => false, 'msg' => 'Parámetros obligatorios faltantes.'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new OrdenMateriaPrimaDAO($idOrden, $tipo, $codigo, $pPlan, $kgPlan, $kgReal))->upsert();
            $cx->ejecutar($sql, $p);
            $filas = $cx->filas();
            return [
                'ok'   => true,
                'msg'  => 'Materia prima registrada/actualizada.',
                'filas'=> $filas
            ];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error en upsert de materia prima: ' . $e->getMessage()];
        } finally {
            $cx->cerrar();
        }
    }

    /**
     * Actualiza solo el kg_real
     */
    public static function actualizarKgReal(string $idOrden, string $tipo, string $codigo, float $kgReal): array {
        if ($idOrden === '' || $tipo === '' || $codigo === '') {
            return ['ok' => false, 'msg' => 'Parámetros obligatorios faltantes.'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new OrdenMateriaPrimaDAO($idOrden, $tipo, $codigo, null, null, $kgReal))->actualizarKgReal();
            $cx->ejecutar($sql, $p);
            $filas = $cx->filas();
            return [
                'ok'   => $filas > 0,
                'msg'  => $filas > 0 ? 'Kg real actualizado.' : 'No se actualizó ninguna fila.',
                'filas'=> $filas
            ];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al actualizar kg_real: ' . $e->getMessage()];
        } finally {
            $cx->cerrar();
        }
    }

    /**
     * Elimina un registro puntual
     */
    public static function eliminarUno(string $idOrden, string $tipo, string $codigo): array {
        if ($idOrden === '' || $tipo === '' || $codigo === '') {
            return ['ok' => false, 'msg' => 'Parámetros obligatorios faltantes.'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new OrdenMateriaPrimaDAO($idOrden, $tipo, $codigo))->eliminarUno();
            $cx->ejecutar($sql, $p);
            $filas = $cx->filas();
            return [
                'ok'   => $filas > 0,
                'msg'  => $filas > 0 ? 'Materia prima eliminada.' : 'No se eliminó ninguna materia prima.',
                'filas'=> $filas
            ];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al eliminar materia prima: ' . $e->getMessage()];
        } finally {
            $cx->cerrar();
        }
    }

    /**
     * Elimina todas las materias primas de una orden
     */
    public static function eliminarPorOrden(string $idOrden): array {
        if ($idOrden === '') {
            return ['ok' => false, 'msg' => 'Id de orden requerido.'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = OrdenMateriaPrimaDAO::eliminarPorOrden($idOrden);
            $cx->ejecutar($sql, $p);
            $filas = $cx->filas();
            return [
                'ok'   => true,
                'msg'  => 'Materias primas eliminadas de la orden.',
                'filas'=> $filas
            ];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al eliminar materias primas: ' . $e->getMessage()];
        } finally {
            $cx->cerrar();
        }
    }
}
