<?php
require_once __DIR__ . '/Conexion.php';

class InsertoDAO
{
    private string $id_inserto;
    private string $id_molde;
    private string $descripcion;
    private int $activo;

    public function __construct(
        string $id_inserto = '',
        string $id_molde = '',
        string $descripcion = '',
        int $activo = 1
    ) {
        $this->id_inserto  = $id_inserto;
        $this->id_molde    = $id_molde;
        $this->descripcion = $descripcion;
        $this->activo      = $activo ? 1 : 0;
    }

    /* ========================= CRUD ========================= */

    public function crear(): array
    {
        $sql = "INSERT INTO inserto (id_inserto,id_molde,descripcion,activo,created_at)
                VALUES (:id_inserto,:id_molde,:descripcion,:activo,NOW())";
        return [$sql, [
            ':id_inserto'  => $this->id_inserto,
            ':id_molde'    => $this->id_molde,
            ':descripcion' => $this->descripcion,
            ':activo'      => $this->activo
        ]];
    }

    public function actualizar(): array
    {
        $sql = "UPDATE inserto
                   SET id_molde=:id_molde,
                       descripcion=:descripcion,
                       activo=:activo,
                       updated_at=NOW()
                 WHERE id_inserto=:id_inserto";
        return [$sql, [
            ':id_molde'    => $this->id_molde,
            ':descripcion' => $this->descripcion,
            ':activo'      => $this->activo,
            ':id_inserto'  => $this->id_inserto
        ]];
    }

    public function eliminarLogico(): array
    {
        $sql = "UPDATE inserto
                   SET activo = 0, updated_at = NOW()
                 WHERE id_inserto = :id";
        return [$sql, [':id' => $this->id_inserto]];
    }

    public function eliminarFisico(): array
    {
        $sql = "DELETE FROM inserto WHERE id_inserto = :id";
        return [$sql, [':id' => $this->id_inserto]];
    }

    /* ========================= QUERIES ========================= */

    public function consultarPorId(): array
    {
        $sql = "SELECT i.id_inserto, i.id_molde, i.descripcion, i.activo,
                       i.created_at, i.updated_at,
                       m.nombre AS molde_nombre
                  FROM inserto i
             LEFT JOIN molde m ON m.id_molde=i.id_molde
                 WHERE i.id_inserto=:id LIMIT 1";
        return [$sql, [':id' => $this->id_inserto]];
    }

    public static function listar(string $q = '', string $estado = ''): array
    {
        $w = [];
        $p = [];
        if ($q !== '') {
            $w[] = "(i.id_inserto LIKE :q OR i.descripcion LIKE :q OR m.nombre LIKE :q)";
            $p[':q'] = "%$q%";
        }
        if ($estado !== '') {
            $w[] = $estado === 'activos' ? "i.activo=1" : "i.activo=0";
        }
        $where = $w ? ('WHERE ' . implode(' AND ', $w)) : '';
        $sql = "SELECT i.id_inserto, i.id_molde, i.descripcion, i.activo, i.created_at, i.updated_at,
                       m.nombre AS molde_nombre
                  FROM inserto i
             LEFT JOIN molde m ON m.id_molde=i.id_molde
                $where
              ORDER BY m.nombre ASC, i.id_inserto ASC";
        return [$sql, $p];
    }

    public static function existeId(string $id): array
    {
        $sql = "SELECT COUNT(*) c FROM inserto WHERE id_inserto=:id";
        return [$sql, [':id' => $id]];
    }

    public static function listarActivos(): array
    {
        $sql = "SELECT i.id_inserto, i.descripcion, m.nombre AS molde_nombre
                  FROM inserto i
             LEFT JOIN molde m ON m.id_molde=i.id_molde
                 WHERE i.activo=1
              ORDER BY m.nombre ASC, i.id_inserto ASC";
        return [$sql, []];
    }

    public function activar(): array
    {
        $sql = "UPDATE inserto
                   SET activo = 1, updated_at = NOW()
                 WHERE id_inserto=:id";
        return [$sql, [':id' => $this->id_inserto]];
    }
}
