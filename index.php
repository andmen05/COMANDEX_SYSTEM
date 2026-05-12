<?php
// index.php — Front Controller
session_start();
date_default_timezone_set('America/Bogota');   // ← Zona horaria Colombia
require_once __DIR__ . '/config/database.php';

// ─── Componente de Auditoría (Guía 8) ────────────────────────────────────────
require_once __DIR__ . '/app/Core/Logger.php';

/**
 * Instancia global del Logger — accesible desde cualquier controlador o modelo.
 * Uso: logger()->log('Mensaje', 'TIPO') o logger()->info('Mensaje')
 */
function logger(): \App\Core\Logger {
    static $instance = null;
    if ($instance === null) {
        $instance = new \App\Core\Logger();
    }
    return $instance;
}
// ─────────────────────────────────────────────────────────────────────────────

// Autoloader simple
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/controllers/' . $class . '.php',
        __DIR__ . '/models/'      . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Helpers globales
function redirect(string $path): void {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function view(string $viewPath, array $data = []): void {
    extract($data);
    $viewFile = __DIR__ . '/views/' . $viewPath . '.php';
    if (!file_exists($viewFile)) {
        die("Vista no encontrada: $viewPath");
    }
    require $viewFile;
}

function isLogged(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLogged()) redirect('login');
}

function requireRole(array $roles): void {
    if (!isset($_SESSION['user_rol']) || !in_array($_SESSION['user_rol'], $roles)) {
        redirect('dashboard');
    }
}

function currentUser(): array {
    return $_SESSION['user'] ?? [];
}

function formatMoney(float $amount): string {
    // Formato COP: $ 1.250.000 (sin decimales, punto como separador de miles)
    return '$ ' . number_format($amount, 0, ',', '.');
}

// Lee un valor de configuración del sistema (con caché en sesión)
function getConfig(string $clave, string $default = ''): string {
    if (!isset($_SESSION['_config'])) {
        try {
            $rows = getPDO()->query("SELECT clave, valor FROM configuracion")->fetchAll(PDO::FETCH_KEY_PAIR);
            $_SESSION['_config'] = $rows;
        } catch (\Throwable $e) {
            $_SESSION['_config'] = [];
        }
    }
    return $_SESSION['_config'][$clave] ?? $default;
}

function csrf(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(): void {
    $token = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        // Auditoría: intento de petición con token CSRF inválido
        $ip  = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
        $uid = $_SESSION['user_id']    ?? 'anonimo';
        logger()->warning("CSRF inválido — usuario_id:{$uid} IP:{$ip} URL:{$_SERVER['REQUEST_URI']}");
        http_response_code(403);
        die('CSRF token inválido');
    }
}

// ─── Router ───────────────────────────────────────────────────────────────────
$url = trim($_GET['url'] ?? '', '/');
$parts = explode('/', $url);
$controller = $parts[0] ?: 'dashboard';
$action = $parts[1] ?? 'index';
$param = $parts[2] ?? null;

// Rutas protegidas
$publicRoutes = ['login', 'logout'];

if (!in_array($controller, $publicRoutes) && !isLogged()) {
    redirect('login');
}

// ── Guard de permisos ─────────────────────────────────────────────────────────
// Módulos que requieren un permiso específico para acceder
$permGuard = [
    'dashboard'      => 'dashboard',
    'mesero'         => 'mesero',
    'cocina'         => 'cocina',
    'pagos'          => 'pagos',
    'mesas'          => 'mesas',
    'reportes'       => 'reportes',
    'admin'          => 'admin',
    'configuracion'  => 'admin',   // ← sólo Administrador
];

if (isLogged() && !in_array($controller, $publicRoutes) && isset($permGuard[$controller])) {
    $permisos = $_SESSION['permisos'] ?? ['dashboard'];
    $needed   = $permGuard[$controller];
    if (!in_array($needed, $permisos)) {
        // Redirigir al primer módulo disponible
        $landing = $permisos[0] ?? 'dashboard';
        redirect($landing);
    }
}
// ─────────────────────────────────────────────────────────────────────────────

// Dispatch
switch ($controller) {
    case 'login':
        $c = new AuthController();
        $c->login();
        break;
    case 'logout':
        $c = new AuthController();
        $c->logout();
        break;
    case 'dashboard':
    case '':
        $c = new DashboardController();
        $c->index();
        break;
    case 'mesero':
        $c = new MeseroController();
        if ($action === 'pedido' && $param) {
            $c->pedido((int)$param);
        } else {
            $c->index();
        }
        break;
    case 'cocina':
        $c = new CocinaController();
        $c->index();
        break;
    case 'pagos':
        $c = new PagosController();
        if ($action === 'cobrar' && $param) {
            $c->cobrar((int)$param);
        } else {
            $c->index();
        }
        break;
    case 'mesas':
        $c = new MesasController();
        $c->index();
        break;
    case 'admin':
        requireRole(['Administrador', 'Gerente']);
        $c = new AdminController();
        $c->{$action}();
        break;
    case 'perfil':
        $c = new PerfilController();
        $c->index();
        break;
    case 'configuracion':
        requireRole(['Administrador']);   // ← ÚNICO el admin principal
        $c = new AdminController();
        $c->configuracion();
        break;
    case 'reportes':
        $c = new ReportesController();
        $c->index();
        break;

    // ── API AJAX ──
    case 'api':
        header('Content-Type: application/json');
        require_once __DIR__ . '/api/' . ($action ?: 'pedidos') . '.php';
        break;

    default:
        http_response_code(404);
        view('errors/404');
}
