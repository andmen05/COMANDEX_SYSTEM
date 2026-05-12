<?php
// controllers/MesasController.php
class MesasController {
    private Mesa $mesaModel;
    private Pedido $pedidoModel;

    public function __construct() {
        $this->mesaModel   = new Mesa();
        $this->pedidoModel = new Pedido();
    }

    public function index(): void {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mesa_id = (int)($_POST['mesa_id'] ?? 0);
            $estado  = $_POST['estado'] ?? '';
            if ($mesa_id && in_array($estado, ['libre','ocupada','reservada','por_liberar'])) {
                $this->mesaModel->updateEstado($mesa_id, $estado);
            }
            if (isset($_GET['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['ok' => true]);
                exit;
            }
        }

        $zonaFiltro = $_GET['zona'] ?? '';
        $mesas      = $this->mesaModel->getAll(1, $zonaFiltro);
        $stats      = $this->mesaModel->countByEstado();

        view('mesas/index', compact('mesas','stats','zonaFiltro'));
    }
}
