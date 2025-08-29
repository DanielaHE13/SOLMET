<?php
/**
 * Conexion PDO para MySQL/MariaDB
 * - Prepared statements
 * - utf8mb4
 * - ERRMODE_EXCEPTION
 * - emulate prepares OFF
 * - fetch BOTH (índices y asociativo)
 *
 * Ajusta credenciales abajo o usa variables de entorno:
 *   DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS
 */
class Conexion {
    private ?PDO $pdo  = null;
    private ?PDOStatement $stmt = null;

    // Ajusta estos defaults si no usas variables de entorno
    private string $host = '';
    private string $port = '';
    private string $db   = '';
    private string $user = '';
    private string $pass = '';

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: '127.0.0.1';
        $this->port = getenv('DB_PORT') ?: '3306';
        $this->db   = getenv('DB_NAME') ?: 'solmet';
        $this->user = getenv('DB_USER') ?: 'root';
        $this->pass = getenv('DB_PASS') ?: '';
    }

    public function abrir(): void {
        if ($this->pdo instanceof PDO) return;

        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset=utf8mb4";

        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH,     // BOTH => índices y asociativo
            PDO::ATTR_EMULATE_PREPARES   => false,               // prepared reales
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci",
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,          // buffer resultados
        ];

        $this->pdo = new PDO($dsn, $this->user, $this->pass, $opts);
    }

    /**
     * Ejecuta una consulta preparada
     * @param string $sql
     * @param array  $params  (posicionales o nombrados)
     */
    public function ejecutar(string $sql, array $params = []): void {
        if (!$this->pdo) $this->abrir();
        $this->stmt = $this->pdo->prepare($sql);
        $this->stmt->execute($params);
    }

    /** Devuelve el siguiente registro del statement actual (o false si no hay más) */
    public function registro(): array|false {
        return $this->stmt ? $this->stmt->fetch() : false;
    }

    /** Devuelve todos los registros restantes como array */
    public function registros(): array {
        return $this->stmt ? ($this->stmt->fetchAll() ?: []) : [];
    }

    /**
     * Cantidad de filas afectadas (OJO: en SELECT con MySQL no siempre es confiable).
     * Úsalo para INSERT/UPDATE/DELETE. Para SELECT, mejor usa registro()/registros().
     */
    public function filas(): int {
        return $this->stmt ? $this->stmt->rowCount() : 0;
    }

    /** Último ID autoincrement generado (si aplica) */
    public function ultimoId(): string {
        return $this->pdo ? $this->pdo->lastInsertId() : '0';
    }

    /** Manejo de transacciones */
    public function begin(): void   { if ($this->pdo && !$this->pdo->inTransaction()) $this->pdo->beginTransaction(); }
    public function commit(): void  { if ($this->pdo &&  $this->pdo->inTransaction()) $this->pdo->commit(); }
    public function rollback(): void{ if ($this->pdo &&  $this->pdo->inTransaction()) $this->pdo->rollBack(); }

    /** Cierra statement y conexión */
    public function cerrar(): void {
        $this->stmt = null;
        $this->pdo  = null;
    }

    /** (Opcional) Exponer PDO si lo necesitas para algo específico */
    public function pdo(): ?PDO {
        return $this->pdo;
    }
}
