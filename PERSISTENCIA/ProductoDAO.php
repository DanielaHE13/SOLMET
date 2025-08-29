<?php
class ProductoDAO
{
    private string $id;
    private string $nombre;
    private ?float $peso; // peso_teorico_g
    private ?float $cpm;

    public function __construct(string $id = "", string $nombre = "", ?float $peso = null, ?float $cpm = null)
    {
        $this->id     = $id;
        $this->nombre = $nombre;
        $this->peso   = $peso;
        $this->cpm    = $cpm;
    }

    /** SELECT por id */
    public function consultarPorId(): array
    {
        $sql = "SELECT id_producto, nombre, peso_teorico_g, ciclos_por_min
                  FROM producto
                 WHERE id_producto = ?";
        return [$sql, [$this->id]];
    }

    /** Listado con filtro opcional por nombre o id */
    public function listar(string $q = ""): array
    {
        $sql = "SELECT id_producto, nombre, peso_teorico_g, ciclos_por_min
                  FROM producto
                 WHERE (? = '' OR nombre LIKE CONCAT('%', ?, '%') OR id_producto LIKE CONCAT('%', ?, '%'))
              ORDER BY nombre ASC";
        return [$sql, [$q, $q, $q]];
    }

    /** INSERT */
    public function crear(): array
    {
        $sql = "INSERT INTO producto (id_producto, nombre, peso_teorico_g, ciclos_por_min)
                VALUES (?, ?, ?, ?)";
        return [$sql, [$this->id, $this->nombre, $this->peso, $this->cpm]];
    }

    /** UPDATE */
    public function actualizar(): array
    {
        $sql = "UPDATE producto
                   SET nombre = ?, peso_teorico_g = ?, ciclos_por_min = ?
                 WHERE id_producto = ?";
        return [$sql, [$this->nombre, $this->peso, $this->cpm, $this->id]];
    }

    /** DELETE */
    public function eliminar(): array
    {
        $sql = "DELETE FROM producto WHERE id_producto = ?";
        return [$sql, [$this->id]];
    }

    /** Productos compatibles con un molde */
    public function listarCompatiblesPorMolde(string $idMolde): array
    {
        $sql = "SELECT p.id_producto,
                       p.nombre,
                       p.peso_teorico_g AS peso_gramos,
                       p.ciclos_por_min AS cpm
                  FROM producto p
                  INNER JOIN molde_producto mp ON mp.id_producto = p.id_producto
                 WHERE mp.id_molde = ?
              ORDER BY p.nombre ASC";
        return [$sql, [$idMolde]];
    }

    /** Verifica existencia de id_producto */
    public function existeId(): array
    {
        $sql = "SELECT 1 FROM producto WHERE id_producto = ? LIMIT 1";
        return [$sql, [$this->id]];
    }

    /** Búsqueda por nombre con paginación */
    public function buscarPorNombre(string $q = "", int $limit = 50, int $offset = 0): array
    {
        $limit  = min($limit, 100);
        $offset = max($offset, 0);
        $sql = "SELECT id_producto, nombre, peso_teorico_g, ciclos_por_min
                  FROM producto
                 WHERE (? = '' OR nombre LIKE CONCAT('%', ?, '%'))
              ORDER BY nombre ASC
                 LIMIT ? OFFSET ?";
        return [$sql, [$q, $q, $limit, $offset]];
    }
}
