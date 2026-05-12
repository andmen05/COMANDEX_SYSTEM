<?php
// controllers/PagosController.php
class PagosController {
    private Pedido $pedidoModel;
    private Pago   $pagoModel;

    public function __construct() {
        $this->pedidoModel = new Pedido();
        $this->pagoModel   = new Pago();
    }

    public function index(): void {
        requireLogin();
        // Solo pedidos que YA llegaron a la mesa (listo o servido) y que NO tienen pago
        $pendientes = $this->pedidoModel->getPendientesPago();
        view('pagos/index', compact('pendientes'));
    }

    public function cobrar(int $pedido_id): void {
        requireLogin();
        $pedido = $this->pedidoModel->findById($pedido_id);
        if (!$pedido) redirect('pagos');

        // Si ya está pagado, solo mostrar recibo
        $pagoPrevio = $this->pagoModel->findByPedido($pedido_id);
        if ($pagoPrevio && $pedido['estado'] === 'pagado') {
        $subtotal = $this->pedidoModel->getSubtotal($pedido_id);
        $iva      = round($subtotal * 0.19, 0);   // IVA Colombia 19%
        $servicio = round($subtotal * 0.10, 0);
        $total    = $subtotal + $iva + $servicio;
            $yaFuePagado = true;
            view('pagos/cobrar', compact('pedido','subtotal','iva','servicio','total','pagoPrevio','yaFuePagado'));
            return;
        }

        $subtotal  = $this->pedidoModel->getSubtotal($pedido_id);
        $iva       = round($subtotal * 0.19, 0);   // IVA Colombia 19%
        $servicio  = round($subtotal * 0.10, 0);
        $total     = $subtotal + $iva + $servicio;
        $yaFuePagado = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $metodo   = $_POST['metodo'] ?? 'efectivo';
            $recibido = (float)($_POST['recibido'] ?? $total);
            $cambio   = max(0, $recibido - $total);

            // Guardar pago
            $this->pagoModel->create([
                'pedido_id'  => $pedido_id,
                'metodo'     => $metodo,
                'monto'      => $total,
                'recibido'   => $recibido,
                'cambio'     => $cambio,
                'referencia' => $_POST['referencia'] ?? null,
            ]);

            // Marcar pedido como PAGADO y liberar la mesa
            $this->pedidoModel->updateEstado($pedido_id, 'pagado');

            // Auditoría: pago registrado
            $uid = $_SESSION['user_id'] ?? 'desconocido';
            logger()->info("PAGO_REGISTRADO — pedido_id:{$pedido_id} total:{$total} metodo:{$metodo} cajero_id:{$uid}");

            // Redirigir a recibo
            redirect('pagos/cobrar/' . $pedido_id);
        }

        view('pagos/cobrar', compact('pedido','subtotal','iva','servicio','total','pagoPrevio','yaFuePagado'));
    }
}
