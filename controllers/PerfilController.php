<?php
// controllers/PerfilController.php
class PerfilController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function index(): void {
        requireLogin();
        $db     = getPDO();
        $userId = $_SESSION['user_id'];
        $msg    = '';
        $err    = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion'] ?? '';

            // ── Actualizar info básica ──────────────────────────────────────
            if ($accion === 'actualizar_info') {
                $nombre = trim($_POST['nombre'] ?? '');
                $email  = trim($_POST['email']  ?? '');
                if (!$nombre || !$email) {
                    $err = 'Nombre y correo son obligatorios.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $err = 'Correo electrónico no válido.';
                } else {
                    $db->prepare("UPDATE usuarios SET nombre=?, email=? WHERE id=?")
                       ->execute([$nombre, $email, $userId]);
                    $fresh = $this->userModel->findById($userId);
                    $_SESSION['user'] = $fresh;
                    $msg = 'Información actualizada correctamente.';
                }
            }

            // ── Cambiar contraseña ──────────────────────────────────────────
            if ($accion === 'cambiar_password') {
                $actual  = $_POST['password_actual']  ?? '';
                $nueva   = $_POST['password_nueva']   ?? '';
                $confirm = $_POST['password_confirm'] ?? '';
                $user    = $this->userModel->findById($userId);
                if (!password_verify($actual, $user['password'])) {
                    $err = 'La contraseña actual no es correcta.';
                } elseif (strlen($nueva) < 8) {
                    $err = 'La nueva contraseña debe tener al menos 8 caracteres.';
                } elseif ($nueva !== $confirm) {
                    $err = 'Las contraseñas nuevas no coinciden.';
                } else {
                    $hash = password_hash($nueva, PASSWORD_BCRYPT, ['cost' => 12]);
                    $db->prepare("UPDATE usuarios SET password=? WHERE id=?")->execute([$hash, $userId]);
                    $msg = 'Contraseña actualizada correctamente.';
                }
            }

            // ── Guardar color de avatar ─────────────────────────────────────
            if ($accion === 'personalizar') {
                $color = $_POST['avatar_color'] ?? '#2563eb';
                if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                    $color = '#2563eb';
                }
                $db->prepare("UPDATE usuarios SET avatar=? WHERE id=?")->execute([$color, $userId]);
                $fresh = $this->userModel->findById($userId);
                $_SESSION['user'] = $fresh;
                $msg = 'Personalización guardada.';
            }

            // Registrar actividad de cambio de perfil
            if ($msg) {
                $db->prepare("INSERT INTO actividad (usuario_id, tipo, descripcion, entidad, entidad_id)
                              VALUES (?, 'perfil_update', ?, 'usuario', ?)")
                   ->execute([$userId, $msg, $userId]);
            }
        }

        $user = $this->userModel->findById($userId);

        // Actividad reciente (últimas 20 acciones)
        $stmtAct = $db->prepare("SELECT * FROM actividad WHERE usuario_id = ? ORDER BY created_at DESC LIMIT 20");
        $stmtAct->execute([$userId]);
        $actividad = $stmtAct->fetchAll();

        // Estadísticas personales — prepared statements correctos
        $stmtHoy = $db->prepare("SELECT COUNT(*) FROM pedidos WHERE mesero_id = ? AND DATE(created_at) = CURDATE()");
        $stmtHoy->execute([$userId]);
        $pedidosHoy = (int)$stmtHoy->fetchColumn();

        $stmtTotal = $db->prepare("SELECT COUNT(*) FROM pedidos WHERE mesero_id = ?");
        $stmtTotal->execute([$userId]);
        $totalPedidos = (int)$stmtTotal->fetchColumn();

        $stats = [
            'pedidos_hoy'   => $pedidosHoy,
            'total_pedidos' => $totalPedidos,
            'ultimo_acceso' => $user['ultimo_acceso'] ?? null,
        ];

        view('perfil/index', compact('user', 'actividad', 'stats', 'msg', 'err'));
    }
}
