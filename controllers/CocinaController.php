<?php
// controllers/CocinaController.php
class CocinaController {
    private Pedido $pedidoModel;

    public function __construct() {
        $this->pedidoModel = new Pedido();
    }

    public function index(): void {
        requireLogin();

        // Cambio de estado vía POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pedido_id = (int)($_POST['pedido_id'] ?? 0);
            $estado    = $_POST['estado'] ?? '';
            if ($pedido_id && in_array($estado, ['en_cocina','listo','servido','cancelado'])) {
                $this->pedidoModel->updateEstado($pedido_id, $estado);
                // Log
                $db = getPDO();
                $db->prepare("INSERT INTO actividad (usuario_id,tipo,descripcion,entidad,entidad_id) VALUES (?,?,?,?,?)")
                   ->execute([$_SESSION['user_id'] ?? null, 'estado_pedido',
                              "Pedido #$pedido_id marcado como $estado", 'pedido', $pedido_id]);
            }
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            exit;
        }

        $pedidos = $this->pedidoModel->getKitchenOrders();

        $nuevo        = array_filter($pedidos, fn($p) => $p['estado'] === 'pendiente');
        $en_preparacion = array_filter($pedidos, fn($p) => $p['estado'] === 'en_cocina');
        $listo        = array_filter($pedidos, fn($p) => $p['estado'] === 'listo');

        view('cocina/index', compact('pedidos','nuevo','en_preparacion','listo'));
    }
}
