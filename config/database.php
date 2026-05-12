<?php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'comandexx');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Comandex');
define('APP_VERSION', '1.0.0');
define('BASE_URL', '/COMANDEX');  // Ruta real en htdocs

function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Auditoría: fallo de conexión a la base de datos
            // No podemos usar logger() aquí (aún no está cargado), así que
            // escribimos directamente en audit.log para no perder la trazabilidad
            $logPath = dirname(__DIR__) . '/logs/audit.log';
            $stamp   = date('Y-m-d H:i:s');
            @file_put_contents(
                $logPath,
                "[{$stamp}] [DB] [CONEXION_FALLIDA — {$e->getMessage()}]" . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
