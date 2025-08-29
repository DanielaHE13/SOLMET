<?php
declare(strict_types=1);

require_once __DIR__ . '/../persistencia/Conexion.php';
require_once __DIR__ . '/../persistencia/MaquinaDAO.php';

/**
 * ============================================================
 *  Capa LÓGICA: Maquina
 *  - Orquesta llamadas al DAO y normaliza resultados
 *  - Cierra la conexión SIEMPRE (try/finally)
 *  - Devuelve arreglos asociativos consistentes
 *
 *  Convenciones de retorno:
 *   - consultarPorId():      ?array {id_maquina, nombre, estado}
 *   - listarTodas():         array<array{id_maquina, nombre, estado}>
 *   - listarActivas():       array<array{id_maquina, nombre, estado}>
 *   - listarCompatibles():   array<array{id, nombre}>   (para <select>)
 * ============================================================
 */
class Maquina
{
    private string $id_maquina;
    private string $nombre;
    private string $estado; // 'activa' | 'inactiva'

    public function __construct(
        string $id_maquina = '',
        string $nombre     = '',
        string $estado     = 'activa'
    ) {
        $this->id_maquina = $id_maquina;
        $this->nombre     = $nombre;
        $this->estado     = $estado;
    }

    /* =========================================================
       Crear máquina
       Usa DAO->crear() y retorna true si afectó filas
       ========================================================= */
    public function crear(): bool {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $params] = (new MaquinaDAO(
                $this->id_maquina,
                $this->nombre,
                $this->estado
            ))->crear();
            $cx->ejecutar($sql, $params);
            // Para INSERT/UPDATE rowCount() sí es confiable en PDO MySQL
            return $cx->filas() > 0;
        } finally {
            $cx->cerrar();
        }
    }

    /* =========================================================
       Consultar máquina por ID
       Devuelve null si no existe. Normaliza claves.
       ========================================================= */
    public function consultarPorId(): ?array {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $params] = (new MaquinaDAO($this->id_maquina))->consultarPorId();
            $cx->ejecutar($sql, $params);
            $row = $cx->registro();
            if (!$row) return null;

            return [
                'id_maquina' => $row['id_maquina'] ?? $row[0] ?? '',
                'nombre'     => $row['nombre']     ?? $row[1] ?? '',
                'estado'     => $row['estado']     ?? $row[2] ?? '',
            ];
        } finally {
            $cx->cerrar();
        }
    }

    /* =========================================================
       Listar TODAS las máquinas
       ========================================================= */
    public static function listarTodas(): array {
        $cx = new Conexion();
        $out = [];
        try {
            $cx->abrir();
            // Se asume MaquinaDAO::listarTodas() => [SQL, params]
            [$sql, $params] = MaquinaDAO::listarTodas();
            $cx->ejecutar($sql, $params);
            while ($row = $cx->registro()) {
                $out[] = [
                    'id_maquina' => $row['id_maquina'] ?? $row[0] ?? '',
                    'nombre'     => $row['nombre']     ?? $row[1] ?? '',
                    'estado'     => $row['estado']     ?? $row[2] ?? '',
                ];
            }
            return $out;
        } finally {
            $cx->cerrar();
        }
    }

    /* =========================================================
       Listar máquinas ACTIVAS
       ========================================================= */
    public static function listarActivas(): array {
        $cx = new Conexion();
        $out = [];
        try {
            $cx->abrir();
            // Se asume MaquinaDAO::listarActivas() => [SQL, params]
            [$sql, $params] = MaquinaDAO::listarActivas();
            $cx->ejecutar($sql, $params);
            while ($row = $cx->registro()) {
                $out[] = [
                    'id_maquina' => $row['id_maquina'] ?? $row[0] ?? '',
                    'nombre'     => $row['nombre']     ?? $row[1] ?? '',
                    'estado'     => $row['estado']     ?? $row[2] ?? '',
                ];
            }
            return $out;
        } finally {
            $cx->cerrar();
        }
    }

    /* =========================================================
       Actualizar máquina
       ========================================================= */
    public function actualizar(): bool {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $params] = (new MaquinaDAO(
                $this->id_maquina,
                $this->nombre,
                $this->estado
            ))->actualizar();
            $cx->ejecutar($sql, $params);
            return $cx->filas() > 0;
        } finally {
            $cx->cerrar();
        }
    }

    /* =========================================================
       Eliminar (lógico): inactivar máquina
       ========================================================= */
    public function eliminar(): bool {
        $cx = new Conexion();
        try {
            $cx->abrir();
            [$sql, $params] = (new MaquinaDAO($this->id_maquina))->eliminar();
            $cx->ejecutar($sql, $params);
            return $cx->filas() > 0;
        } finally {
            $cx->cerrar();
        }
    }

    /* =========================================================
       Listar máquinas COMPATIBLES con un molde
       - Usa DAO->listarCompatibles($idMolde)
       - Devuelve arreglo para poblar <select>:
         [ ['id' => 'ISPH9502', 'nombre' => 'SAPHIR 95'], ... ]
       ========================================================= */
    public static function listarCompatibles(string $idMolde): array {
        $cx   = new Conexion();
        $dao  = new MaquinaDAO();
        $list = [];

        try {
            $cx->abrir();
            [$sql, $params] = $dao->listarCompatibles($idMolde);
            $cx->ejecutar($sql, $params);

            while ($row = $cx->registro()) {
                // La consulta del DAO es: SELECT m.id_maquina, m.nombre ...
                $list[] = [
                    'id'     => $row['id_maquina'] ?? $row['id'] ?? $row[0] ?? '',
                    'nombre' => $row['nombre']     ?? $row[1] ?? '',
                ];
            }
            return $list;
        } catch (Throwable $e) {
            // Log interno; la API de presentación manejará el error al serializar JSON
            error_log('Maquina::listarCompatibles error: ' . $e->getMessage());
            return [];
        } finally {
            $cx->cerrar();
        }
    }

    /* =========================================================
       (Atajo opcional) Alias semántico
       ========================================================= */
    public static function listarPorMolde(string $idMolde): array {
        return self::listarCompatibles($idMolde);
    }
}
