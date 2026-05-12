<?php
// controllers/AuthController.php
class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login(): void {
        if (isLogged()) redirect('dashboard');

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->findByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                // Cargar permisos del rol desde la BD
                $db   = getPDO();
                $stmt = $db->prepare("SELECT permisos FROM roles WHERE id = ?");
                $stmt->execute([$user['rol_id']]);
                $row      = $stmt->fetch();
                $permisos = json_decode($row['permisos'] ?? '["dashboard"]', true);

                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_rol']  = $user['rol'];
                $_SESSION['user']      = $user;
                $_SESSION['permisos']  = $permisos;   // ← permisos cargados al login
                $_SESSION['csrf']      = bin2hex(random_bytes(32));

                $this->userModel->updateLastAccess($user['id']);

                // Auditoría: login exitoso
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
                logger()->auth("LOGIN_EXITOSO — usuario:{$email} rol:{$user['rol']} IP:{$ip}");

                // Redirigir al primer módulo permitido
                $landing = $permisos[0] ?? 'dashboard';
                redirect($landing);
            } else {
                // Auditoría: credenciales incorrectas
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
                logger()->auth("LOGIN_FALLIDO — intento con email:{$email} IP:{$ip}");
                $error = 'Credenciales incorrectas. Intenta de nuevo.';
            }
        }

        view('auth/login', ['error' => $error]);
    }

    public function logout(): void {
        session_destroy();
        redirect('login');
    }
}
