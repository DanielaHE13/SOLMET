<?php
require_once __DIR__ . '/../persistencia/Conexion.php';
require_once __DIR__ . '/../persistencia/OrdenMetricasDAO.php';

class OrdenMetricas
{
    /* =========================================================
     * ===============     CÁLCULOS DE MÉTRICAS     =============
     * ========================================================= */

    /**
     * Calcula la fecha fin estimada a partir de un inicio y los ítems.
     * @param string $fechaInicioProg Fecha y hora de inicio (Y-m-d H:i:s).
     * @param array $items Arreglo de items [['cantidad'=>int,'cpm'=>float], ...].
     * @return string Fecha estimada de fin en formato Y-m-d H:i:s.
     */
    public static function calcularFinEstimada(string $fechaInicioProg, array $items): string {
        $totalMin = 0.0;
        foreach ($items as $it) {
            $cpm      = (float)($it['cpm']      ?? 0.0);
            $cantidad = (float)($it['cantidad'] ?? 0.0);
            if ($cpm > 0) {
                $totalMin += $cantidad / $cpm;
            }
        }
        $inicio = new DateTime($fechaInicioProg);
        $fin    = (clone $inicio)->modify('+' . ceil($totalMin) . ' minutes');
        return $fin->format('Y-m-d H:i:s');
    }

    /**
     * Calcula peso teórico total en kg.
     * @param array $items [['cantidad'=>int,'peso_g'=>float], ...]
     * @return float
     */
    public static function pesoTeoricoTotalKg(array $items): float {
        $g = 0.0;
        foreach ($items as $it) {
            $g += ((float)($it['cantidad'] ?? 0)) * ((float)($it['peso_g'] ?? 0.0));
        }
        return round($g / 1000.0, 3);
    }

    /**
     * Calcula devolución teórica (placeholder).
     * @param float $colada_g Peso de colada en gramos.
     * @param array $items
     * @return float
     */
    public static function devolucionTeoricaKg(float $colada_g, array $items): float {
        // Aquí puedes implementar tu propia regla según cavidades/tiros.
        return 0.0;
    }

    /* =========================================================
     * ===============        ACCESO A DATOS       =============
     * ========================================================= */

    /**
     * Consulta métricas de una orden.
     * @param string $idOp
     * @return ?array null si no existen
     */
    public static function consultar(string $idOp): ?array {
        if ($idOp === '') return null;

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new OrdenMetricasDAO($idOp))->consultar();
            $cx->ejecutar($sql, $p);
            $r = $cx->registro(); // [pt, pr, dt, dr]

            if (!$r) return null;

            return [
                'peso_teorico_total_kg' => $r[0] !== null ? (float)$r[0] : null,
                'peso_real_total_kg'    => $r[1] !== null ? (float)$r[1] : null,
                'devolucion_teorica_kg' => $r[2] !== null ? (float)$r[2] : null,
                'devolucion_real_kg'    => $r[3] !== null ? (float)$r[3] : null,
            ];
        } finally {
            $cx->cerrar();
        }
    }

    /**
     * Crea/actualiza métricas (UPSERT).
     */
    public static function upsert(
        string $idOp,
        ?float $pesoTeoricoTotalKg,
        ?float $pesoRealTotalKg,
        ?float $devolucionTeoricaKg,
        ?float $devolucionRealKg
    ): array {
        if ($idOp === '') {
            return ['ok' => false, 'msg' => 'Id de orden requerido'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new OrdenMetricasDAO(
                $idOp,
                $pesoTeoricoTotalKg,
                $pesoRealTotalKg,
                $devolucionTeoricaKg,
                $devolucionRealKg
            ))->upsert();

            $cx->ejecutar($sql, $p);
            return ['ok' => true, 'msg' => 'Métricas registradas/actualizadas'];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al registrar métricas' . (ini_get('display_errors') ? ': ' . $e->getMessage() : '')];
        } finally {
            $cx->cerrar();
        }
    }

    /**
     * Elimina métricas de una orden.
     */
    public static function eliminarPorOrden(string $idOp): array {
        if ($idOp === '') {
            return ['ok' => false, 'msg' => 'Id de orden requerido'];
        }

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new OrdenMetricasDAO($idOp))->eliminarPorOrden();
            $cx->ejecutar($sql, $p);
            return ['ok' => true, 'msg' => 'Métricas eliminadas'];
        } catch (Throwable $e) {
            return ['ok' => false, 'msg' => 'Error al eliminar métricas' . (ini_get('display_errors') ? ': ' . $e->getMessage() : '')];
        } finally {
            $cx->cerrar();
        }
    }

    /**
     * Verifica si existen métricas para la orden.
     */
    public static function existe(string $idOp): bool {
        if ($idOp === '') return false;

        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $p] = (new OrdenMetricasDAO($idOp))->existe();
            $cx->ejecutar($sql, $p);
            return (bool)$cx->registro();
        } finally {
            $cx->cerrar();
        }
    }

    /**
     * Calcula métricas teóricas a partir de items y colada, y las guarda con UPSERT.
     * @param string $idOp
     * @param array $items [['cantidad'=>int,'peso_g'=>float,'cpm'=>float], ...]
     * @param ?float $colada_g
     * @param ?float $pesoRealTotalKg
     * @param ?float $devolucionRealKg
     */
    public static function calcularYUpsert(
        string $idOp,
        array $items,
        ?float $colada_g = null,
        ?float $pesoRealTotalKg = null,
        ?float $devolucionRealKg = null
    ): array {
        if ($idOp === '') {
            return ['ok' => false, 'msg' => 'Id de orden requerido'];
        }

        $pt = self::pesoTeoricoTotalKg($items);
        $dt = self::devolucionTeoricaKg((float)($colada_g ?? 0.0), $items);

        return self::upsert($idOp, $pt, $pesoRealTotalKg, $dt, $devolucionRealKg);
    }
}
