<?php
require_once __DIR__ . '/../persistencia/Conexion.php';
require_once __DIR__ . '/../persistencia/OrdenProduccionDAO.php';

class OrdenProduccion
{
    private string $idOp;
    private ?string $idMolde;
    private ?string $idMaquina;
    private ?string $fechaInicioProg;
    private ?string $fechaFinEstimada;
    private ?string $estado;

    public function __construct(
        string $idOp = '',
        ?string $idMolde = null,
        ?string $idMaquina = null,
        ?string $fechaInicioProg = null,
        ?string $fechaFinEstimada = null,
        ?string $estado = null
    ) {
        $this->idOp            = $idOp;
        $this->idMolde         = $idMolde;
        $this->idMaquina       = $idMaquina;
        $this->fechaInicioProg = $fechaInicioProg;
        $this->fechaFinEstimada = $fechaFinEstimada;
        $this->estado          = $estado;
    }

    /* =========================================================
     * ================== MÉTODOS CRUD =========================
     * ========================================================= */

    public function crear(): bool
    {
        $dao = new OrdenProduccionDAO(
            $this->idOp,
            $this->idMolde,
            $this->idMaquina,
            $this->fechaInicioProg,
            $this->fechaFinEstimada,
            $this->estado
        );
        [$sql, $params] = $dao->insertar();

        $cx = new Conexion();
        $cx->abrir();
        $ok = $cx->ejecutar($sql, $params);
        $cx->cerrar();

        return $ok;
    }

    public function actualizar(): bool
    {
        $dao = new OrdenProduccionDAO(
            $this->idOp,
            $this->idMolde,
            $this->idMaquina,
            $this->fechaInicioProg,
            $this->fechaFinEstimada,
            $this->estado
        );
        [$sql, $params] = $dao->actualizar();

        $cx = new Conexion();
        $cx->abrir();
        $ok = $cx->ejecutar($sql, $params);
        $cx->cerrar();

        return $ok;
    }

    public function eliminar(): bool
    {
        $dao = new OrdenProduccionDAO($this->idOp);
        [$sql, $params] = $dao->eliminar();

        $cx = new Conexion();
        $cx->abrir();
        $ok = $cx->ejecutar($sql, $params);
        $cx->cerrar();

        return $ok;
    }

    public function consultar(): ?array
    {
        $dao = new OrdenProduccionDAO($this->idOp);
        [$sql, $params] = $dao->consultar();

        $cx = new Conexion();
        $cx->abrir();
        $cx->ejecutar($sql, $params);
        $row = $cx->registro();
        $cx->cerrar();

        return $row ?: null;
    }

    public static function listarTodos(): array
    {
        $dao = new OrdenProduccionDAO();
        [$sql, $params] = $dao->listarTodos();

        $cx = new Conexion();
        $cx->abrir();
        $cx->ejecutar($sql, $params);

        $rows = [];
        while ($r = $cx->registro()) {
            $rows[] = $r;
        }
        $cx->cerrar();

        return $rows;
    }

    public static function listarPorEstado(string $estado): array
    {
        $dao = new OrdenProduccionDAO('', null, null, null, null, $estado);
        [$sql, $params] = $dao->listarPorEstado();

        $cx = new Conexion();
        $cx->abrir();
        $cx->ejecutar($sql, $params);

        $rows = [];
        while ($r = $cx->registro()) {
            $rows[] = $r;
        }
        $cx->cerrar();

        return $rows;
    }

    public static function maxId(): int
    {
        $dao = new OrdenProduccionDAO();
        [$sql, $params] = $dao->maxId();

        $cx = new Conexion();
        $cx->abrir();
        $cx->ejecutar($sql, $params);
        $row = $cx->registro();
        $cx->cerrar();

        return (int)($row['max_id'] ?? 0);
    }

    public static function listarPorRangoFechas(string $desde, string $hasta): array
    {
        $dao = new OrdenProduccionDAO();
        [$sql, $params] = $dao->listarPorRangoFechas($desde, $hasta);

        $cx = new Conexion();
        $cx->abrir();
        $cx->ejecutar($sql, $params);

        $rows = [];
        while ($r = $cx->registro()) {
            $rows[] = $r;
        }
        $cx->cerrar();

        return $rows;
    }

    public static function listarDetallado(): array
    {
        $dao = new OrdenProduccionDAO();
        [$sql, $params] = $dao->listarDetallado();

        $cx = new Conexion();
        $cx->abrir();
        $cx->ejecutar($sql, $params);

        $rows = [];
        while ($r = $cx->registro()) {
            $rows[] = $r;
        }
        $cx->cerrar();

        return $rows;
    }
    public static function maxNumero(): array
    {
        $dao = new OrdenProduccionDAO();
        [$sql, $params] = $dao->maxId();

        $cx = new Conexion();
        $cx->abrir();
        $cx->ejecutar($sql, $params);
        $row = $cx->registro();
        $cx->cerrar();

        $max = (int)($row['max_id'] ?? 0);
        return [
            'max'  => $max,
            'next' => $max + 1
        ];
    }
}
