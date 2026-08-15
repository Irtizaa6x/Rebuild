<?php
/**
 * admin/db.php
 *
 * Database connection and abstraction layer for the IrtiJa admin panel.
 * Uses SQLite via PDO for simplicity and compatibility with InfinityFree.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- Prevent direct access ---
if (!defined('IRTIJA_ADMIN')) {
    die('Direct access to this file is not permitted.');
}

// --- Determine the database file path ---
// The database file is stored one level up from the admin directory
define('IRTIJA_DB_PATH', __DIR__ . '/../database/blog.db');
define('IRTIJA_DB_DIR', __DIR__ . '/../database');

/**
 * Database class — singleton pattern for a single PDO instance.
 */
class Database
{
    /**
     * @var Database|null Singleton instance
     */
    private static ?Database $instance = null;

    /**
     * @var PDO|null PDO connection instance
     */
    private ?PDO $pdo = null;

    /**
     * @var bool Whether the connection is in a transaction
     */
    private bool $inTransaction = false;

    /**
     * Private constructor — use getInstance() instead.
     */
    private function __construct()
    {
        $this->connect();
    }

    /**
     * Private clone — prevent cloning.
     */
    private function __clone()
    {
        // No cloning allowed
    }

    /**
     * Get the singleton instance of the Database class.
     *
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establish the database connection.
     *
     * @return void
     * @throws PDOException If the connection fails
     */
    private function connect(): void
    {
        // Ensure the database directory exists
        if (!is_dir(IRTIJA_DB_DIR)) {
            if (!mkdir(IRTIJA_DB_DIR, 0755, true)) {
                throw new RuntimeException('Unable to create database directory: ' . IRTIJA_DB_DIR);
            }
        }

        // SQLite connection string
        $dsn = 'sqlite:' . IRTIJA_DB_PATH;

        // PDO options for security and performance
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
            // The constant PDO::SQLITE_ATTR_OPEN_FLAGS may not be defined in all PHP versions.
            // If it exists, we can use it; otherwise, skip it.
        ];

        // Add the SQLite open flags only if the constant is defined
        if (defined('PDO::SQLITE_ATTR_OPEN_FLAGS')) {
            $options[PDO::SQLITE_ATTR_OPEN_FLAGS] = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE;
        }

        try {
            $this->pdo = new PDO($dsn, null, null, $options);

            // Enable foreign key constraints for data integrity
            $this->pdo->exec('PRAGMA foreign_keys = ON');

            // Enable WAL mode for better concurrency (readers don't block writers)
            $this->pdo->exec('PRAGMA journal_mode = WAL');

            // Set busy timeout to avoid "database is locked" errors
            $this->pdo->exec('PRAGMA busy_timeout = 5000');

        } catch (PDOException $e) {
            // Re-throw with a user-friendly message (but keep the original for logging)
            throw new PDOException('Database connection failed: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Get the underlying PDO instance.
     *
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Prepare and execute a query with bound parameters.
     *
     * @param string $sql    The SQL query with placeholders (:name or ?)
     * @param array  $params The parameters to bind
     *
     * @return PDOStatement The executed statement
     * @throws PDOException
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        if ($stmt === false) {
            throw new PDOException('Failed to prepare statement: ' . $sql);
        }

        foreach ($params as $key => $value) {
            // Determine the parameter type
            $type = PDO::PARAM_STR;
            if (is_int($value)) {
                $type = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $type = PDO::PARAM_BOOL;
            } elseif ($value === null) {
                $type = PDO::PARAM_NULL;
            }
            $stmt->bindValue(is_int($key) ? $key + 1 : $key, $value, $type);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Fetch a single row from a query.
     *
     * @param string $sql    The SQL query with placeholders
     * @param array  $params The parameters to bind
     *
     * @return array|false The row as an associative array, or false if no row
     * @throws PDOException
     */
    public function fetchOne(string $sql, array $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Fetch all rows from a query.
     *
     * @param string $sql    The SQL query with placeholders
     * @param array  $params The parameters to bind
     *
     * @return array Array of associative arrays
     * @throws PDOException
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch a single column value from a query.
     *
     * @param string $sql    The SQL query with placeholders
     * @param array  $params The parameters to bind
     *
     * @return mixed The column value, or false if no row
     * @throws PDOException
     */
    public function fetchColumn(string $sql, array $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }

    /**
     * Insert a row into a table.
     *
     * @param string $table  The table name
     * @param array  $data   Associative array of column => value
     *
     * @return int The last insert ID
     * @throws PDOException
     */
    public function insert(string $table, array $data): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Insert data cannot be empty.');
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->escapeIdentifier($table),
            implode(', ', $this->escapeIdentifiers($columns)),
            implode(', ', $placeholders)
        );

        $this->query($sql, $data);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Update rows in a table.
     *
     * @param string $table   The table name
     * @param array  $data    Associative array of column => value to set
     * @param string $where   The WHERE clause (without the word "WHERE")
     * @param array  $whereParams Parameters for the WHERE clause
     *
     * @return int The number of affected rows
     * @throws PDOException
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Update data cannot be empty.');
        }

        $set = [];
        $params = [];
        foreach ($data as $col => $val) {
            $paramKey = 'set_' . $col;
            $set[] = $this->escapeIdentifier($col) . ' = :' . $paramKey;
            $params[$paramKey] = $val;
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $this->escapeIdentifier($table),
            implode(', ', $set),
            $where
        );

        $stmt = $this->query($sql, array_merge($params, $whereParams));
        return $stmt->rowCount();
    }

    /**
     * Delete rows from a table.
     *
     * @param string $table   The table name
     * @param string $where   The WHERE clause (without the word "WHERE")
     * @param array  $params  Parameters for the WHERE clause
     *
     * @return int The number of affected rows
     * @throws PDOException
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $this->escapeIdentifier($table),
            $where
        );
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Begin a transaction.
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        if ($this->inTransaction) {
            return false;
        }
        $this->inTransaction = $this->pdo->beginTransaction();
        return $this->inTransaction;
    }

    /**
     * Commit a transaction.
     *
     * @return bool
     */
    public function commit(): bool
    {
        if (!$this->inTransaction) {
            return false;
        }
        $result = $this->pdo->commit();
        $this->inTransaction = false;
        return $result;
    }

    /**
     * Roll back a transaction.
     *
     * @return bool
     */
    public function rollBack(): bool
    {
        if (!$this->inTransaction) {
            return false;
        }
        $result = $this->pdo->rollBack();
        $this->inTransaction = false;
        return $result;
    }

    /**
     * Check if a transaction is currently active.
     *
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    /**
     * Escape a single identifier (table or column name).
     *
     * @param string $identifier
     *
     * @return string
     */
    private function escapeIdentifier(string $identifier): string
    {
        // SQLite uses double quotes for identifiers
        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    /**
     * Escape multiple identifiers.
     *
     * @param array $identifiers
     *
     * @return array
     */
    private function escapeIdentifiers(array $identifiers): array
    {
        return array_map([$this, 'escapeIdentifier'], $identifiers);
    }

    /**
     * Get the database file path (useful for debugging).
     *
     * @return string
     */
    public function getDatabasePath(): string
    {
        return IRTIJA_DB_PATH;
    }

    /**
     * Check if the database file exists.
     *
     * @return bool
     */
    public function databaseExists(): bool
    {
        return file_exists(IRTIJA_DB_PATH);
    }

    /**
     * Get the database file size in bytes.
     *
     * @return int|false
     */
    public function getDatabaseSize()
    {
        if ($this->databaseExists()) {
            return filesize(IRTIJA_DB_PATH);
        }
        return false;
    }

    /**
     * Destructor — close the connection if open.
     */
    public function __destruct()
    {
        if ($this->inTransaction) {
            $this->rollBack();
        }
        $this->pdo = null;
    }
}

// ============================================================
//   HELPER FUNCTIONS (for convenience)
// ============================================================

/**
 * Get the database instance (convenience wrapper).
 *
 * @return Database
 */
function db(): Database
{
    return Database::getInstance();
}

/**
 * Get the PDO instance (convenience wrapper).
 *
 * @return PDO
 */
function pdo(): PDO
{
    return Database::getInstance()->getPdo();
}

/**
 * Execute a query and return the statement.
 *
 * @param string $sql
 * @param array  $params
 *
 * @return PDOStatement
 */
function db_query(string $sql, array $params = []): PDOStatement
{
    return Database::getInstance()->query($sql, $params);
}

/**
 * Fetch one row.
 *
 * @param string $sql
 * @param array  $params
 *
 * @return array|false
 */
function db_fetch_one(string $sql, array $params = [])
{
    return Database::getInstance()->fetchOne($sql, $params);
}

/**
 * Fetch all rows.
 *
 * @param string $sql
 * @param array  $params
 *
 * @return array
 */
function db_fetch_all(string $sql, array $params = []): array
{
    return Database::getInstance()->fetchAll($sql, $params);
}

/**
 * Fetch a single column.
 *
 * @param string $sql
 * @param array  $params
 *
 * @return mixed
 */
function db_fetch_column(string $sql, array $params = [])
{
    return Database::getInstance()->fetchColumn($sql, $params);
}

/**
 * Insert a row.
 *
 * @param string $table
 * @param array  $data
 *
 * @return int
 */
function db_insert(string $table, array $data): int
{
    return Database::getInstance()->insert($table, $data);
}

/**
 * Update rows.
 *
 * @param string $table
 * @param array  $data
 * @param string $where
 * @param array  $whereParams
 *
 * @return int
 */
function db_update(string $table, array $data, string $where, array $whereParams = []): int
{
    return Database::getInstance()->update($table, $data, $where, $whereParams);
}

/**
 * Delete rows.
 *
 * @param string $table
 * @param string $where
 * @param array  $params
 *
 * @return int
 */
function db_delete(string $table, string $where, array $params = []): int
{
    return Database::getInstance()->delete($table, $where, $params);
}

// ============================================================
//   LOGGING HELPER (for errors)
// ============================================================

/**
 * Log a database error (safe for production — no sensitive data).
 *
 * @param string  $message
 * @param array   $context
 * @param string  $level
 *
 * @return void
 */
function db_log_error(string $message, array $context = [], string $level = 'error'): void
{
    // In production, log to a file. In development, output to browser.
    $logFile = __DIR__ . '/../logs/db_errors.log';
    $logDir = dirname($logFile);

    // Create logs directory if it doesn't exist
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
    $logLine = sprintf(
        "[%s] [%s] %s%s%s",
        $timestamp,
        strtoupper($level),
        $message,
        $contextStr,
        PHP_EOL
    );

    @file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
}

// ============================================================
//   AUTO-CREATE DATABASE SCHEMA (on first run)
// ============================================================

/**
 * Check if the database exists and create it if it doesn't.
 * This is called automatically when the admin panel is first accessed.
 *
 * @return bool True if the database is ready
 */
function db_ensure_ready(): bool
{
    $db = Database::getInstance();

    // If the database already exists, check if the posts table exists
    if ($db->databaseExists()) {
        try {
            // Check if the posts table exists
            $result = $db->fetchColumn(
                "SELECT name FROM sqlite_master WHERE type='table' AND name='posts'"
            );
            if ($result !== false) {
                // Tables exist, everything is ready
                return true;
            }
        } catch (PDOException $e) {
            // Table check failed — database might be corrupted
            db_log_error('Failed to check database schema', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // Database doesn't exist or is missing tables — create schema
    try {
        return db_create_schema($db);
    } catch (PDOException $e) {
        db_log_error('Failed to create database schema', [
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Create the database schema from scratch.
 *
 * @param Database $db
 *
 * @return bool
 * @throws PDOException
 */
function db_create_schema(Database $db): bool
{
    $pdo = $db->getPdo();

    // --- Create categories table ---
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // --- Create posts table ---
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER,
            title TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            display_date TEXT NOT NULL,
            sort_date TEXT,
            preview_text TEXT,
            content TEXT NOT NULL,
            cover_image TEXT,
            certificate_url TEXT,
            status TEXT DEFAULT 'published',
            view_count INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )
    ");

    // --- Create tags table ---
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE NOT NULL,
            slug TEXT UNIQUE NOT NULL
        )
    ");

    // --- Create post_tags table ---
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS post_tags (
            post_id INTEGER,
            tag_id INTEGER,
            PRIMARY KEY (post_id, tag_id),
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
        )
    ");

    // --- Create gallery_images table ---
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS gallery_images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            post_id INTEGER NOT NULL,
            image_path TEXT NOT NULL,
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
        )
    ");

    // --- Create admin_user table ---
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_user (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // --- Insert default category ---
    $pdo->exec("
        INSERT OR IGNORE INTO categories (name, slug)
        VALUES ('Uncategorized', 'uncategorized')
    ");

    // --- Create indexes for performance ---
    $indexes = [
        'CREATE INDEX IF NOT EXISTS idx_posts_slug ON posts(slug)',
        'CREATE INDEX IF NOT EXISTS idx_posts_status ON posts(status)',
        'CREATE INDEX IF NOT EXISTS idx_posts_category_id ON posts(category_id)',
        'CREATE INDEX IF NOT EXISTS idx_post_tags_post_id ON post_tags(post_id)',
        'CREATE INDEX IF NOT EXISTS idx_post_tags_tag_id ON post_tags(tag_id)',
        'CREATE INDEX IF NOT EXISTS idx_gallery_images_post_id ON gallery_images(post_id)',
    ];

    foreach ($indexes as $indexSql) {
        $pdo->exec($indexSql);
    }

    // Create a trigger to automatically update the updated_at column
    $pdo->exec("
        CREATE TRIGGER IF NOT EXISTS update_posts_updated_at
        AFTER UPDATE ON posts
        BEGIN
            UPDATE posts SET updated_at = CURRENT_TIMESTAMP
            WHERE id = NEW.id;
        END
    ");

    return true;
}

// --- Ensure the database is ready when this file is included ---
// (This runs only once — subsequent includes are safe)
try {
    // Only run the check if we're in an admin context
    if (defined('IRTIJA_ADMIN') && IRTIJA_ADMIN === true) {
        db_ensure_ready();
    }
} catch (Throwable $e) {
    // Silently fail — the calling code will handle the error
    // Don't expose errors here, just log them
    db_log_error('Database readiness check failed', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
