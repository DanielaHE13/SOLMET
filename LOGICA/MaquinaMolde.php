<?php
require_once __DIR__ . '/../persistencia/Conexion.php';
require_once __DIR__ . '/../persistencia/MaquinaMoldeDAO.php';

class MaquinaMolde {
    private string $id_molde;
    private string $id_maquina;

    public function __construct(string $id_molde = "", string $id_maquina = "") {
        $this->id_molde   = $id_molde;
        $this->id_maquina = $id_maquina;
    }

    /* ==============================
       Crear relación máquina ↔ molde
    ============================== */
    public function crear(): bool {
        $cx = new Conexion();
        $cx->abrir();
        [$sql, $param] = (new MaquinaMoldeDAO(
            $this->id_molde,
            $this->id_maquina
        ))->crear();
        $cx->ejecutar($sql, $param);
        $cx->cerrar();
        return true;
    }

    /* ==============================
       Listar todas las compatibles
    ============================== */
    public static function listarCompatibles(string $idMolde): array {
        $cx = new Conexion();
        $cx->abrir();
        [$sql, $param] = MaquinaMoldeDAO::listarCompatibles($idMolde);
        $cx->ejecutar($sql, $param);

        $resultado = [];
        while ($row = $cx->registro()) {
            $resultado[] = $row;
        }
        $cx->cerrar();
        return $resultado;
    }

    /* ==============================
       Listar solo activas
    ============================== */
    public static function listarCompatiblesActivas(string $idMolde): array {
        $cx = new Conexion();
        $cx->abrir();
        [$sql, $param] = MaquinaMoldeDAO::listarCompatiblesActivas($idMolde);
        $cx->ejecutar($sql, $param);

        $resultado = [];
        while ($row = $cx->registro()) {
            $resultado[] = $row;
        }
        $cx->cerrar();
        return $resultado;
    }

    /* ==============================
       Eliminar relación
    ============================== */
    public function eliminar(): bool {
        $cx = new Conexion();
        $cx->abrir();
        [$sql, $param] = (new MaquinaMoldeDAO(
            $this->id_molde,
            $this->id_maquina
        ))->eliminar();
        $cx->ejecutar($sql, $param);
        $cx->cerrar();
        return true;
    }
}
