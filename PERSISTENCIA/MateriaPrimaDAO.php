<?php
// persistencia/MateriaPrimaDAO.php
require_once __DIR__ . '/Conexion.php';

class MateriaPrimaDAO
{
    /* ============================================
       Enums y validaciones
    ============================================ */
    public static function estadosValidos(): array
    {
        return ['original', 'peletizado', 'molido'];
    }
    public static function normalizarEstado(?string $e): string
    {
        $e = strtolower(trim((string)$e));
        return in_array($e, self::estadosValidos(), true) ? $e : 'original';
    }

    /* ============================================
       Crear
    ============================================ */
    public static function crear(array $d): array
    {
        $sql = "INSERT INTO materia_prima
                   (codigo, polimero, referencia, estado, color, procedencia, uso_final, activo)
                VALUES (?,?,?,?,?,?,?,?)";
        $params = [
            (string)$d['codigo'],
            (string)$d['polimero'],
            (string)$d['referencia'],
            self::normalizarEstado($d['estado'] ?? 'original'),
            $d['color'] ?? null,
            $d['procedencia'] ?? null,
            $d['uso_final'] ?? null,
            isset($d['activo']) ? (int)$d['activo'] : 1,
        ];
        return [$sql, $params];
    }

    /* ============================================
       Obtener por código (detalle)
    ============================================ */
    public static function obtenerPorCodigo(string $codigo): array
    {
        $sql = "SELECT codigo, polimero, referencia, estado, color, procedencia, uso_final, activo,
                       created_at, updated_at
                  FROM materia_prima
                 WHERE codigo = ?";
        return [$sql, [$codigo]];
    }

    /* ============================================
       Existe (para validaciones de crear/editar)
    ============================================ */
    public static function existe(string $codigo): array
    {
        $sql = "SELECT 1 FROM materia_prima WHERE codigo = ? LIMIT 1";
        return [$sql, [$codigo]];
    }

    /* ============================================
       Listados “rápidos”
    ============================================ */
    public static function listarTodas(): array
    {
        $sql = "SELECT codigo, polimero, referencia, estado, color, procedencia, uso_final, activo,
                       DATE_FORMAT(created_at,'%Y-%m-%d') AS creado
                  FROM materia_prima
              ORDER BY codigo ASC";
        return [$sql, []];
    }

    public static function listarActivas(): array
    {
        $sql = "SELECT codigo, polimero, referencia, estado, color, procedencia, uso_final, activo,
                       DATE_FORMAT(created_at,'%Y-%m-%d') AS creado
                  FROM materia_prima
                 WHERE activo = 1
              ORDER BY codigo ASC";
        return [$sql, []];
    }

    /* ============================================
       Listado con filtros + paginación
       Filtros soportados: q (buscar), estado, polimero, activo
    ============================================ */
    public static function contarConFiltros(array $f): array
    {
        [$wSQL, $params] = self::buildWhere($f);
        $sql = "SELECT COUNT(*) AS total FROM materia_prima {$wSQL}";
        return [$sql, $params];
    }

    public static function listarConFiltros(array $f, int $limit, int $offset): array
    {
        [$wSQL, $params] = self::buildWhere($f);
        $sql = "SELECT codigo, polimero, referencia, estado, color, procedencia, uso_final, activo,
                       DATE_FORMAT(created_at,'%Y-%m-%d') AS creado
                  FROM materia_prima
                  {$wSQL}
              ORDER BY codigo ASC
                 LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        return [$sql, $params];
    }

    private static function buildWhere(array $f): array
    {
        $where  = [];
        $params = [];

        if (!empty($f['q'])) {
            $q = '%' . $f['q'] . '%';
            $where[] = "(codigo LIKE ? OR referencia LIKE ? OR polimero LIKE ? OR color LIKE ? OR uso_final LIKE ?)";
            array_push($params, $q, $q, $q, $q, $q);
        }
        if (isset($f['activo']) && $f['activo'] !== '') {
            $where[] = "activo = ?";
            $params[] = (int)$f['activo'];
        }
        if (!empty($f['estado']) && in_array($f['estado'], self::estadosValidos(), true)) {
            $where[] = "estado = ?";
            $params[] = $f['estado'];
        }
        if (!empty($f['polimero'])) {
            $where[] = "polimero = ?";
            $params[] = $f['polimero'];
        }

        $wSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        return [$wSQL, $params];
    }

    /* ============================================
       Actualizar
    ============================================ */
    public static function actualizar(
        string $codigo,
        string $polimero,
        string $referencia,
        string $estado,
        string $color = '',
        string $procedencia = '',
        string $uso_final = '',
        int $activo = 1
    ): array {
        $sql = "UPDATE materia_prima
               SET polimero=:polimero,
                   referencia=:referencia,
                   estado=:estado,
                   color=:color,
                   procedencia=:procedencia,
                   uso_final=:uso_final,
                   activo=:activo,
                   updated_at=NOW()
             WHERE codigo=:codigo";
        return [$sql, [
            ':codigo'     => $codigo,
            ':polimero'   => $polimero,
            ':referencia' => $referencia,
            ':estado'     => $estado,
            ':color'      => $color,
            ':procedencia' => $procedencia,
            ':uso_final'  => $uso_final,
            ':activo'     => $activo
        ]];
    }


    /* ============================================
       Cambiar estado / eliminar
    ============================================ */
    public static function desactivar(string $codigo): array
    {
        $sql = "UPDATE materia_prima SET activo = 0 WHERE codigo = ?";
        return [$sql, [$codigo]];
    }

    public static function reactivar(string $codigo): array
    {
        $sql = "UPDATE materia_prima SET activo = 1 WHERE codigo = ?";
        return [$sql, [$codigo]];
    }

    public static function toggleActivo(string $codigo): array
    {
        $sql = "UPDATE materia_prima
                   SET activo = CASE WHEN activo=1 THEN 0 ELSE 1 END
                 WHERE codigo = ?";
        return [$sql, [$codigo]];
    }

    public static function eliminarFisico(string $codigo): array
    {
        $sql = "DELETE FROM materia_prima WHERE codigo = ?";
        return [$sql, [$codigo]];
    }

    /* ============================================
       Extras útiles para UI/filtros
    ============================================ */
    // Distintos polímeros para combos de filtro
    public static function polimeros(): array
    {
        $sql = "SELECT DISTINCT polimero FROM materia_prima ORDER BY polimero ASC";
        return [$sql, []];
    }

    // Búsqueda rápida de códigos (autosuggest opcional)
    public static function buscarCodigos(string $q, int $limit = 10): array
    {
        $sql = "SELECT codigo
                  FROM materia_prima
                 WHERE codigo LIKE ?
              ORDER BY codigo ASC
                 LIMIT ?";
        return [$sql, ['%' . $q . '%', $limit]];
    }
}
