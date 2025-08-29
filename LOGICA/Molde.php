<?php
require_once __DIR__ . '/../persistencia/Conexion.php';
require_once __DIR__ . '/../persistencia/MoldeDAO.php';

class Molde
{
    /** Retorna todos los moldes activos */
    public static function listarDisponibles(): array {
        $cx = new Conexion();
        $dao = new MoldeDAO();

        $moldes = [];
        try {
            $cx->abrir();
            [$sql, $params] = $dao->listarDisponibles();
            $cx->ejecutar($sql, $params);

            while ($row = $cx->registro()) {
                $moldes[] = [
                    'id'        => $row[0],
                    'nombre'    => $row[1],
                    'colada_g'  => $row[2],
                ];
            }
        } catch (Throwable $e) {
            error_log("Error listarDisponibles: " . $e->getMessage());
        } finally {
            $cx->cerrar();
        }
        return $moldes;
    }

    /** Retorna todos los moldes (activos e inactivos) */
    public static function listarTodos(): array {
        $cx = new Conexion();
        $cx->abrir();

        $dao = new MoldeDAO();
        [$sql, $params] = $dao->listarTodos();
        $cx->ejecutar($sql, $params);

        $out = [];
        while ($r = $cx->registro()) {
            $out[] = [
                'id'       => $r[0],
                'nombre'   => $r[1],
                'colada_g' => $r[2],
                'estado'   => $r[3],
            ];
        }

        $cx->cerrar();
        return $out;
    }

    /** Consultar un molde específico */
    public static function consultar(string $idMolde): ?array {
        $cx = new Conexion();
        $cx->abrir();

        $dao = new MoldeDAO($idMolde);
        [$sql, $params] = $dao->consultar();
        $cx->ejecutar($sql, $params);

        $row = $cx->registro();
        $cx->cerrar();

        if (!$row) return null;

        return [
            'id'       => $row[0],
            'nombre'   => $row[1],
            'colada_g' => $row[2],
            'estado'   => $row[3],
        ];
    }
}
