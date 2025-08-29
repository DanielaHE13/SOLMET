<?php
require_once __DIR__ . '/../persistencia/Conexion.php';
require_once __DIR__ . '/../persistencia/InsertoDAO.php';

class Inserto {
    private string $id_inserto;
    private string $id_molde;
    private string $descripcion;
    private int $activo;

    public function __construct(
        string $id_inserto = "",
        string $id_molde = "",
        string $descripcion = "",
        int $activo = 1
    ) {
        $this->id_inserto  = $id_inserto;
        $this->id_molde    = $id_molde;
        $this->descripcion = $descripcion;
        $this->activo      = $activo ? 1 : 0;
    }

    /* ==============================
       Crear un inserto
    ============================== */
    public function crear(): bool {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $param] = (new InsertoDAO(
                $this->id_inserto,
                $this->id_molde,
                $this->descripcion,
                $this->activo
            ))->crear();
            $cx->ejecutar($sql, $param);
            $cx->cerrar();
            return true;
        } catch (\Throwable $e) {
            $cx->cerrar();
            throw $e;
        }
    }

    /* ==============================
       Consultar inserto por ID
    ============================== */
    public function consultarPorId(): ?array {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $param] = (new InsertoDAO($this->id_inserto))->consultarPorId();
            $cx->ejecutar($sql, $param);
            $row = $cx->registro();
            $cx->cerrar();
            return $row ?: null;
        } catch (\Throwable $e) {
            $cx->cerrar();
            throw $e;
        }
    }

    /* ==============================
       Listar insertos activos
    ============================== */
    public static function listarActivos(): array {
        $cx = new Conexion();
        $resultado = [];
        try {
            $cx->abrir();
            [$sql, $param] = InsertoDAO::listarActivos();
            $cx->ejecutar($sql, $param);
            while ($row = $cx->registro()) {
                $resultado[] = $row;
            }
            $cx->cerrar();
            return $resultado;
        } catch (\Throwable $e) {
            $cx->cerrar();
            throw $e;
        }
    }

    /* ==============================
       Actualizar un inserto
    ============================== */
    public function actualizar(): bool {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $param] = (new InsertoDAO(
                $this->id_inserto,
                $this->id_molde,
                $this->descripcion,
                $this->activo
            ))->actualizar();
            $cx->ejecutar($sql, $param);
            $cx->cerrar();
            return true;
        } catch (\Throwable $e) {
            $cx->cerrar();
            throw $e;
        }
    }

    /* ==============================
       Eliminar (lógico)
    ============================== */
    public function eliminarLogico(): bool {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $param] = (new InsertoDAO($this->id_inserto))->eliminarLogico();
            $cx->ejecutar($sql, $param);
            $cx->cerrar();
            return true;
        } catch (\Throwable $e) {
            $cx->cerrar();
            throw $e;
        }
    }

    /* ==============================
       Eliminar (físico)
    ============================== */
    public function eliminarFisico(): bool {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $param] = (new InsertoDAO($this->id_inserto))->eliminarFisico();
            $cx->ejecutar($sql, $param);
            $cx->cerrar();
            return true;
        } catch (\Throwable $e) {
            $cx->cerrar();
            throw $e;
        }
    }

    /* ==============================
       Activar inserto
    ============================== */
    public function activar(): bool {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $param] = (new InsertoDAO($this->id_inserto))->activar();
            $cx->ejecutar($sql, $param);
            $cx->cerrar();
            return true;
        } catch (\Throwable $e) {
            $cx->cerrar();
            throw $e;
        }
    }
}
