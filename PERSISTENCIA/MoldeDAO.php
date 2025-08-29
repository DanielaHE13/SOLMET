<?php

/**
 * DAO para la tabla molde
 */
class MoldeDAO
{
    private ?string $idMolde;
    private ?string $nombre;
    private ?float $coladaG;
    private ?string $estado;

    public function __construct(
        ?string $idMolde = null,
        ?string $nombre = null,
        ?float $coladaG = null,
        ?string $estado = null
    ) {
        $this->idMolde = $idMolde;
        $this->nombre  = $nombre;
        $this->coladaG = $coladaG;
        $this->estado  = $estado;
    }

    /** Listar moldes activos */
    public function listarDisponibles(): array
    {
        $sql = "SELECT id_molde, nombre, peso_colada_g
                  FROM molde
                 WHERE estado = 'disponible'
              ORDER BY nombre ASC";
        return [$sql, []];
    }


    /** Consultar un molde por id */
    public function consultar(): array
    {
        $sql = "SELECT id_molde, nombre, colada_g, estado
                  FROM molde
                 WHERE id_molde = ?";
        return [$sql, [$this->idMolde]];
    }

    /** Listar todos los moldes */
    public function listarTodos(): array
    {
        $sql = "SELECT id_molde, nombre, colada_g, estado
                  FROM molde
              ORDER BY nombre ASC";
        return [$sql, []];
    }
}
