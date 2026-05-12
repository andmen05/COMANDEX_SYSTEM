<?php
// controllers/MeseroController.php
class MeseroController {
    private Pedido $pedidoModel;
    private Producto $productoModel;
    private Mesa $mesaModel;

    public function __construct() {
        $this->pedidoModel   = new Pedido();
        $this->productoModel = new Producto();
        $this->mesaModel     = new Mesa();
    }

    public function index(): void {
        requireLogin();
        $mesas = $this->mesaModel->getAll(1);
        view('mesero/index', compact('mesas'));
    }

    public function pedido(int $mesa_id): void {
        requireLogin();
        $mesa       = $this->mesaModel->findById($mesa_id);
        if (!$mesa) redirect('mesero');

        $categorias  = $this->productoModel->getCategorias();
        $productos    = $this->productoModel->getAll();
        $favoritos    = $this->productoModel->getFavoritos();
        $pedidoActivo = $this->pedidoModel->getActivoByMesa($mesa_id);
        $todasMesas   = $this->mesaModel->getAll(1);

        // POST: Guardar/enviar pedido
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion'] ?? '';

            if ($accion === 'agregar_item') {
                $pedido_id = (int)($_POST['pedido_id'] ?? 0);
                if (!$pedido_id) {
                    // Crear nuevo pedido
                    $pedido_id = $this->pedidoModel->create([
                        'mesa_id'   => $mesa_id,
                        'mesero_id' => $_SESSION['user_id'],
                        'personas'  => (int)($_POST['personas'] ?? 1),
                    ]);
                }
                $this->pedidoModel->addItem($pedido_id, [
                    'producto_id'     => (int)$_POST['producto_id'],
                    'cantidad'        => (int)($_POST['cantidad'] ?? 1),
                    'precio_unitario' => (float)$_POST['precio'],
                    'notas'           => $_POST['notas'] ?? null,
                    'modificadores_sel' => json_decode($_POST['modificadores'] ?? '[]', true),
                ]);
                header('Content-Type: application/json');
                echo json_encode(['ok' => true, 'pedido_id' => $pedido_id]);
                exit;
            }

            if ($accion === 'enviar_cocina') {
                $pedido_id = (int)$_POST['pedido_id'];
                $this->pedidoModel->updateEstado($pedido_id, 'en_cocina');
                // Log actividad
                $db = getPDO();
                $pedido = $this->pedidoModel->findById($pedido_id);
                $db->prepare("INSERT INTO actividad (usuario_id,tipo,descripcion,entidad,entidad_id) VALUES (?,?,?,?,?)")
                   ->execute([$_SESSION['user_id'], 'pedido_nuevo',
                              "Nuevo pedido #{$pedido['numero']} enviado a cocina — Mesa {$pedido['mesa_numero']}",
                              'pedido', $pedido_id]);
                redirect('mesero');
            }

            if ($accion === 'marcar_servido') {
                $pedido_id = (int)$_POST['pedido_id'];
                $this->pedidoModel->updateEstado($pedido_id, 'servido');
                redirect('mesero');
            }

            if ($accion === 'eliminar_item') {
                $item_id = (int)$_POST['item_id'];
                $this->pedidoModel->removeItem($item_id);
                header('Content-Type: application/json');
                echo json_encode(['ok' => true]);
                exit;
            }
        }

        view('mesero/pedido', compact(
            'mesa','categorias','productos','favoritos','pedidoActivo','todasMesas','mesa_id'
        ));
    }
}
