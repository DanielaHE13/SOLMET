<?php
class RolDAO
{
    private string $id;
    private string $nombre;

    public function __construct(string $id = "", string $nombre = "")
    {
        $this->id     = $id;
        $this->nombre = $nombre;
    }

    /** Listar todos los roles */
    public function listarTodos(): array
    {
        $sql = "SELECT id_rol, nombre
                  FROM rol
              ORDER BY nombre ASC";
        return [$sql, []];
    }

    /** Consultar un rol por id */
    public function consultarPorId(): array
    {
        $sql = "SELECT id_rol, nombre
                  FROM rol
                 WHERE id_rol = ?";
        return [$sql, [$this->id]];
    }

    /** Crear rol */
    public function crear(): array
    {
        $sql = "INSERT INTO rol (id_rol, nombre)
                VALUES (?, ?)";
        return [$sql, [$this->id, $this->nombre]];
    }

    /** Actualizar rol */
    public function actualizar(): array
    {
        $sql = "UPDATE rol
                   SET nombre = ?
                 WHERE id_rol = ?";
        return [$sql, [$this->nombre, $this->id]];
    }

    /** Eliminar rol */
    public function eliminar(): array
    {
        $sql = "DELETE FROM rol WHERE id_rol = ?";
        return [$sql, [$this->id]];
    }

    /** Verificar si existe un rol */
    public function existeId(): array
    {
        $sql = "SELECT 1 FROM rol WHERE id_rol = ? LIMIT 1";
        return [$sql, [$this->id]];
    }
}
