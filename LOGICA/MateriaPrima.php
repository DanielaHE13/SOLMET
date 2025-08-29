<?php
// logica/MateriaPrima.php
require_once __DIR__ . '/../persistencia/Conexion.php';
require_once __DIR__ . '/../persistencia/MateriaPrimaDAO.php';

class MateriaPrima {
    private string $codigo;
    private string $polimero;
    private string $referencia;
    private string $estado;         // original | peletizado | molido
    private ?string $color;
    private ?string $procedencia;
    private ?string $uso_final;
    private int $activo;            // 1|0

    public function __construct(
        string $codigo = "",
        string $polimero = "",
        string $referencia = "",
        string $estado = "original",
        ?string $color = null,
        ?string $procedencia = null,
        ?string $uso_final = null,
        int $activo = 1
    ) {
        $this->codigo      = trim($codigo);
        $this->polimero    = trim($polimero);
        $this->referencia  = trim($referencia);
        $this->estado      = MateriaPrimaDAO::normalizarEstado($estado);
        $this->color       = $this->nn($color);
        $this->procedencia = $this->nn($procedencia);
        $this->uso_final   = $this->nn($uso_final);
        $this->activo      = $activo ? 1 : 0;
    }

    private function nn(?string $s): ?string {
        $s = $s!==null ? trim($s) : null;
        return ($s==='') ? null : $s;
    }

    /* ================== CREATE ================== */
    public function crear(): bool {
        $cx = new Conexion(); $cx->abrir();
        try {
            [$sql, $param] = MateriaPrimaDAO::crear([
                'codigo'     => $this->codigo,
                'polimero'   => $this->polimero,
                'referencia' => $this->referencia,
                'estado'     => $this->estado,
                'color'      => $this->color,
                'procedencia'=> $this->procedencia,
                'uso_final'  => $this->uso_final,
                'activo'     => $this->activo,
            ]);
            $cx->ejecutar($sql, $param);
            return true;
        } finally { $cx->cerrar(); }
    }

    /* =================== READ =================== */
    public static function obtenerPorCodigo(string $codigo): ?array {
        $cx = new Conexion(); $cx->abrir();
        try {
            [$sql, $param] = MateriaPrimaDAO::obtenerPorCodigo($codigo);
            $cx->ejecutar($sql, $param);
            $r = $cx->registro();
            return $r ?: null;
        } finally { $cx->cerrar(); }
    }

    public static function listarTodas(): array {
        $cx = new Conexion(); $cx->abrir();
        try {
            [$sql, $param] = MateriaPrimaDAO::listarTodas();
            $cx->ejecutar($sql, $param);
            $out=[]; while($r=$cx->registro()) $out[]=$r;
            return $out;
        } finally { $cx->cerrar(); }
    }

    public static function listarActivas(): array {
        $cx = new Conexion(); $cx->abrir();
        try {
            [$sql, $param] = MateriaPrimaDAO::listarActivas();
            $cx->ejecutar($sql, $param);
            $out=[]; while($r=$cx->registro()) $out[]=$r;
            return $out;
        } finally { $cx->cerrar(); }
    }

    /**
     * Buscar con filtros + paginación
     * $f = ['q'=>'', 'estado'=>'original|peletizado|molido', 'polimero'=>'PP', 'activo'=>1|0|'']
     */
    public static function buscar(array $f, int $limit=20, int $offset=0): array {
        $cx = new Conexion(); $cx->abrir();
        try {
            [$sql, $param] = MateriaPrimaDAO::listarConFiltros($f, $limit, $offset);
            $cx->ejecutar($sql, $param);
            $rows=[]; while($r=$cx->registro()) $rows[]=$r;
            return $rows;
        } finally { $cx->cerrar(); }
    }

    public static function contar(array $f): int {
        $cx = new Conexion(); $cx->abrir();
        try {
            [$sql, $param] = MateriaPrimaDAO::contarConFiltros($f);
            $cx->ejecutar($sql, $param);
            $c = $cx->registro();
            return (int)($c['total'] ?? $c[0] ?? 0);
        } finally { $cx->cerrar(); }
    }

    public static function polimeros(): array {
        $cx = new Conexion(); $cx->abrir();
        try {
            [$sql, $param] = MateriaPrimaDAO::polimeros();
            $cx->ejecutar($sql, $param);
            $arr=[]; while($r=$cx->registro()) $arr[] = $r['polimero'];
            return $arr;
        } finally { $cx->cerrar(); }
    }

    /* ================== UPDATE ================== */
    public function actualizar(): bool {
        $cx = new Conexion(); $cx->abrir();
        try {
            [$sql, $param] = MateriaPrimaDAO::actualizar([
                'codigo'     => $this->codigo,
                'polimero'   => $this->polimero,
                'referencia' => $this->referencia,
                'estado'     => $this->estado,
                'color'      => $this->color,
                'procedencia'=> $this->procedencia,
                'uso_final'  => $this->uso_final,
                'activo'     => $this->activo,
            ]);
            $cx->ejecutar($sql, $param);
            return true;
        } finally { $cx->cerrar(); }
    }

    /* ================== DELETE (lógico) ================== */
    public static function desactivar(string $codigo): bool {
        $cx = new Conexion(); $cx->abrir();
        try {
            [$sql, $param] = MateriaPrimaDAO::desactivar($codigo);
            $cx->ejecutar($sql, $param);
            return true;
        } finally { $cx->cerrar(); }
    }

    public static function reactivar(string $codigo): bool {
        $cx = new Conexion(); $cx->abrir();
        try {
            [$sql, $param] = MateriaPrimaDAO::reactivar($codigo);
            $cx->ejecutar($sql, $param);
            return true;
        } finally { $cx->cerrar(); }
    }

    public static function toggleActivo(string $codigo): bool {
        $cx = new Conexion(); $cx->abrir();
        try {
            [$sql, $param] = MateriaPrimaDAO::toggleActivo($codigo);
            $cx->ejecutar($sql, $param);
            return true;
        } finally { $cx->cerrar(); }
    }

    /* ================== DELETE (físico opcional) ================== */
    public static function eliminarFisico(string $codigo): bool {
        $cx = new Conexion(); $cx->abrir();
        try {
            [$sql, $param] = MateriaPrimaDAO::eliminarFisico($codigo);
            $cx->ejecutar($sql, $param);
            return true;
        } finally { $cx->cerrar(); }
    }
}
